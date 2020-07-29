---
title: Serial Jobs
tags:
 - slurm
---
# Serial Jobs

This shows how to submit one of the serial programs compiled in the section [Compiling Serial Programs](../../../../compile/serial).  

Create a job submission file:

<pre>
#!/bin/bash
# FILENAME:  serial_hello.sub

./serial_hello

</pre>

Submit the job:

<pre>$ sbatch --nodes=1 --ntasks=1 --time=00:01:00 serial_hello.sub</pre>

After the job completes, view results in the output file:
<pre>
$ cat slurm-myjobid.out
{::if resource.name != Weber}
Runhost:${resource.hostname}-a009.rcac.purdue.edu   hello, world
{::else}
Runhost:${resource.hostname}.rcac.purdue.edu   hello, world
{::/}
</pre>

If the job failed to run, then view error messages in the file <kbd>slurm-myjobid.out</kbd>.

