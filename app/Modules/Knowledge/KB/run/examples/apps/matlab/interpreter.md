---
title: Matlab Script (.m File)
tags:
 - slurm
 - wholenode
 - sharednode
---
# Matlab Script (.m File)

This section illustrates how to submit a small, serial, MATLAB program as a job to a batch queue.  This MATLAB program prints the name of the run host and gets three random numbers.

Prepare a MATLAB script <kbd>myscript.m</kbd>, and a MATLAB function file <kbd>myfunction.m</kbd>:

<pre>
% FILENAME:  myscript.m

% Display name of compute node which ran this job.
[c name] = system('hostname');
fprintf('\n\nhostname:%s\n', name);

% Display three random numbers.
A = rand(1,3);
fprintf('%f %f %f\n', A);

quit;
</pre>

<pre>
% FILENAME:  myfunction.m

function result = myfunction ()

    % Return name of compute node which ran this job.
    [c name] = system('hostname');
    result = sprintf('hostname:%s', name);

    % Return three random numbers.
    A = rand(1,3);
    r = sprintf('%f %f %f', A);
    result=strvcat(result,r);

end
</pre>

Also, prepare a job submission file, here named <kbd>myjob.sub</kbd>.  Run with the name of the script:

<pre>
#!/bin/bash
# FILENAME:  myjob.sub

echo "myjob.sub"

# Load module, and set up environment for Matlab to run
module load matlab
{::if resource.batchsystem == pbs}
cd $PBS_O_WORKDIR
{::/}
unset DISPLAY

# -nodisplay:        run MATLAB in text mode; X11 server not needed
# -singleCompThread: turn off implicit parallelism
# -r:                read MATLAB program; use MATLAB JIT Accelerator
# Run Matlab, with the above options and specifying our .m file
matlab -nodisplay -singleCompThread -r myscript
</pre>

[Submit the job](/knowledge/${resource.hostname}/run/${resource.batchsystem}/submit)

[View job status](/knowledge/${resource.hostname}/run/${resource.batchsystem}/status)

[View results of the job](/knowledge/${resource.hostname}/run/${resource.batchsystem}/output)

<pre>
myjob.sub

                            &lt; M A T L A B (R) &gt;
                  Copyright 1984-2011 The MathWorks, Inc.
                    R2011b (7.13.0.564) 64-bit (glnxa64)
                              August 13, 2011

To get started, type one of these: helpwin, helpdesk, or demo.
For product information, visit www.mathworks.com.


hostname:${resource.hostname}-a001.rcac.purdue.edu
0.814724 0.905792 0.126987
</pre>

Output shows that a processor core on one compute node (${resource.hostname}-a001) processed the job.  Output also displays the three random numbers.


For more information about MATLAB:
<ul>
 <li><a href="http://www.mathworks.com/help/techdoc/ref/inv.html" target="_blank" rel="noopener">inv()</a></li>
 <li><a href="http://www.mathworks.com/help/distcomp/introduction-to-parallel-solutions.html#brjw1fx-2" target="_blank" rel="noopener">Run a Batch Job</a></li>
 <li><a href="http://www.mathworks.com/help/doc-archives.html" target="_blank" rel="noopener">Archived MathWorks Documentation</a></li>
 <li><a href="http://www.mathworks.com/" target="_blank" rel="noopener">MathWorks Website</a></li>
</ul>
