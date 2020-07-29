---
title: Running R jobs
tags:
 - slurm
 - wholenode
 - sharednode
---
# R

This section illustrates how to submit a small R job to a {::if resource.batchsystem == slurm}SLURM{::else}PBS{::/} queue. The example job computes a Pythagorean triple.

Prepare an R input file with an appropriate filename, here named <kbd>myjob.R</kbd>:

<pre>
# FILENAME:  myjob.R

# Compute a Pythagorean triple.
a = 3
b = 4
c = sqrt(a*a + b*b)
c     # display result
</pre> 

Prepare a job submission file with an appropriate filename, here named <kbd>myjob.sub</kbd>:
<pre>
#!/bin/bash
# FILENAME:  myjob.sub

module load r
{::if resource.batchsystem == pbs}
cd $PBS_O_WORKDIR
{::/}
# --vanilla:
# --no-save: do not save datasets at the end of an R session
R --vanilla --no-save &lt; myjob.R
</pre> 

[submit the job](/knowledge/${resource.hostname}/run/${resource.batchsystem}/submit)

[View job status](/knowledge/${resource.hostname}/run/${resource.batchsystem}/status)

[View results of the job](/knowledge/${resource.hostname}/run/${resource.batchsystem}/output)

For other examples or R jobs:
<ul>
 <li><a href="http://cran.r-project.org/manuals.html" target="_blank" rel="noopener">The R Manuals</a></li>
 <li><a href="http://www.mayin.org/ajayshah/KB/R/index.html" target="_blank" rel="noopener">Other R Examples</a></li>
 <li><a href="https://swcarpentry.github.io/r-novice-inflammation/" target="_blank" rel="noopener">Software Carpentry - Programing with R</a></li>
 <li><a href="http://www.datacarpentry.org/lessons/" target="_blannk" rel="noopener">Data Carpentry Lessons</a></li>
</ul>
