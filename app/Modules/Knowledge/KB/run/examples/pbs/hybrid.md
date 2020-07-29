---
title: Hybrid 
tags:
 - mpi
---
# Hybrid

A hybrid job combines both MPI and OpenMP to take advantage of multiple nodes while minimizing MPI traffic. 

This example shows how to submit a hybrid program compiled in the section [Compiling Hybrid Programs](../../../../compile/hybrid).

To run a hybrid program, set the environment variable OMP_NUM_THREADS to the desired number of threads:

In csh:
<pre>
$ setenv OMP_NUM_THREADS ${resource.nodecores}
</pre>

In bash:
<pre>
$ export OMP_NUM_THREADS=${resource.nodecores}
</pre>

Create a job submission file:
<pre>
#!/bin/sh -l
# FILENAME:  hybrid_hello.sub
{::if resource.qsub_needs_gpu == 1}
#PBS -l nodes=2:ppn=${resource.nodecores}:gpus=1,walltime=00:01:00
{::else}
#PBS -l nodes=2:ppn=${resource.nodecores},walltime=00:01:00
{::/}
#PBS -q ${resource.queue}

cd $PBS_O_WORKDIR
uniq <$PBS_NODEFILE >nodefile
export OMP_NUM_THREADS=${resource.nodecores}
mpiexec -n 2 -machinefile nodefile ./hybrid_hello
</pre> 

Since PBS sets the working directory to your home directory (as if you just logged in), you should use the <kbd>cd $PBS_O_WORKDIR</kbd> command to change the job's working directory to the directory from which you submitted the job. 

You run a hybrid program with the <kbd>mpiexec</kbd> command.  You may need to specify how to place the threads on the compute node. Several examples on how to specify thread placement with various MPI libraries are at the bottom of this section. 

Submit the hybrid job:
<pre>
$ qsub hybrid_hello.sub
179168.${resource.hostname}-adm.rcac.purdue.edu
</pre> 

View the results from one of the sample hybrid programs about task parallelism:
<pre>
$ cat hybrid_hello.sub.omyjobid
SERIAL REGION:     Runhost:${resource.hostname}-a044.rcac.purdue.edu   Thread:0 of 1 thread    hello, world
PARALLEL REGION:   Runhost:${resource.hostname}-a044.rcac.purdue.edu   Thread:0 of ${resource.nodecores} threads   hello, world
PARALLEL REGION:   Runhost:${resource.hostname}-a044.rcac.purdue.edu   Thread:1 of ${resource.nodecores} threads   hello, world
   ...
PARALLEL REGION:   Runhost:${resource.hostname}-a045.rcac.purdue.edu   Thread:0 of ${resource.nodecores} threads   hello, world
PARALLEL REGION:   Runhost:${resource.hostname}-a045.rcac.purdue.edu   Thread:1 of ${resource.nodecores} threads   hello, world
   ...
</pre> 
 
If the job failed to run, then view error messages in the file `hybrid_hello.sub.emyjobid`.

If a hybrid job uses a lot of memory and ${resource.nodecores} OpenMP threads per compute node uses all of the memory of the compute nodes, request more compute nodes (MPI ranks) and use fewer processor cores (OpenMP threads) on each compute node.

Prepare a job submission file with double the number of compute nodes (MPI ranks) and half the number of processor cores (OpenMP threads):
   
<pre>
#!/bin/sh -l
# FILENAME:  hybrid_hello.sub
#PBS -l nodes=4:ppn=${resource.nodecores},walltime=00:01:00
#PBS -q ${resource.queue}

cd $PBS_O_WORKDIR
uniq <$PBS_NODEFILE >nodefile
export OMP_NUM_THREADS=${resource.nodecores/2}
mpiexec -n 4 -machinefile nodefile ./hybrid_hello
</pre> 

Submit the job:
<pre>
$ qsub hybrid_hello.sub
</pre> 

View the results from one of the sample hybrid programs about task parallelism with double the number of compute nodes (MPI ranks) and half the number of processor cores (OpenMP threads):
<pre>
$ cat hybrid_hello.sub.omyjobid
SERIAL REGION:     Runhost:${resource.hostname}-a044.rcac.purdue.edu   Thread:0 of 1 thread    hello, world
PARALLEL REGION:   Runhost:${resource.hostname}-a044.rcac.purdue.edu   Thread:0 of ${resource.nodecores/2} threads   hello, world
PARALLEL REGION:   Runhost:${resource.hostname}-a044.rcac.purdue.edu   Thread:1 of ${resource.nodecores/2} threads   hello, world
   ...
PARALLEL REGION:   Runhost:${resource.hostname}-a045.rcac.purdue.edu   Thread:0 of ${resource.nodecores/2} threads   hello, world
PARALLEL REGION:   Runhost:${resource.hostname}-a045.rcac.purdue.edu   Thread:1 of ${resource.nodecores/2} threads   hello, world
   ...
PARALLEL REGION:   Runhost:${resource.hostname}-a046.rcac.purdue.edu   Thread:0 of ${resource.nodecores/2} threads   hello, world
PARALLEL REGION:   Runhost:${resource.hostname}-a046.rcac.purdue.edu   Thread:1 of ${resource.nodecores/2} threads   hello, world
   ...
PARALLEL REGION:   Runhost:${resource.hostname}-a047.rcac.purdue.edu   Thread:0 of ${resource.nodecores/2} threads   hello, world
PARALLEL REGION:   Runhost:${resource.hostname}-a047.rcac.purdue.edu   Thread:1 of ${resource.nodecores/2} threads   hello, world
   ...
</pre> 

### Thread placement

Compute nodes are made up of two or more processor chips, or <em>sockets</em>. Typically each socket shares a memory controller and communication busses for all of its cores. Consider these cores as having "shortcuts" to each other. Cores within a socket will be able to communicate faster and more efficiently amongst themselves than with another socket or compute node. MPI ranks should consequently be placed so that they can utilize these "shortcuts".  When running hybrid codes it is essential to specify this placement as by default some MPI libraries will limit a rank to a single core or may scatter a rank across processor chips.

Below are examples on how to specify this placement with several MPI libraries. <strong>Hybrid codes should be run within jobs requesting the entire node by either using <kbd>ppn=${resource.nodecores}</kbd> or the <kbd>-n</kbd> exclusive flag or the job may result in unexpected and poor thread placement.</strong>

OpenMPI 1.6.3
<pre>
mpiexec -cpus-per-rank $OMP_NUM_THREADS --bycore -np 2 -machinefile nodefile ./hybrid_loop
</pre>

OpenMPI 1.8
<pre>
mpiexec -map-by socket:pe=$OMP_NUM_THREADS -np 2 -machinefile nodefile ./hybrid_loop
</pre> 

Intel MPI
<pre>
mpiexec -np 2 -machinefile nodefile ./hybrid_loop
</pre> 

<strong>Notes</strong>
<ul>

 <li>Use <kbd>qlist</kbd> to determine which queues are available to you. The name of the queue which is available to everyone on ${resource.name} is "${resource.queue}".</li>
 <li>Invoking a hybrid program on ${resource.name} with <kbd>./program</kbd> is typically wrong, since this will use only one MPI process and defeats the purpose of using MPI.  Unless that is what you want (rarely the case), you should use <kbd>mpiexec</kbd> to invoke a hybrid program.</li>
 <li>In general, the exact order in which MPI processes of a hybrid program output similar write requests to an output file is random.</li>
</ul>
