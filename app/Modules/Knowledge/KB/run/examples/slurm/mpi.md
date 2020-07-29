---
title: MPI 
tags:
 - mpislurm
---
# MPI

An MPI job is a set of processes that take advantage of multiple compute nodes by communicating with each other. OpenMPI and Intel MPI (IMPI) are implementations of the MPI standard.

This section shows how to submit one of the MPI programs compiled in the section [Compiling MPI Programs](../../../../compile/mpi). 

Use <kbd>module load</kbd> to set up the paths to access these libraries. Use <kbd>module avail</kbd> to see all MPI packages installed on ${resource.name}.

Create a job submission file:
<pre>
#!/bin/bash
# FILENAME:  mpi_hello.sub
#SBATCH  --nodes=2
#SBATCH  --ntasks-per-node=${resource.nodecores}
#SBATCH  --time=00:01:00
#SBATCH  -A ${resource.queue}


srun -n ${resource.nodecores*2} ./mpi_hello
</pre>


SLURM can run an MPI program with the <kbd>srun</kbd> command.  The number of processes is requested with the <kbd>-n</kbd> option. If you do not specify the <kbd>-n</kbd> option, it will default to the total number of processor cores you request from SLURM.

If the code is built with OpenMPI, it can be run with a simple <kbd>srun -n </kbd> command.  If it is built with Intel IMPI, then you also need to add the <kbd>--mpi=pmi2</kbd> option: <kbd> srun --mpi=pmi2 -n ${resource.nodecores*2} ./mpi_hello</kbd> in this example.

Submit the MPI job: 

<pre>
$ sbatch ./mpi_hello.sub
</pre> 

View results in the output file:
<pre>
$ cat slurm-myjobid.out
Runhost:${resource.hostname}-a010.rcac.purdue.edu   Rank:0 of ${resource.nodecores*2} ranks   hello, world
Runhost:${resource.hostname}-a010.rcac.purdue.edu   Rank:1 of ${resource.nodecores*2} ranks   hello, world
...
Runhost:${resource.hostname}-a011.rcac.purdue.edu   Rank:${resource.nodecores} of ${resource.nodecores*2} ranks   hello, world
Runhost:${resource.hostname}-a011.rcac.purdue.edu   Rank:${resource.nodecores+1} of ${resource.nodecores*2} ranks   hello, world
...
</pre> 

If the job failed to run, then view error messages in the output file.

If an MPI job uses a lot of memory and ${resource.nodecores} MPI ranks per compute node use all of the memory of the compute nodes, request more compute nodes, while keeping the total number of MPI ranks unchanged.

Submit the job with double the number of compute nodes and modify the resource request to halve the number of MPI ranks per compute node.
<pre>
#!/bin/bash
# FILENAME:  mpi_hello.sub
{::if resource.qsub_needs_gpu == 1}
#SBATCH --nodes=4
#SBATCH --ntasks-per-node=${resource.nodecores/2}
#SBATCH -G 1
#SBATCH -t 00:01:00
{::else}
#SBATCH --nodes=4                                                                                                                                        
#SBATCH --ntasks-per-node=${resource.nodecores/2}                                                                                                        
#SBATCH -t 00:01:00                                                                                                                                      
{::/}
#SBATCH -A ${resource.queue}


srun -n ${resource.nodecores*2} ./mpi_hello
</pre>

<pre>
$ sbatch ./mpi_hello.sub
</pre> 

View results in the output file:
<pre>
$ cat slurm-myjobid.out
Runhost:${resource.hostname}-a010.rcac.purdue.edu   Rank:0 of ${resource.nodecores*2} ranks   hello, world
Runhost:${resource.hostname}-a010.rcac.purdue.edu   Rank:1 of ${resource.nodecores*2} ranks   hello, world
...
Runhost:${resource.hostname}-a011.rcac.purdue.edu   Rank:${resource.nodecores/2} of ${resource.nodecores*2} ranks   hello, world
...
Runhost:${resource.hostname}-a012.rcac.purdue.edu   Rank:${resource.nodecores} of ${resource.nodecores*2} ranks   hello, world
...
Runhost:${resource.hostname}-a013.rcac.purdue.edu   Rank:${resource.nodecores*1.5} of ${resource.nodecores*2} ranks   hello, world
...
</pre> 

<strong>Notes</strong>

<ul>
 <li>Use <kbd>slist</kbd> to determine which queues (--account or -A option) are available to you. The name of the queue which is available to everyone on ${resource.name} is "${resource.queue}".</li>
 <li>Invoking an MPI program on ${resource.name} with <kbd>./program</kbd> is typically wrong, since this will use only one MPI process and defeat the purpose of using MPI. Unless that is what you want (rarely the case), you should use <kbd>srun</kbd> or <kbd>mpiexec</kbd> to invoke an MPI program.</li>
 <li>In general, the exact order in which MPI ranks output similar write requests to an output file is random.</li>
 </ul>
 <p>For an introductory tutorial on how to write your own MPI programs:</p>
<ul>
 <li><a href="/tutorials/mpi/">Introduction to Parallel Programming with MPI</a></li>
</ul>
