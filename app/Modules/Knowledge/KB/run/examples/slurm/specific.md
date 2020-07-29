---
title: Specific Types of Nodes
tags:
 - slurm
---
# Specific Types of Nodes

SLURM allows running a job on [specific types of compute nodes](../../../../overview) to accommodate special hardware requirements (e.g. a certain CPU or GPU type, etc.)

Cluster nodes have a set of descriptive features assigned to them, and users can
specify which of these features are required by their job by using the constraint option at submission time.  Only nodes having features matching the job constraints will be used to satisfy the request.

<strong>Example:</strong>  a job requires a compute node in an "A" sub-cluster:
{::if resource.qsub_needs_gpu == 1}
<pre>$ sbatch --nodes=1 --ntasks=${resource.nodecores} --gpus=1 --constraint=A myjobsubmissionfile.sub</pre>
{::else}
<pre>$ sbatch --nodes=1 --ntasks=${resource.nodecores} --constraint=A myjobsubmissionfile.sub</pre>
{::/}

Compute node allocated:

<pre>${resource.hostname}-a003</pre>

Feature constraints can be used for both batch and interactive jobs, as well as for individual job steps inside a job.  Multiple constraints can be specified with a predefined syntax to achieve complex request logic.

Refer to [Detailed Hardware Specification](../../../../overview) section for list of available sub-cluster labels, their respective per-node memory sizes and other hardware details.  You could also use `sfeatures` command to list available constraint feature names for different node types.
