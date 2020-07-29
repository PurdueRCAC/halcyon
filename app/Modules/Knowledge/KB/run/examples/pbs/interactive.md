---
title: Interactive Jobs
tags:
 - wholenode
 - sharednode
---
# Interactive Jobs

Interactive jobs are run on compute nodes, while giving you a shell to interact with. They give you the ability to type commands or use a graphical interface as if you were on a front-end.

To submit an interactive job with one hour of wall time, use the <kbd>-I</kbd> option to <kbd>qsub</kbd>:

{::if resource.qsub_needs_gpu == 1}
On ${resource.name}, <b>specifying the number of GPUs requested per node is required.</b>

<pre>$ qsub -I -l nodes=1:ppn=1:gpus=1 -l walltime=01:00:00
{::else}
<pre>$ qsub -I -l nodes=1:ppn=${resource.nodecores} -l walltime=01:00:00
{::/}
qsub: waiting for job 100.${resource.hostname}-adm.rcac.purdue.edu to start
qsub: job 100.${resource.hostname}-adm.rcac.purdue.edu ready
</pre> 

If you need to use a remote X11 display from within your job (see the [ThinLinc section](../../../../accounts/login/thinlinc)), add the <kbd>-X</kbd> option to <kbd>qsub</kbd> as well:

{::if resource.qsub_needs_gpu == 1}
<pre>$ qsub -I -l nodes=1:ppn=1:gpus=1 -l walltime=01:00:00 -X
{::else}
<pre>$ qsub -I -l nodes=1:ppn=${resource.nodecores} -l walltime=01:00:00 -X
{::/}
qsub: waiting for job 101.${resource.hostname}-adm.rcac.purdue.edu to start
qsub: job 101.${resource.hostname}-adm.rcac.purdue.edu ready
</pre> 

To quit your interactive job:

<pre>logout</pre>
