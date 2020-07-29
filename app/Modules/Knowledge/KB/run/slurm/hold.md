---
title: Holding a Job
tags:
 - slurm
---

# Holding a Job

Sometimes you may want to submit a job but not have it run just yet. You may be wanting to allow labmates to cut in front of you in the queue - so hold the job until their jobs have started, and then release yours.

To place a hold on a job before it starts running, use the <kbd>scontrol hold job</kbd> command:

<pre>$ scontrol hold job  myjobid</pre>

Once a job has started running it can not be placed on hold.

To release a hold on a job, use the <kbd>scontrol release job</kbd> command:
<pre>$ scontrol release job  myjobid</pre>

You find the job ID using the <kbd>squeue </kbd> command as explained in the [SLURM Job Status section](../status).
