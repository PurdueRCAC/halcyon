---
title: OpenMP
tags:
 - slurm
---
# OpenMP

A shared-memory job is a single process that takes advantage of a multi-core processor and its shared memory to achieve parallelization.  

This example shows how to submit an OpenMP program compiled in the section [Compiling OpenMP Programs](../../../../compile/openmp). 

<strong>When running OpenMP programs, all threads must be on the same compute node to take advantage of shared memory. The threads cannot communicate between nodes.</strong>

To run an OpenMP program, set the environment variable OMP_NUM_THREADS to the desired number of threads: 

In csh:
<pre>
$ setenv OMP_NUM_THREADS ${resource.nodecores}
</pre> 

In bash:
<pre>
$ export OMP_NUM_THREADS=${resource.nodecores}
</pre>

This should almost always be equal to the number of cores on a compute node. You may want to set to another appropriate value if you are running several processes in parallel in a single job or node.

Create a job submissionfile:
<pre>
#!/bin/bash
# FILENAME:  omp_hello.sub
#SBATCH --nodes=1
#SBATCH --ntasks=${resource.nodecores}
#SBATCH --time=00:01:00

export OMP_NUM_THREADS=${resource.nodecores}
./omp_hello 
</pre>


Submit the job:
<pre>
$ sbatch omp_hello.sub 
</pre>

View the results from one of the sample OpenMP programs about task parallelism:
{::if resource.name != Weber}
<pre>
$ cat omp_hello.sub.omyjobid
SERIAL REGION:     Runhost:${resource.hostname}-a044.rcac.purdue.edu   Thread:0 of 1 thread    hello, world
PARALLEL REGION:   Runhost:${resource.hostname}-a044.rcac.purdue.edu   Thread:0 of ${resource.nodecores} threads   hello, world
PARALLEL REGION:   Runhost:${resource.hostname}-a044.rcac.purdue.edu   Thread:1 of ${resource.nodecores} threads   hello, world
   ...
</pre> 
{::else}
<pre>
$ cat omp_hello.sub.omyjobid
SERIAL REGION:     Runhost:${resource.hostname}-01.rcac.purdue.edu   Thread:0 of 1 thread    hello, world
PARALLEL REGION:   Runhost:${resource.hostname}-01.rcac.purdue.edu   Thread:0 of ${resource.nodecores} threads   hello, world
PARALLEL REGION:   Runhost:${resource.hostname}-01.rcac.purdue.edu   Thread:1 of ${resource.nodecores} threads   hello, world
   ...
</pre> 
{::/}

If the job failed to run, then view error messages in the file <kbd>slurm-myjobid.out</kbd>.

If an OpenMP program uses a lot of memory and ${resource.nodecores} threads use all of the memory of the compute node, use fewer processor cores (OpenMP threads) on that compute node.
