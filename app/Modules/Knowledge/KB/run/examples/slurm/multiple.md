--- 
title: Multiple Node
tags:
 - slurm
---

# Multiple Node

In some cases, you may want to request multiple nodes. To utilize multiple nodes, you will need to have a program or code that is specifically programmed to use multiple nodes such as with [MPI](../mpi). Simply requesting more nodes will not make your work go faster. Your code must support this ability.

This example shows a request for multiple compute nodes. The job submission file contains a single command to show the names of the compute nodes allocated:

<pre>
# FILENAME:  myjobsubmissionfile.sub
echo $SLURM_JOB_NODELIST
</pre>

{::if resource.qsub_needs_gpu == 1}
On ${resource.name}, <b>specifying the number of GPUs requested per node is required.</b>

<pre>
$ sbatch --nodes=2 --gpus-per-node=1 --time=00:10:00 -A ${resource.queue} myjobsubmissionfile.sub
</pre>
{::else}
<pre>
$ sbatch --nodes=2 --time=00:10:00 -A ${resource.queue} myjobsubmissionfile.sub
</pre>
{::/}

Compute nodes allocated:
<pre>
${resource.hostname}-a[014-015]
</pre> 
