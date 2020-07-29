---
title: Implicit Parallelism
tags:
 - slurm
 - wholenode
 - sharednode
---
# Implicit Parallelism

MATLAB implements <em>implicit parallelism</em> which is automatic multithreading of many computations, such as matrix multiplication, linear algebra, and performing the same operation on a set of numbers. This is different from the explicit parallelism of the Parallel Computing Toolbox.

MATLAB offers implicit parallelism in the form of thread-parallel enabled functions. Since these processor cores, or threads, share a common memory, many MATLAB functions contain multithreading potential.  Vector operations, the particular application or algorithm, and the amount of computation (array size) contribute to the determination of whether a function runs serially or with multithreading.

When your job triggers implicit parallelism, it attempts to allocate its threads on all processor cores of the compute node on which the MATLAB client is running, including processor cores running other jobs.  This competition can degrade the performance of all jobs running on the node.

>When you know that you are coding a serial job but are unsure whether you are using thread-parallel enabled operations, run MATLAB with implicit parallelism turned off.  Beginning with the R2009b, you can turn multithreading off by starting MATLAB with <kbd>-singleCompThread</kbd>:

<pre>
$ matlab -nodisplay -singleCompThread -r mymatlabprogram
</pre>

When you are using implicit parallelism, make sure you request exclusive access to a compute node, as MATLAB has no facility for sharing nodes.

For more information about MATLAB's implicit parallelism:
<ul>
 <li><a href="http://www.mathworks.com/support/solutions/en/data/1-4PG4AN/index.html?solution=1-4PG4AN" target="_blank" rel="noopener">Which MATLAB functions benefit from multithreaded computation?</a></li>
 <li><a href="http://www.mathworks.com/support/solutions/en/data/1-3P8CC5/index.html" target="_blank" rel="noopener">What is the difference between "MATLAB as a fully-multithreaded application" versus "multithreaded computation"?</a></li>
 <li><a href="http://www.mathworks.com/" target="_blank" rel="noopener">MathWorks Website</a></li>
</ul>
