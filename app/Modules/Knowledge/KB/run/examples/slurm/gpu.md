---
title: GPU
tags:
 - gpuslurm
---
# GPU

The ${resource.name} cluster nodes contain {::if resource.nodegpus == 1}GPU{::else}GPUs{::/} that support <em>CUDA</em> and <em>OpenCL</em>. See the [detailed hardware overview](../../../../overview) for the specifics on the GPUs in ${resource.name}.

This section illustrates how to use SLURM to submit a simple GPU program.

Suppose that you named your executable file <kbd>gpu_hello</kbd> from the sample code [`gpu_hello.cu`](/knowledge/downloads/compile/src/gpu_hello.cu).  Prepare a job submission file with an appropriate name, here named <kbd>gpu_hello.sub</kbd>:

<pre>
#!/bin/bash
# FILENAME:  gpu_hello.sub

module load cuda

host=`hostname -s`

echo $CUDA_VISIBLE_DEVICES

# Run on the first available GPU
./gpu_hello 0
</pre>

Submit the job:
<pre>
$ sbatch  -A {::if resource.queuemodel == partner}partner{::else}${resource.queue}{::/} --nodes=1 --gpus=1 -t 00:01:00 gpu_hello.sub
</pre>

<b>Requesting a GPU from the scheduler is required.</b><br />
You can request total number of gpus, or gpus-per-node, or even gpus-per-task:
<pre>
$ sbatch  -A {::if resource.queuemodel == partner}partner{::else}${resource.queue}{::/} --nodes=1 --gpus=1 -t 00:01:00 gpu_hello.sub
$ sbatch  -A {::if resource.queuemodel == partner}partner{::else}${resource.queue}{::/} --nodes=1 --gpus-per-node=1 -t 00:01:00 gpu_hello.sub
$ sbatch  -A {::if resource.queuemodel == partner}partner{::else}${resource.queue}{::/} --nodes=1 --gpus-per-task=1 -t 00:01:00 gpu_hello.sub
</pre>


After job completion, View the new output file in your directory:
<pre>
$ ls -l
gpu_hello
gpu_hello.cu
gpu_hello.sub
slurm-myjobid.out
</pre> 

View results in the file for all standard output, slurm-myjobid.out
<pre>
0
hello, world
</pre>

If the job failed to run, then view error messages in the file <kbd>slurm-myjobid.out</kbd>.

To use multiple GPUs in your job, simply specify a larger value to the gpu-specification parameter. However, be aware of the number of GPUs installed on the node(s) you may be requesting. The scheduler can not allocate more GPUs than exist.
