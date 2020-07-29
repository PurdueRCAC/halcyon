---
title: xCAT Staci
tags:
 - internal
---

# xCAT Staci
This tool consists of 2 scripts that designed to stop a node from scheduling
and boot it into a different operating system. One script marks nodes
provisioned by xCAT offline within the PBS Torque resource manager and adds a
message in the PBS node notes about which operating system the node will boot
into. The second script is called every 15 minutes as a CRON job, looks for
nodes marked offline, checks the nodes PBS note message for an operating
system, ensures the node is not executing any jobs, and then reboots then node
into the listed operating system.

1. `/usr/site/rcac/sbin/xcat_mark_for_upgrade`  
```
Usage: /usr/site/rcac/sbin/xcat_mark_for_upgrade <xcat-noderange> <xcat-osimage>
  xcat-noderange  xCAT specific node range
  xcat-osimage    OS image defined in xCAT
```
This script, located on the xCAT manager, takes a noderange (eg.
halstead-a[000-399]) and an operating system (eg.
halstead-compute-rhels6.8-2017.05.31-rc.139) and marks the listed nodes offline
in PBS and adds a note
`xCAT REBOOT OS=halstead-compute-rhels6.8-2017.05.31-rc.139`. If the node
already contains a message similar to the one before, the script changes the OS
listed in the message to reflect the currently listed operating system.

2. `/usr/site/rcac/sbin/xcat_upgrade_marked_node`  
This command takes no flags. This script, located on the xCAT manager and
executed every 15 minutes as a CRON job, assumes that `pbsnodes` is installed
and configured to point to the cluster's ADM server. The upgrade script uses
`pbsnodes` to list all nodes on the cluster and to check their status. Once the
upgrade script checks a node and meets the following classifications, the
script will change the OS provisioned to the node using
`/opt/xcat/sbin/nodeset` followed by restarting the node using
`/opt/xcat/bin/rpower`.
* Marked offline in PBS
* Contains a node note with `xCAT REBOOT OS=<osimage>`
* Is not currently running jobs for PBS
* `<osimage>` listed in message is actual xCAT OS
* Is not already booted into this image

The update script logs a message to the syslog whenever it attempts to
provision an OS to a node using the process name `STACI_XCAT` to permit easy
monitoring to the actions of upgrade script. If the script fails to provision
a node, it will log the failure and move to the next node.


# Debugging
If nodes are rebooted but do not boot into a new image - restart xcatd service.

If nodes are not being reprovisioned but nodes are marked and are not running
jobs - the PBS node notes file may need to be cleaned up and pbs_server may
need to be restarted.


# Useful Scripts

### pbsnodes-filter
Filter nodes by their notes. First argument is a space separated string that
the search will include. The second argument a similar list that the search
will exclude.

`pbsnodes-filter 'xcat reboot' 'nhc mn-'`

```bash
#!/bin/bash
#
# Filter statements come in as a string of keywords separated by spaces
# $1 - Filter for
# $2 - Filter out

PBSNODESBIN="/usr/pbs/bin/pbsnodes"
if [ ! -x "/usr/pbs/bin/pbsnodes" ] ; then PBSNODESBIN="/usr/bin/pbsnodes" ; fi

cmd="${PBSNODESBIN} -ln"
if [[ ${HOSTNAME} =~ ^halstead-.*$ ]] ; then
  cmd="${PBSNODESBIN} -ln | grep halstead-a"
fi

if [[ ! -z "$2" ]] ; then
  cmd="$cmd | grep -ivE '($(echo $2 | sed 's/ /|/g'))'"
fi

if [[ ! -z "$1" ]] ; then
  cmd="$cmd | grep -iE '($(echo $1 | sed 's/ /|/g'))'"
fi

/bin/bash -c "$cmd"
```

### pbsnodes-to-noderange
Similar to `dshbak` but for `pbsnodes` format. Requires a Python program called
`clustershell`. Install using `pip install --user clustershell`.

`pbsnodes-filter 'xcat' | pbsnodes-to-noderange`

```bash
#!/bin/bash

cat - | awk '{print $1}' | cluset --fold | sed 's/://'
```

### pbsnodes-running-job
Takes a noderange and checks to see if the nodes are running jobs. Output can
be fed into `dshbak`.

`pbsnodes-running-job halstead-a[000-399] | dshbak -c`

```bash
#!/bin/bash

pdsh -w $1 'if [ "$(ls /var/spool/torque/active | wc -l)" -gt 0 ] ; then echo running jobs ; else echo not running jobs ; fi'
```

### nodes_nhc_dirty
Takes anoderange and checks to see if they are failing NHC health checks.
Output can be fed into `dshbak`.

`nodes_nhc_dirty halstead-a[000-399] | dshbak -c`

```bash
#!/bin/bash

# $1 - Node range

sudo -H pdsh -w $1 'nhc 1>/dev/null 2>/dev/null && echo clean || echo dirty'
```

### pbs-fix-nodenote
Must be run as root on the ADM server. Will modify the PBS node note file at
`/var/spool/torque/server_priv/node_note` by removing empty lines and cutting
long lines to 200 characters. Backs the node_note file up to
`node_note.cook71.<timestamp>.bak` and puts the modified version into
`node_note.new`. Requires manual movement of `node_note.new` to `node_note` to
permit manual inspection before making the production change. Service
`pbs_server` must be restarted. Check the status of the service after
restarting to ensure service is running properly.

