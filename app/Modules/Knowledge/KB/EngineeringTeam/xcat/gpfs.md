---
title: GPFS Client on xCAT Systems
tags:
 - internal
---

# GPFS Client on xCAT Systems

Configuring native GPFS clients on xCAT provisioned hosts can be problematic
when dealing with xCAT nodes due to the ordering of starting services. This
article will touch on how to setup a GPFS native client on xCAT systems so
that the GPFS service starts before PBS MOM to ensure NHC doesn't run prior
GPFS being mounted.

### Installation
Ensure the following packages are defined in the xCAT OS Image's _pkglist_:

* gpfs.base
* gpfs.docs
* gpfs.ext
* gpfs.gpl
* gpfs.gplbin
* gpfs.gskit
* gpfs.msg.en_US

Ensure the following files are configured and placed in the correct locations
within the xCAT OS Image's _rootimg_ directory:

* /etc/init.d/gpfs (placed by package installation)
* /etc/rc.d/init.d/gpfs (placed by package installation)
* /var/mmfs/ccr/ccr.nodes
* /var/mmfs/gen/mmsdrfs
* /var/mmfs/ssl/stage/\*
* /var/mmfs/sync/gpfsrunlevel
* /root/.ssh/authorized_keys (don't overwrite original, ensure gpfs contents included)

### xCAT Configuration

Once the packages are defined in the xCAT OS Image's _pkglist_, run _genimage_
and then synchronize the configuration files to the _rootimg_ directory of the
xCAT OS Image. Finish the xCAT OS Image build.

In a pure xCAT configured OS Image, create a _postbootscript_ that starts GPFS
and another that starts PBS MOM. Install them in the _postscripts_ directory on
the xCAT Manager server.

_/install/postscripts/startgpfs.sh_
```bash
#!/bin/bash

if [ "$(uname -s|tr 'A-Z' 'a-z')" = "linux" ];then
   str_dir_name=`dirname $0`
   . $str_dir_name/xcatlib.sh
fi

startservice gpfs
```

_/install/postscripts/startpbsmom.sh_
```bash
#!/bin/bash

if [ "$(uname -s|tr 'A-Z' 'a-z')" = "linux" ];then
   str_dir_name=`dirname $0`
   . $str_dir_name/xcatlib.sh
fi

startservice pbs_mom
```

Finally assign the new scripts to the _postbootscripts_ to the nodes so that
the GPFS script runs first. This step is executed on the xCAT Manager server.

**NOTE**: It is recommended to use the group name of the nodes rather than an
actual _noderange_ because xCAT will define the _postbootscript_ once for the
group rather than once for every node in the _noderange_.

```bash
/opt/xcat/bin/chdef -t node -o <noderange> -p postbootscripts='startgpfs.sh,startpbsmom.sh'
```
_TIP_: The `-p` flag with the `chdef` command tells xCAT to append the new
values to the stated attribute.
