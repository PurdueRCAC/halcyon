---
title: Gaussian
tags:
 - wholenode
 - sharednode
---

# Gaussian

Gaussian is a computational chemistry software package which works on electronic structure. This section illustrates how to submit a small Gaussian job to a PBS queue. This Gaussian example runs the Fletcher-Powell multivariable optimization.

Prepare a Gaussian input file with an appropriate filename, here named <kbd>myjob.com</kbd>. The final blank line is necessary:

<pre>
#P TEST OPT=FP STO-3G OPTCYC=2

STO-3G FLETCHER-POWELL OPTIMIZATION OF WATER

0 1
O
H 1 R
H 1 R 2 A

R 0.96
A 104.

</pre> 

To submit this job, load Gaussian then run the provided script, named <kbd>subg16</kbd>. This job uses one compute node with ${resource.nodecores} processor cores:

<pre>
$ module load gaussian16
{::if resource.qsub_needs_gpu == 1}
$ subg16 myjob -l nodes=1:ppn=${resource.nodecores}:gpus=1
{::else}
$ subg16 myjob -l nodes=1:ppn=${resource.nodecores}
{::/}
</pre>

View job status:

<pre>
$ qstat -u ${user.username}
</pre>

View results in the file for Gaussian output, here named <kbd>myjob.log</kbd>. Only the first and last few lines appear here:

<pre> 
 Entering Gaussian System, Link 0=/apps/cent7/gaussian/g16-A.03/g16-haswell/g16/g16
 Initial command:
{::if resource.letteredscratch == true}
 /apps/cent7/gaussian/g16-A.03/g16-haswell/g16/l1.exe ${resource.scratch}/${user.usernameletter}/${user.username}/gaussian/Gau-7781.inp -scrdir=${resource.scratch}/${user.usernameletter}/${user.username}/gaussian/
{::else}
 /apps/cent7/gaussian/g16-A.03/g16-haswell/g16/l1.exe ${resource.scratch}/${user.username}/gaussian/Gau-7781.inp -scrdir=${resource.scratch}/${user.username}/gaussian/
{::/}
 Entering Link 1 = /apps/cent7/gaussian/g16-A.03/g16-haswell/g16/l1.exe PID=      7782.

 Copyright (c) 1988,1990,1992,1993,1995,1998,2003,2009,2016,
            Gaussian, Inc.  All Rights Reserved.

.
.
.

 Job cpu time:       0 days  0 hours  3 minutes 28.2 seconds.
 Elapsed time:       0 days  0 hours  0 minutes 12.9 seconds.
 File lengths (MBytes):  RWF=     17 Int=      0 D2E=      0 Chk=      2 Scr=      2
 Normal termination of Gaussian 16 at Tue May  1 17:12:00 2018.
real 13.85
user 202.05
sys 6.12
Machine:
${resource.hostname}-a012
${resource.hostname}-a012
${resource.hostname}-a012
${resource.hostname}-a012
${resource.hostname}-a012
${resource.hostname}-a012
${resource.hostname}-a012
${resource.hostname}-a012
</pre> 

### Examples of Gaussian PBS Job Submissions

Submit job using ${resource.nodecores} processor cores on a single node:

<pre>
{::if resource.qsub_needs_gpu == 1}
$ subg16 myjob -l nodes=1:ppn=${resource.nodecores}:gpus=${resource.nodegpus},walltime=24:00:00 -q ${resource.queue}
{::else}
$ subg16 myjob -l nodes=1:ppn=${resource.nodecores},walltime=200:00:00 -q myqueuename
{::/}
</pre> 

Submit job using ${resource.nodecores} processor cores on each of 2 nodes:

<pre>
{::if resource.qsub_needs_gpu == 1}
$ subg16 myjob -l nodes=2:ppn=${resource.nodecores}:gpus=${resource.nodegpus},walltime=24:00:00 -q ${resource.queue}
{::else}
$ subg16 myjob -l nodes=2:ppn=${resource.nodecores},walltime=200:00:00 -q myqueuename
{::/}
</pre>

For more information about Gaussian:
<ul>
 <li><a href="http://www.gaussian.com/" target="_blank" rel="noopener">Gaussian Website</a></li>
</ul>
