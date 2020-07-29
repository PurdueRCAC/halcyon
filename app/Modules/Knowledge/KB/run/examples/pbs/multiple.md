--- 
title: Multiple Node
tags:
 - mpi
---

# Multiple Node

In some cases, you may want to request multiple nodes. To utilize multiple nodes, you will need to have a program or code that is specifically programmed to use multiple nodes such as with [MPI](../mpi). Simply requesting more nodes will not make your work go faster. Your code must support this ability.

This example shows a request for multiple compute nodes. The job submission file contains a single command to show the names of the compute nodes allocated:

<pre>
# FILENAME:  myjobsubmissionfile.sub

cat $PBS_NODEFILE
</pre>

The option <kbd>ppn</kbd> should be equal to the number of cores on a compute node:

{::if resource.qsub_needs_gpu == 1}
On ${resource.name}, <b>specifying the number of GPUs requested per node is required.</b>

<pre>
$ qsub -l nodes=2:ppn=${resource.nodecores}:gpus=1,walltime=00:10:00 -q ${resource.queue} myjobsubmissionfile.sub
</pre>
{::else}
<pre>
$ qsub -l nodes=2:ppn=${resource.nodecores},walltime=00:10:00 -q ${resource.queue} myjobsubmissionfile.sub
</pre>
{::/}

Compute nodes allocated:
<pre>
${resource.hostname}-a139
...
${resource.hostname}-a138
...
</pre> 
