---
title: Directives 
tags:
 - slurm
---

# Directives

So far these examples have shown submitting jobs with the resource requests on the `sbatch` command line such as:

{::if resource.qsub_needs_gpu == 1}
<pre>
$ sbatch -A ${resource.queue} --nodes=1 --gpus-per-node=1 --time=00:01:00 hello.sub
</pre>
{::else}
<pre>
$ sbatch -A ${resource.queue} --nodes=1 --time=00:01:00 hello.sub
</pre>
{::/}

The resource requests can also be put into job submission file itself. Documenting the resource requests in the job submission is desirable because the job can be easily reproduced later. Details left in your command history are quickly lost. Arguments are specified with the `#SBATCH ` syntax:

<pre>
#!/bin/bash

# FILENAME: hello.sub
#SBATCH -A ${resource.queue}
{::if resource.qsub_needs_gpu == 1}
#SBATCH --nodes=1 --gpus-per-node=1 --time=00:01:00
{::else}
#SBATCH --nodes=1 --time=00:01:00
{::/}

# Show this ran on a compute node by running the hostname command.
hostname

echo "Hello World"
</pre>

The `#SBATCH` directives **must** appear at the top of your submission file. SLURM will stop parsing directives as soon as it encounters a line that does not start with '#'. If you insert a directive in the middle of your script, it will be ignored.

This job can be then submitted with:

<pre>
$ sbatch hello.sub
</pre>
