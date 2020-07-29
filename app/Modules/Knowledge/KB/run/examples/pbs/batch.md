---
title: Simple Job
tags:
 - wholenode
 - sharednode
---

# Simple Job

Every PBS job consists of a job submission file. A job submssion file contains a list of commands that run your pgoram and a set of resource (nodes, walltime, queue) requests. The resource requests can appear in the [job submission file](../directives) or can be specified at submit-time as shown below.

This simple example submits the job submission file <kbd>hello.sub</kbd> to the <kbd>${resource.queue}</kbd> queue on ${resource.name} and requests a single node:

<pre>
# FILENAME: hello.sub

# Show this ran on a compute node by running the hostname command.
hostname

echo "Hello World"
</pre>

{::if resource.qsub_needs_gpu == 1}
On ${resource.name}, <b>specifying the number of GPUs requested per node is required.</b>

<pre>
$ qsub -q ${resource.queue} -l nodes=1:ppn=1:gpus=1,walltime=00:01:00 hello.sub
99.${resource.hostname}-adm.rcac.purdue.edu
</pre>
{::else}
<pre>
$ qsub -q ${resource.queue} -l nodes=1:ppn=${resource.nodecores},walltime=00:01:00 hello.sub
99.${resource.hostname}-adm.rcac.purdue.edu
</pre>

{::/}


For a real job you would replace `echo "Hello World"` with a command, or sequence of commands, that run your program.

After your job finishes running, the <kbd>ls</kbd> command  will show two new files in your directory, the <kbd>.o</kbd> and <kbd>.e</kbd> files:

<pre>$ ls -l
hello.sub
hello.sub.e99
hello.sub.o99
</pre> 

If everything went well, then the file <kbd>hello.sub.e99</kbd> will be empty. The '.e' file contains any error messages your program gave while running. In this case, it should be empty. The file <kbd>hello.sub.o99</kbd> contains the output from your program:

<pre>
$ cat hello.sub.o99
${resource.hostname}-a001.rcac.purdue.edu
Hello World
</pre>

You should see the hostname of the compute node your job was executed on. Following should be the "Hello World" statement.
