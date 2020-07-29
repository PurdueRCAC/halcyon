---
title: Submitting a Job
tags:
 - wholenode
 - sharednode
---

# Submitting a Job

Once you have a [job submission file](../script), you may submit this script to PBS using the `qsub` command.  PBS will find, or wait for, available resources matching your request and run your job there. 

To submit your job to one compute node:

{::if resource.qsub_needs_gpu == 1}
    $ qsub -lnodes=1:ppn=1:gpus=1 myjobsubmissionfile
{::else}
    $ qsub -lnodes=1:ppn=${resource.nodecores} myjobsubmissionfile
{::/}

To submit your job to a specific queue:

{::if resource.qsub_needs_gpu == 1}
    $ qsub -lnodes=1:ppn=1:gpus=1 myjobsubmissionfile -q ${resource.queue}
{::else}
    $ qsub -lnodes=1:ppn=${resource.nodecores} myjobsubmissionfile -q ${resource.queue}
{::/}

{::if resource.qsub_needs_gpu == 1}
On ${resource.name}, you must specify the number of GPUs with the `gpus` option.
{::/}

By default, each job receives 30 minutes of *wall time*, or clock time.  If you know that your job will not need more than a certain amount of time to run, request less than the maximum wall time, as this may allow your job to run sooner.  To request the 1 hour and 30 minutes of wall time:

{::if resource.qsub_needs_gpu == 1}
    $ qsub  -l walltime=01:30:00 -lnodes=1:ppn=1:gpus=1 myjobsubmissionfile -q ${resource.queue}
{::else}
    $ qsub  -l walltime=01:30:00 -lnodes=1:ppn=${resource.nodecores} myjobsubmissionfile -q ${resource.queue}
{::/}

The `nodes` value indicates how many compute nodes you would like for your job and the `ppn` value indicates the number of processors per node.

{::if resource.name == Gilbreth}Each compute node in ${resource.name} has various cores per node. Refer to the [Hardware Overview](../../../overview) and [Queue Overview](../queues) for details.{::else}Each compute node in ${resource.name} has ${resource.nodecores} processor cores.{::/} 

In some cases, you may want to request multiple nodes. To utilize multiple nodes, you will need to have a program or code that is specifically programmed to use multiple nodes such as with [MPI](../../examples/pbs/mpi). Simply requesting more nodes will not make your work go faster. Your code must support this ability.

To request 2 compute nodes with ${resource.nodecores} cores per node:

{::if resource.qsub_needs_gpu == 1}
    $ qsub -l nodes=2:ppn=${resource.nodecores}:gpus=1 myjobsubmissionfile
{::else}
    $ qsub -l nodes=2:ppn=${resource.nodecores} myjobsubmissionfile
{::/}

{::if resource.naccesspolicy == singlejob}Normally, a job will have exclusive access to compute nodes and other jobs will not use the same nodes. If you want to request a single, or a subset of processor cores you need to explicitly [enable node sharing](../naccesspolicy) using `naccesspolicy=singlueuser`. 

Jobs that share nodes will have its cores reserved for that job, however, all jobs will share the compute node's memory and network throughput. If a shared job consumes a large amount of a shared resource, such as memory, these jobs may interfere with other jobs and negatively impact performance of all jobs on the compute node. To reduce such adverse effects, we recommend users to share nodes only with their own jobs.

Jobs that do not explicitly allow sharing [will not share nodes](../naccesspolicy) with sharing-enabled jobs. **These jobs will consume the full ${resource.nodecores} cores from your submit queue's available cores because your job is effectively using a whole compute node.** Requesting multiple partial compute nodes on ${resource.name} is not permitted. You may submit multiple jobs that request one partial compute node and these jobs will be packed together onto the same compute nodes if node sharing is enabled.
{::else}
By default, jobs on ${resource.name} will share nodes with other jobs. If you wish to have exclusive access to a node, or only share with your own jobs, [request singleuser mode](../naccesspolicy) using `naccesspolicy=singlueuser`. 

Jobs that share nodes will have its cores reserved for that job, however, all jobs will share the compute node's memory and network throughput. If a shared job consumes a large amount of a shared resource, such as memory, these jobs may interfere with other jobs and negatively impact performance of all jobs on the compute node. To reduce such adverse effects, we recommend users to share nodes only with their own jobs.
{::/}
 
To submit a job using 1 compute node with 4 processor cores:

    $ qsub -l nodes=1:ppn=4,naccesspolicy=singleuser myjobsubmissionfile 

**Please note that [when `naccesspolicy=singleuser` is specified](../naccesspolicy), the scheduler ensures that only jobs from the same user are allocated on a node. So, if your `singleuser` jobs do not fill all the cores on a node, you would still occupy ${resource.nodecores} cores in your queue.**

If more convenient, you may also specify any command line options to `qsub` from within your job submission file, using a special form of comment:

    #!/bin/sh -l
    # FILENAME:  myjobsubmissionfile
    
    #PBS -q myqueuename
{::if resource.qsub_needs_gpu == 1}
    #PBS -l nodes=1:ppn=1:gpus=1,naccesspolicy=singleuser
{::else}
    #PBS -l nodes=1:ppn=1,naccesspolicy=singleuser
{::/}
    #PBS -l walltime=01:30:00
    #PBS -N myjobname
    
    # Print the hostname of the compute node on which this job is running.
    /bin/hostname

If an option is present in both your job submission file and on the command line, the option on the command line will take precedence.

After you submit your job with `qsub`, it may wait in queue for minutes, hours, or even weeks.  How long it takes for a job to start depends on the specific queue, the resources and time requested, and other jobs already waiting in that queue requested as well.  It is impossible to say for sure when any given job will start.  For best results, request no more resources than your job requires.

Once your job is submitted, you can [monitor the job status](../status), wait for the job to complete, and [check the job output](../output).
