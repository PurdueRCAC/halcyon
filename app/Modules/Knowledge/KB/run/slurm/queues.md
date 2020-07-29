---
title: Queues
tags:
 - slurm
---

{::if resource.queuemodel == dedicated}
### Partner Queues
${resource.name}, as a community cluster, has one or more queues dedicated to and named after each partner who has purchased access to the cluster.  These queues provide partners and their researchers with priority access to their portion of the cluster. Jobs in these queues are typically limited to 336 hours. **The expectation is that any jobs submitted to named partner queues will start within 4 hours, assuming the queue currently has enough capacity for the job** (that is, your labmates aren't using all of the cores currently). 
{::elseif resource.queuemodel == partner}
### Partner Queue
${resource.name} provides partners and their researchers shared access to the cluster through a shared `partner` queue. This is the default queue for submitting short to moderately long jobs. It allows jobs up to 24 hours and lets researchers run up to 4 jobs simultaneously. **The expectation is that any jobs submitted to the <kbd>partner</kbd> queue will start within 4 hours, assuming the queue currently has enough capacity for the job**.

### Dedicated Queues
If a research group has purchased dedicated access to ${resource.name} there will be a queue named after the faculty or research group.  These queues provide faculty and their researchers with priority access to their portion of the cluster. Jobs in these queues are typically limited to 336 hours. **The expectation is that any jobs submitted to dedicated queues will start within 4 hours, assuming the queue currently has enough capacity for the job** (that is, your labmates aren't using all of the cores currently). 
{::/}

{::if resource.name == Scholar}
### Scholar Queue
This is the default queue for submitting jobs on Scholar. The maximum walltime on <kbd>scholar</kbd> queue is 4 hours.

### Long Queue
If your job requires more than 4 hours to complete, you can submit it to the <kbd>long</kbd> queue. The maximum walltime is 3 days. There are only 5 nodes in this queue, so you may have to wait for some time to get access to a node.

### GPU Queue 
If your job needs access to an Nvidia GPU accelerator, then use the <kbd>gpu</kbd> queue. The maximum walltime is 4 hours.
{::/}

{::if resource.name == Gilbreth}
### Long Queue
If your job requires more than 24 hours to complete, you can submit it to the <kbd>long</kbd> queue. There are only 4 nodes in this queue, so you may have to wait for a considerable amount of time to get access to a node.

### High Memory Queue
If your job requires GPUs with large memory (32GB), but can finish in a short time, use the <kbd>highmem</kbd> queue. This queue shares nodes with the <kbd>training</kbd> queue, so you may need to wait until a node becomes available.

### Training Queue 
If your job can scale on 4 GPUs or more and it requires longer than 24 hours, then use the <kbd>training</kbd> queue. Since the <kbd>training</kbd> nodes have specialty hardware and are few in number, these are restricted to users whose workloads can scale well with number of GPUs. Please note that ITaP staff may ask you to provide evidence that your jobs can fully utilize the GPUs, before granting access to this queue. There are only 3 nodes in this queue, so you may have to wait a considerable amount of time before your job is scheduled.
{::/}

{::if resource.standby == true}
### Standby Queue
Additionally, community clusters provide a "${resource.queue}" queue which is available to all cluster users.  This "${resource.queue}" queue allows users to utilize portions of the cluster that would otherwise be idle, but at a lower priority than partner-queue jobs, and with a relatively short time limit, to ensure "${resource.queue}" jobs will not be able to tie up resources and prevent partner-queue jobs from running quickly. Jobs in standby are limited to 4 hours. **There is no expectation of job start time.** If the cluster is very busy with partner queue jobs, or you are requesting a very large job, jobs in standby may take hours or days to start.
{::/}

{::if resource.debug == true}
### Debug Queue
The debug queue allows you to quickly start small, short, interactive jobs in order to debug code, test programs, or test configurations. You are limited to one running job at a time in the queue, and you may run up to two compute nodes for 30 minutes. The expectation is that debug jobs should start within a couple of minutes, assuming all of its dedicated nodes are not taken by others.
{::/}

### List of Queues
{::if resource.batchsystem == slurm}
To see a list of all queues on ${resource.name} that you may submit to, use the <kbd>slist</kbd> command

This lists each queue you can submit to, the number of nodes allocated to the queue, how many are available to run jobs, and the maximum walltime you may request.  Options to the command will give more detailed information.  This command can be used to get a general idea of how busy an individual queue is and how long you may have to wait for your job to start.


{::if resource.name == Gilbreth}
### Summary of Queues

Gilbreth contains several queues and heterogeneous hardware consisting of different number of cores and different GPU models. Some queues are backed by only one node type, but some queues may land on multiple node types. On queues that land on multiple node types, you will need to be mindful of your resource request. Below are the current combinations of queues, GPU types, and resources you may request.


| Queue	   | GPU Type       | Number of cores (#GPUs) / node | Intended use-case | Number of nodes | Max walltime |
| -------- | -------------- | ------------------------------ | ----------------- | --------------- | ------------ |
| partner  | P100 (16 GB) or V100 (16 GB) | 16, 20, or 24 (2) | Short to moderately long jobs | 48 | 24 hours |
| long     | V100 (16 GB) | 20 (2) | Long jobs | 4 | 7 days |
| training | V100 (32 GB) | 20 (4) | Long jobs such as Deep Learning model training, code must scale to 4-GPUs or more | 3* | 7 days |
| highmem  | V100 (32GB)  | 20 (4) | Short jobs that require large GPU memory | 3* | 4 hours |
| debug    | P100 (16GB) or V100 (16 GB) | 16 (2) | Quick testing | 1 | 30 mins |
{::/}
