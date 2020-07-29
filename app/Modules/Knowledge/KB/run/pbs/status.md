---
title: Checking Job Status
tags:
 - wholenode
 - sharednode
---

# Checking Job Status

Once a job is [submitted](../submit) there are several commands you can use to monitor the progress of the job.

To see yourjobs, use the <kbd>qstat -u</kbd> command and specify your username:</p>
<pre>
$ qstat -a -u ${user.username}

${resource.hostname}-adm.rcac.purdue.edu:
                                                                   Req'd  Req'd   Elap
Job ID             Username     Queue    Jobname    SessID NDS TSK Memory Time  S Time
------------------ ----------   -------- ---------- ------ --- --- ------ ----- - -----
182792.${resource.hostname}-adm  ${user.username}   ${resource.queue} job1        28422   1   4    --  23:00 R 20:19
185841.${resource.hostname}-adm  ${user.username}   ${resource.queue} job2        24445   1   4    --  23:00 R 20:19
185844.${resource.hostname}-adm  ${user.username}   ${resource.queue} job3        12999   1   4    --  23:00 R 20:18
185847.${resource.hostname}-adm  ${user.username}   ${resource.queue} job4        13151   1   4    --  23:00 R 20:18
</pre>

To retrieve useful information about your queued or running job, use the <kbd>checkjob</kbd> command with your job's ID number. The output should look similar to the following:

<pre>
$ checkjob -v 163000

job 163000 (RM job '163000.${resource.hostname}-adm.rcac.purdue.edu')

AName: test
State: Idle
Creds:  user:${user.username}  group:mygroup  class:myqueue
WallTime:   00:00:00 of 20:00:00
SubmitTime: Wed Apr 18 09:08:37
  (Time Queued  Total: 1:24:36  Eligible: 00:00:23)

NodeMatchPolicy: EXACTNODE
Total Requested Tasks: 2
Total Requested Nodes: 1

Req[0]  TaskCount: 2  Partition: ALL
TasksPerNode: 2  NodeCount:  1


Notification Events: JobFail

IWD:            /home/${user.username}/gaussian
UMask:          0000
OutputFile:     ${resource.hostname}-fe00.rcac.purdue.edu:/home/${user.username}/gaussian/test.o163000
ErrorFile:      ${resource.hostname}-fe00.rcac.purdue.edu:/home/${user.username}/gaussian/test.e163000
User Specified Partition List:   ${resource.hostname}-adm,SHARED
Partition List: ${resource.hostname}-adm
SrcRM:          ${resource.hostname}>-adm  DstRM: ${resource.hostname}-adm  DstRMJID: 163000.${resource.hostname}-adm.rcac.purdue.edu
Submit Args:    -l nodes=1:ppn=2,walltime=20:00:00 -q myqueue
Flags:          RESTARTABLE
Attr:           checkpoint
StartPriority:  1000
PE:             2.00
NOTE:  job violates constraints for partition ${resource.hostname}-adm (job 163000 violates active HARD MAXPROC limit of 160 for class myqueue  partition ALL (Req: 2  InUse: 160))

BLOCK MSG: job 163000 violates active HARD MAXPROC limit of 160 for class myqueue  partition ALL (Req: 2  InUse: 160) (recorded at last scheduling iteration)
</pre>

There are several useful bits of information in this output.

* `State` lets you know if the job is Idle, Running, Completed, or Held.
* `WallTime` will show how long the job has run and its maximum time.
* `SubmitTime` is when the job was submitted to the cluster.
* `Total Requested Tasks` is the total number of cores used for the job.
* `Total Requested Nodes` and NodeCount are the number of nodes used for the job.
* `TasksPerNode` is the number of cores used per node.
* `IWD` is the job's working directory.
* `OutputFile` and `ErrorFile` are the locations of stdout and stderr of the job, respectively.
* `Submit Args` will show the arguments given to the qsub command.
* `NOTE/BLOCK MSG` will show details on why the job isn't running. The above error says that all the cores are in use on that queue and the job has to wait. Other errors may give insight as to why the job fails to start or is held.


To view the output of a running job, use the <kbd>qpeek</kbd> command with your job's ID number. The <kbd>-f</kbd> option will continually output to the screen similar to <kbd>tail -f</kbd>, while qpeek without options will just output the whole file so far. Here is an example output from an application:

<pre>
$ qpeek -f 1651025
TIMING: 600  CPU: 97.0045, 0.0926592/step  Wall: 97.0045, 0.0926592/step, 0.11325 hours remaining, 809.902344 MB of memory in use.
ENERGY:     600    359272.8746    280667.4810     81932.7038      5055.7519       -4509043.9946    383233.0971         0.0000         0.0000    947701.9550       -2451180.1312       298.0766  -3398882.0862  -2442581.9707       298.2890           1125.0475        77.0325  10193721.6822         3.5650         3.0569

TIMING: 800  CPU: 118.002, 0.104987/step  Wall: 118.002, 0.104987/step, 0.122485 hours remaining, 809.902344 MB of memory in use.
ENERGY:     800    360504.1138    280804.0922     82052.0878      5017.1543       -4511471.5475    383214.3057         0.0000         0.0000    946597.3980       -2453282.3958       297.7292  -3399879.7938  -2444652.9520       298.0805            978.4130        67.0123  10193578.8030        -0.1088         0.2596

TIMING: 1000  CPU: 144.765, 0.133817/step  Wall: 144.765, 0.133817/step, 0.148686 hours remaining, 809.902344 MB of memory in use.
ENERGY:    1000    361525.2450    280225.2207     81922.0613      5126.4104       -4513315.2802    383460.2355         0.0000         0.0000    947232.8722       -2453823.2352       297.9291  -3401056.1074  -2445219.8163       297.9184            823.8756        43.2552  10193174.7961        -0.7191        -0.2392
...
</pre>