This provides an excellent example of how I counted nodes and checked up on an
xCAT STACI progress.

```bash
#!/bin/bash

now=`date +%Y%m%dT%H%M%S`

if [[ ! $HOSTNAME =~ ^.*-adm.rcac.purdue.edu$ ]] ; then
  echo "Not running on an ADM machine"
  exit 1
fi

if [ ! "$(whoami)" == "root" ] ; then
  echo "Not root user"
  exit 1
fi

if [ ! -f /var/spool/torque/server_priv/node_note ] ; then
  echo "Cannot find node_note file"
  exit 1
fi

cp /var/spool/torque/server_priv/node_note /var/spool/torque/server_priv/node_note.cook71.${now}.bak
cat /var/spool/torque/server_priv/node_note | cut -c -200 | sed -E '/^$/d' > /var/spool/torque/server_priv/node_note.new
#mv /var/spool/torque/server_priv/node_note.new /var/spool/torque/server_priv/node_note

if [ $? -ne 0 ] ; then
  echo -e "\033[0;31mERROR: Line removal failed.\e8"
  exit 1
else
  echo -e "\033[0;32mSuccessfully removed empty lines in node_note\e8"
  exit 0
fi
```

### staci-status
Node range and osimage are defined within the script. Provides a status update
of a cluster (Halstead right now) with the total nodes, nodes waiting to STACI,
and nodes that are not marked for STACI but are marked for other issues. Setup
to print HTML and act as a CGI script. Requires `clustershell` Python program.

```bash
#!/bin/bash

NODERANGE='halstad-a[012-399]'
IDEAL_OSIMAGE='halstead-compute-rhels6.8-2017.05.31-rc.139'
export PDSH_SSH_ARGS_APPEND="-i'id_dsa'"

xcat_nodes_count=$(/usr/bin/pbsnodes -l | grep -iv halstead-t | wc -l)
xcat_nodes_staci_count=$(/usr/bin/pbsnodes -ln | grep -i xcat | wc -l)
xcat_nodes_other_count=$(expr $xcat_nodes_count - $xcat_nodes_staci_count)
xcat_nodes_unhealthy=$(/usr/bin/pbsnodes -l | grep -iv halstead-t | awk '{print $1}' | /usr/bin/cluset -f | sed 's/://')
xcat_nodes_healthy=$(/usr/bin/cluset -f halstead-a[012-399] -x ${xcat_nodes_unhealthy})
xcat_nodes_in_image=$(/usr/bin/pdsh -l cook71 -w ${xcat_nodes_healthy} cat /etc/halstead-build/xcat-image-name | grep ${IDEAL_OSIMAGE} | cut -d: -f2 | sort | uniq -c | awk '{print $1}')

echo "Content-type: text/html"
echo ""
echo "<p><b>Total Number of Nodes:</b> $(/usr/bin/cluset -c ${NODERANGE})</p>"
echo "<p><b>Nodes Marked with STACI:</b> ${xcat_nodes_staci_count}</p>"
echo "<p><b>Nodes Marked Offline with Other:</b> ${xcat_nodes_other_count}</p>"
echo "<p><b>Nodes in OS Image ${IDEAL_OSIMAGE}:</b> ${xcat_nodes_in_image}</p>"
exit 0
```

### pbs-node-final-job
Finds the longest job running on a node. It does not take any arguments. Needs
to be executed as a root user. I used this script to search for the longest
running job on nodes that are marked for an xCAT STACI. This is a Python script
and requires Python 2.6, 2.7, 3.0, or 3.4.

On halstead-sys:
`sudo -H pdsh -w halstead-a[012-399] ~cook71/bin/pbs-node-final-job | dshbak -c`

```python
#!/usr/bin/env python

from datetime import datetime, timedelta

import xml.etree.ElementTree
import os
import time

PBS_JOBS_DIR = '/var/spool/torque/mom_priv/jobs'

pbs_jobs = os.listdir('/var/spool/torque/mom_priv/jobs/')
pbs_jobs_parsed = []

for j in pbs_jobs:
  if not j.endswith('.JB'):
    pbs_jobs.remove(j)

for j in pbs_jobs:
  job = {}
  job_file = xml.etree.ElementTree.parse('%s/%s' % (PBS_JOBS_DIR, j)).getroot()

  wall = job_file.findall('attributes')[0].findall('Resource_List')[0].findall('walltime')[0].text
  wall = map(int, wall.split(':'))
  job['walltime'] = (3600 * wall[0]) + (60 * wall[1]) + wall[2]

  job['id'] = job_file.findall('jobid')[0].text

  job['starttime'] = int(job_file.findall('start_time')[0].text)
  job['endtime'] = job['starttime'] + job['walltime']

  pbs_jobs_parsed.append(job)

pbs_jobs_sorted = sorted(pbs_jobs_parsed, key=lambda k:k['endtime'])
#print pbs_jobs_sorted
print "Job[%s] ends at %s" % (pbs_jobs_sorted[0]['id'], datetime.fromtimestamp(pbs_jobs_sorted[0]['endtime']))
```
