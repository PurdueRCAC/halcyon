---
title: "qdel: Server could not connect to MOM 12345.${resource.hostname}-adm.rcac.purdue.edu"
tags:
 - wholenode
 - sharednode
 - internal
---

### Problem

You receive the following message after attempting to delete a job with the 'qdel' command

`qdel: Server could not connect to MOM 12345.${resource.hostname}-adm.rcac.purdue.edu`

### Solution

This error usually indicates that at least one node running your job has stopped responding or crashed. 
Please forward the job ID to rcac-help@purdue.edu, and ITaP Research Computing staff can help remove the job from the queue.

{::if user.staff == 1}
### Staff Notes

To delete jobs like this, use "qdel -p"
{::/}
