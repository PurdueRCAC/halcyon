---
title: Simple Job
tags:
 - slurm
---

# Simple Job

Every SLURM job consists of a job submission file. A job submssion file contains a list of commands that run your program and a set of resource (nodes, walltime, queue) requests. The resource requests can appear in the [job submission file](../directives) or can be specified at submit-time as shown below.

This simple example submits the job submission file <kbd>hello.sub</kbd> to the <kbd>${resource.queue}</kbd> queue on ${resource.name} and requests a single node:

<pre>
#!/bin/bash
# FILENAME: hello.sub

# Show this ran on a compute node by running the hostname command.
hostname

echo "Hello World"
</pre>

{::if resource.qsub_needs_gpu == 1}
On ${resource.name}, <b>specifying the number of GPUs requested per node is required.</b>

<:pre>
$ sbatch -A ${resource.queue} --nodes=1 --gpus-per-node=1 --time=00:01:00 hello.sub
Submitted batch job 3521
</pre>
{::else}
<pre>
$ sbatch -A ${resource.queue} --nodes=1 --time=00:01:00 hello.sub
Submitted batch job 3521
</pre>

{::/}


For a real job you would replace `echo "Hello World"` with a command, or sequence of commands, that run your program.

After your job finishes running, the <kbd>ls</kbd> command  will show a new file in your directory, the <kbd>.out</kbd> file:

<pre>$ ls -l
hello.sub
slurm-3521.out
</pre> 

The file <kbd>slurm-3521.out</kbd> contains the output and errors your program would have written to the screen if you had typed its commands at a command prompt:
<pre>
$ cat slurm-3521.out 
{::if resource.name != Weber}
${resource.hostname}-a001.rcac.purdue.edu
{::else}
${resource.hostname}.rcac.purdue.edu
{::/}
Hello World
</pre>

You should see the hostname of the compute node your job was executed on. Following should be the "Hello World" statement.
