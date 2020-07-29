---
title: MPI 
tags:
 - mpi
---
# MPI

An MPI job is a set of processes that take advantage of multiple compute nodes by communicating with each other. OpenMPI and Intel MPI (IMPI) are implementations of the MPI standard.

This section shows how to submit one of the MPI programs compiled in the section [Compiling MPI Programs](../../../../compile/mpi). 

Use <kbd>module load</kbd> to set up the paths to access these libraries. Use <kbd>module avail</kbd> to see all MPI packages installed on ${resource.name}.

Create a job submission file:
<pre>
# FILENAME:  mpi_hello.sub
{::if resource.qsub_needs_gpu == 1}
#PBS -l nodes=2:ppn=${resource.nodecores}:gpus=1,walltime=00:01:00
{::else}
#PBS -l nodes=2:ppn=${resource.nodecores},walltime=00:01:00
{::/}
#PBS -q ${resource.queue}

cd $PBS_O_WORKDIR

# Load the default module set to get the recommended MPI library.
module load rcac

mpiexec -n ${resource.nodecores*2} ./mpi_hello
</pre>

Since PBS sets the working directory to your home directory (as if you just logged in), you should use the <kbd>cd $PBS_O_WORKDIR</kbd> command to change the job's working directory to the directory from which you submitted the job.

You run an MPI program with the <kbd>mpiexec</kbd> command.  The number of processes is requested with the <kbd>-n</kbd> option and is typically equal to the total number of processor cores you request from PBS (more on this below).

Submit the MPI job: 

<pre>
$ qsub ./mpi_hello.sub
</pre> 

View results in the output file:
<pre>
$ cat mpi_hello.sub.omyjobid
Runhost:${resource.hostname}-a010.rcac.purdue.edu   Rank:0 of ${resource.nodecores*2} ranks   hello, world
Runhost:${resource.hostname}-a010.rcac.purdue.edu   Rank:1 of ${resource.nodecores*2} ranks   hello, world
...
Runhost:${resource.hostname}-a011.rcac.purdue.edu   Rank:${resource.nodecores} of ${resource.nodecores*2} ranks   hello, world
Runhost:${resource.hostname}-a011.rcac.purdue.edu   Rank:${resource.nodecores+1} of ${resource.nodecores*2} ranks   hello, world
...
</pre> 

If the job failed to run, then view error messages in the file <kbd>mpi_hello.sub.emyjobid</kbd>.

If an MPI job uses a lot of memory and ${resource.nodecores} MPI ranks per compute node use all of the memory of the compute nodes, request more compute nodes, while keeping the total number of MPI ranks unchanged.

Submit the job with double the number of compute nodes and modify the node list to halve the number of MPI ranks per compute node. The `awk` line outputs every other line of the original nodefile and writes into a new nodefile:
<pre>
# FILENAME:  mpi_hello.sub
{::if resource.qsub_needs_gpu == 1}
#PBS -l nodes=4:ppn=${resource.nodecores}:gpus=1,walltime=00:01:00
{::else}
#PBS -l nodes=4:ppn=${resource.nodecores},walltime=00:01:00
{::/}
#PBS -q ${resource.queue}

cd $PBS_O_WORKDIR

# select every 2nd line #
awk 'NR%2 != 0' < $PBS_NODEFILE > nodefile

mpiexec -n ${resource.nodecores*2} -machinefile ./nodefile ./mpi_hello
</pre>

<pre>
$ qsub ./mpi_hello.sub
</pre> 

View results in the output file:
<pre>
$ cat mpi_hello.sub.omyjobid
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
 <li>Use <kbd>qlist</kbd> to determine which queues are available to you. The name of the queue which is available to everyone on ${resource.name} is "${resource.queue}".</li>
 <li>Invoking an MPI program on ${resource.name} with <kbd>./program</kbd> is typically wrong, since this will use only one MPI process and defeat the purpose of using MPI. Unless that is what you want (rarely the case), you should use <kbd>mpiexec</kbd> to invoke an MPI program.</li>
 <li>In general, the exact order in which MPI ranks output similar write requests to an output file is random.</li>
 </ul>
 <p>For an introductory tutorial on how to write your own MPI programs:</p>
<ul>
 <li><a href="/tutorials/mpi/">Introduction to Parallel Programming with MPI</a></li>
</ul>
