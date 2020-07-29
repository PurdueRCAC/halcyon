---
title: Serial Jobs
tags:
 - wholenode
 - sharednode
---
# Serial Jobs

This shows how to submit one of the serial programs compiled in the section [Compiling Serial Programs](../../../../compile/serial).  

Create a job submission file:

<pre>
# FILENAME:  serial_hello.sub

cd $PBS_O_WORKDIR

./serial_hello
</pre>

Since PBS sets the working directory to your home directory (as if you just logged in), you should use the <kbd>cd $PBS_O_WORKDIR</kbd> command to change the job's working directory to the directory from which you submitted the job. 

Submit the job:

{::if resource.qsub_needs_gpu == 1}
<pre>$ qsub -l nodes=1:ppn=1:gpus=1,walltime=00:01:00 ./serial_hello.sub</pre>
{::else}
<pre>$ qsub -l nodes=1:ppn=${resource.nodecores},walltime=00:01:00 ./serial_hello.sub</pre>
{::/}

After the job completes, view results in the output file:
<pre>
$ cat serial_hello.sub.omyjobid
Runhost:${resource.hostname}-a139.rcac.purdue.edu   hello, world
</pre>

If the job failed to run, then view error messages in the file <kbd>serial_hello.sub.emyjobid</kbd>.

# ParaFly

ParaFly is a helper program, available through `module load utilities parafly`, that can be used to run multiple processes on one node by reading commands from a file.  It keeps track of the commands being run and their success or failure, and keeps a specified number of CPU cores on the node busy with the commands in the file.

For instance, assume you have a file called <strong>params.txt</strong> with the following 500 lines in it:
<pre>
runcommand param-1
runcommand param-2
runcommand param-3
runcommand param-4
...
runcommand param-500
</pre>

You can then run ParaFly with this command:

<pre>
ParaFly  -c params.txt -CPU ${resource.nodecores} -failed_cmds rerun.txt
</pre>

and ParaFly will manage the 500 'runcommand' commands, keeping ${resource.nodecores} of them active at all times, and copying the ones that failed into a file called <strong>rerun.txt</strong>.

This gives you a way to execute many single-core commands in a single PBS job running on a single exclusively allocated node, rather than submitting each of them as a separate job. ParaFly has been used with upwards of 10,000 commands in its command file.

So, if you have the params.txt file in the above example, you could submit the following PBS submission file:

<pre>
#!/bin/bash
#PBS -q standby
{::if resource.qsub_needs_gpu == 1}
#PBS -l nodes=1:ppn=1:gpus=1
{::else}
#PBS -l nodes=1:ppn=${resource.nodecores}
{::/}
#PBS -l walltime=2:00:00

cd $PBS_O_WORKDIR

module load utilities parafly
ParaFly -c params.txt -CPU ${resource.nodecores} -failed_cmds rerun.txt
</pre>

This would run all 500 'runcommand' commands with their associated parameters on the same node, ${resource.nodecores} at a time. 

ParaFly command files are not bash scripts themselves; instead they are a list of one-line commands that are executed individually by bash. This means that each command line can use input or output redirection, or different command line options.  For example:

<pre>
command1 -opt1 val1 < input1 > output1
command2 -opt2 val2 < input2 > output2
command3 -opt3 val3 < input3 > output3
...
command500 -opt500 val500 < input500 > output500
</pre>

Note that there is no guarantee of order of execution using ParaFly, so you can not rely on output from one command being available as input for another.
