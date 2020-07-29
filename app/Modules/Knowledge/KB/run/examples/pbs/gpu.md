---
title: GPU
tags:
 - gpu
---
# GPU

The ${resource.name} cluster nodes contain {::if resource.nodegpus == 1}GPU{::else}GPUs{::/} that support <em>CUDA</em> and <em>OpenCL</em>. See the [detailed hardware overview](../../../../overview) for the specifics on the GPUs in ${resource.name}.

This section illustrates how to use PBS to submit a simple GPU program.

Suppose that you named your executable file <kbd>gpu_hello</kbd> from the sample code [`gpu_hello.cu`](/knowledge/downloads/compile/src/gpu_hello.cu).  Prepare a job submission file with an appropriate name, here named <kbd>gpu_hello.sub</kbd>:

<pre>
#!/bin/sh -l
# FILENAME:  gpu_hello.sub

module load cuda

cd $PBS_O_WORKDIR

host=`hostname -s`

echo $CUDA_VISIBLE_DEVICES
echo $PBS_GPUFILE

./gpu_hello
</pre>

Submit the job:
<pre>
$ qsub -q {::if resource.queuemodel == partner}partner{::else}${resource.queue}{::/} -l nodes=1:gpus=1,walltime=00:01:00 gpu_hello.sub
</pre>

<b>Specifying the number of GPUs requested per node is required.</b>
<p>
During job run-time, PBS sets a environment variable <kbd>$PBS_GPUFILE</kbd> that contains a file listing the GPUs allocated to this job. This file is very similar to the <kbd>$PBS_NODEFILE</kbd> environment variable. 
</p>
<p>
The PBS batch system will automatically set <kbd>$CUDA_VISIBLE_DEVICES</kbd> to the specfic GPU devices allocated to the job.
</p>

After job completion, View two new files in your directory (<kbd>.o</kbd> and <kbd>.e</kbd>):
<pre>
$ ls -l
gpu_hello
gpu_hello.cu
gpu_hello.sub
gpu_hello.sub.emyjobid
gpu_hello.sub.omyjobid
</pre> 

View results in the file for all standard output, gpu_hello.sub.omyjobid
<pre>
hello, world
</pre>

If the job failed to run, then view error messages in the file <kbd>gpu_hello.sub.emyjobid</kbd>.

To use multiple GPUs per node, simply specify a larger value to the <kbd>-l nodes=1:gpus</kbd> parameter. 
