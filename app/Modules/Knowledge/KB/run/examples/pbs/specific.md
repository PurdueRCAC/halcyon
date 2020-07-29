---
title: Specific Types of Nodes
tags:
 - wholenode
 - sharednode
---
# Specific Types of Nodes 

PBS allows running a job on [specific types of compute nodes](../../../../overview).

<strong>Example:</strong>  a job requires a compute node in an "A" sub-cluster:
{::if resource.qsub_needs_gpu == 1}
<pre>$ qsub -l nodes=1:ppn=${resource.nodecores}:gpus=1:A myjobsubmissionfile.sub</pre>
{::else}
<pre>$ qsub -l nodes=1:ppn=${resource.nodecores}:A myjobsubmissionfile.sub</pre>
{::/}

Compute node allocated:

<pre>${resource.hostname}-a009</pre>

Refer to [Detailed Hardware Specification](../../../../overview) section for list of available sub-cluster labels and their respective per-node memory sizes.
