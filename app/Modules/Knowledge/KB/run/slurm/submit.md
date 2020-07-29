---
title: Submitting a Job
tags:
 - slurm
---

# Submitting a Job

Once you have a [job submission file](../script), you may submit this script to SLURM using the `sbatch` command.  SLURM will find, or wait for, available resources matching your request and run your job there. 

To submit your job to one compute node:

{::if resource.qsub_needs_gpu == 1}
    $ sbatch --nodes=1 --gpus-per-node=1 myjobsubmissionfile
{::else}
    $ sbatch --nodes=1 myjobsubmissionfile
{::/}

Slurm uses the word 'Account' and the option '-A' to specify different batch queues.
To submit your job to a specific queue:

{::if resource.qsub_needs_gpu == 1}
    $ sbatch --nodes=1  --gpus-per-node=1 -A ${resource.queue} myjobsubmissionfile
{::else}
    $ sbatch --nodes=1  -A ${resource.queue}  myjobsubmissionfile 
{::/}

{::if resource.qsub_needs_gpu == 1}
On ${resource.name}, you must specify the number of GPUs with the `--gpus-per-node` option.
{::/}

By default, each job receives 30 minutes of *wall time*, or clock time.  If you know that your job will not need more than a certain amount of time to run, request less than the maximum wall time, as this may allow your job to run sooner.  To request the 1 hour and 30 minutes of wall time:

{::if resource.qsub_needs_gpu == 1}
    $ sbatch -t 1:30:00 --nodes=1 --gpus-per-node=1 -p ${resource.queue} myjobsubmissionfile
{::else}
    $ sbatch -t 1:30:00 --nodes=1  -A ${resource.queue} myjobsubmissionfile
{::/}

The `--nodes` value indicates how many compute nodes you would like for your job.

{::if resource.name == Gilbreth}Each compute node in ${resource.name} has various cores per node. Refer to the [Hardware Overview](../../../overview) and [Queue Overview](../queues) for details.{::else}Each compute node in ${resource.name} has ${resource.nodecores} processor cores.{::/} 

In some cases, you may want to request multiple nodes. To utilize multiple nodes, you will need to have a program or code that is specifically programmed to use multiple nodes such as with MPI. Simply requesting more nodes will not make your work go faster. Your code must support this ability.

To request 2 compute nodes:

{::if resource.qsub_needs_gpu == 1}
    $ sbatch --nodes=2 --gpus-per-node=1 myjobsubmissionfile
{::else}
    $ sbatch --nodes=2  myjobsubmissionfile
{::/}

{::if resource.naccesspolicy == singlejob}SLURM jobs will have exclusive access to compute nodes and other jobs will not use the same nodes.  SLURM will allow a single job to run multiple `tasks`, and those tasks can be allocated resources with the `--ntasks` option.


{::else}
By default, jobs on ${resource.name} will share nodes with other jobs. <!-- If you wish to have exclusive access to a node, or only share with your own jobs, [request singleuser mode](../naccesspolicy) using `naccesspolicy=singlueuser`.  -->

{::/}
 
To submit a job using 1 compute node with 4 tasks, each using the default 1 core:

    $ sbatch --nodes=1 --ntasks=4 myjobsubmissionfile

If more convenient, you may also specify any command line options to `sbatch` from within your job submission file, using a special form of comment:

    #!/bin/sh -l
    # FILENAME:  myjobsubmissionfile
    
    #SBATCH -A myqueuename
{::if resource.qsub_needs_gpu == 1}
    #SBATCH --nodes=1 --gpus-per-node=1
{::else}
    #SBATCH --nodes=1
{::/}
    #SBATCH --time=1:30:00
    #SBATCH --job-name myjobname
    
    # Print the hostname of the compute node on which this job is running.
    /bin/hostname

If an option is present in both your job submission file and on the command line, the option on the command line will take precedence.

After you submit your job with `SBATCH`, it may wait in queue for minutes, hours, or even weeks.  How long it takes for a job to start depends on the specific queue, the resources and time requested, and other jobs already waiting in that queue requested as well.  It is impossible to say for sure when any given job will start.  For best results, request no more resources than your job requires.

Once your job is submitted, you can [monitor the job status](../status), wait for the job to complete, and [check the job output](../output).
