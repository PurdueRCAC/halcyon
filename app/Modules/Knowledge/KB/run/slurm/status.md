---
title: Checking Job Status
tags:
 - slurm
---

# Checking Job Status

Once a job is [submitted](../submit) there are several commands you can use to monitor the progress of the job.

To see your jobs, use the <kbd>squeue -u</kbd> command and specify your username:</p>
(Remember, in our SLURM environment a queue is referred to as an 'Account')
<pre>                                                                                                                                              
$ squeue -u ${user.username}                                                                                                                     
                                                                                                                                                   
    JOBID   ACCOUNT    NAME          USER   ST    TIME   NODES  NODELIST(REASON)
   182792   ${resource.queue}    job1    ${user.username}    R   20:19       1  ${resource.hostname}-a000
   185841   ${resource.queue}    job2    ${user.username}    R   20:19       1  ${resource.hostname}-a001
   185844   ${resource.queue}    job3    ${user.username}    R   20:18       1  ${resource.hostname}-a002
   185847   ${resource.queue}    job4    ${user.username}    R   20:18       1  ${resource.hostname}-a003
</pre>                                                                                                                                             

To retrieve useful information about your queued or running job, use the <kbd>scontrol show job</kbd> command with your job's ID number. The output should look similar to the following:

<pre>
$ scontrol show job 3519

JobId=3519 JobName=t.sub
   UserId=${user.username} GroupId=mygroup MCS_label=N/A
   Priority=3 Nice=0 Account=(null) QOS=(null)
   JobState=PENDING Reason=BeginTime Dependency=(null)
   Requeue=1 Restarts=0 BatchFlag=1 Reboot=0 ExitCode=0:0
   RunTime=00:00:00 TimeLimit=7-00:00:00 TimeMin=N/A
   SubmitTime=2019-08-29T16:56:52 EligibleTime=2019-08-29T23:30:00
   AccrueTime=Unknown
   StartTime=2019-08-29T23:30:00 EndTime=2019-09-05T23:30:00 Deadline=N/A
   PreemptTime=None SuspendTime=None SecsPreSuspend=0
   LastSchedEval=2019-08-29T16:56:52
   Partition=workq AllocNode:Sid=mack-fe00:54476
   ReqNodeList=(null) ExcNodeList=(null)
   NodeList=(null)
   NumNodes=1 NumCPUs=2 NumTasks=2 CPUs/Task=1 ReqB:S:C:T=0:0:*:*
   TRES=cpu=2,node=1,billing=2
   Socks/Node=* NtasksPerN:B:S:C=0:0:*:* CoreSpec=*
   MinCPUsNode=1 MinMemoryNode=0 MinTmpDiskNode=0
   Features=(null) DelayBoot=00:00:00
   OverSubscribe=OK Contiguous=0 Licenses=(null) Network=(null)
   Command=/home/${user.username}/jobdir/myjobfile.sub
   WorkDir=/home/${user.username}/jobdir
   StdErr=/home/${user.username}/jobdir/slurm-3519.out
   StdIn=/dev/null
   StdOut=/home/${user.username}/jobdir/slurm-3519.out
   Power=

</pre>

There are several useful bits of information in this output.

* `JobState` lets you know if the job is Pending, Running, Completed, or Held.
* `RunTime and TimeLimit` will show how long the job has run and its maximum time.
* `SubmitTime` is when the job was submitted to the cluster.
*  The job's number of Nodes, Tasks, Cores (CPUs) and CPUs per Task are shown.
* `WorkDir` is the job's working directory.
* `StdOut` and `Stderr` are the locations of stdout and stderr of the job, respectively.
* `Reason` will show why a `PENDING` job isn't running. The above error says that it has been requested to start at a specific, later time. 

