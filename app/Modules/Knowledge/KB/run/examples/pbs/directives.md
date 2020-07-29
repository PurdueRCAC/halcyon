---
title: Directives 
tags:
 - wholenode
 - sharednode
---

# Directives

So far these examples have shown submitting jobs with the resource requests on the `qsub` command line such as:

{::if resource.qsub_needs_gpu == 1}
<pre>
$ qsub -q ${resource.queue} -l nodes=1:ppn=1:gpus=1,walltime=00:01:00 hello.sub
</pre>
{::else}
<pre>
$ qsub -q ${resource.queue} -l nodes=1:ppn=${resource.nodecores},walltime=00:01:00 hello.sub
</pre>
{::/}

The resource requests can also be put into job submission file itself. Documenting the resource requests in the job submission is desirable because the job can be easily reproduced later. Details left in your command history are quickly lost. Arguments are specified with the `#PBS ` syntax:

<pre>
# FILENAME: hello.sub
#PBS -q ${resource.queue}
{::if resource.qsub_needs_gpu == 1}
#PBS -l nodes=1:ppn=1:gpus=1,walltime=00:01:00
{::else}
#PBS -l nodes=1:ppn=${resource.nodecores},walltime=00:01:00
{::/}

# Show this ran on a compute node by running the hostname command.
hostname

echo "Hello World"
</pre>

The `#PBS` directives **must** appear at the top of your submission file. PBS will stop parsing directives as soon as it encounters a line that does not start with '#'. If you insert a directive in the middle of your script, it will be ignored.

This job can be then submitted with:

<pre>
$ qsub hello.sub
</pre>
