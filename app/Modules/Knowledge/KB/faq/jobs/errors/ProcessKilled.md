---
title: "1234.${resource.hostname}-adm.rcac.purdue.edu.SC: line 12: 12345 Killed"
tags:
 - wholenode
 - sharednode
---

### Problem

Your PBS job stopped running and you received an email with the following:

    /var/spool/torque/mom_priv/jobs/1234.${resource.hostname}-adm.rcac.purdue.edu.SC: line 12: 12345 Killed <command name>

### Solution

This means that the node your job was running on ran out of memory to support your program or code. This may be due to your job or other jobs sharing your node(s) consuming more memory in total than is available on the node. Your program was killed by the node to preserve the operating system. There are two possible causes:

{::if resource.naccesspolicy == singlejob}
- You requested your job share node(s) with other jobs. You should request all cores of the node or [request exclusive access](../../../../run/pbs/naccesspolicy). Either your job or one of the other jobs running on the node consumed too much memory. Requesting exclusive access will give you full control over all the memory on the node.
- Your job requires more memory than is available on the node. You should use more nodes if your job supports MPI or run a smaller dataset.
{::else}
- On ${resource.name}, jobs using less than ${resource.nodecores} cores per node default to allowing your jobs to share the node(s) with other jobs. You should request all cores of the node or [request exclusive access](../../../../run/pbs/naccesspolicy). Either your job or one of the other jobs running on the node consumed too much memory. Requesting exclusive access will give you full control over all the memory on the node.
- You requested exclusive access to the nodes but your job requires more memory than is available on the node. You should use more nodes if your job supports MPI or run a smaller dataset.
{::/}
