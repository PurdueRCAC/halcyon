---
title: Parallel Computing Toolbox (parfor)
tags:
 - slurm
 - wholenode
 - sharednode
---
# Parallel Computing Toolbox (parfor)

The MATLAB <em>Parallel Computing Toolbox (PCT)</em> extends the MATLAB language with high-level, parallel-processing features such as parallel for loops, parallel regions, message passing, distributed arrays, and parallel numerical methods.  It offers a shared-memory computing environment running on the local cluster profile in addition to your MATLAB client.  Moreover, the MATLAB <em>Distributed Computing Server (DCS)</em> scales PCT applications up to the limit of your DCS licenses.

This section illustrates the fine-grained parallelism of a parallel for loop (<kbd>parfor</kbd>) in a <em>pool job</em>.

The following examples illustrate a method for submitting a small, parallel, MATLAB program with a parallel loop (<kbd>parfor</kbd> statement) as a job to a queue.  This MATLAB program prints the name of the run host and shows the values of variables <kbd>numlabs</kbd> and <kbd>labindex</kbd> for each iteration of the <kbd>parfor</kbd> loop.

This method uses the job submission command to submit a MATLAB client which calls the MATLAB <kbd>batch()</kbd> function with a [user-defined cluster profile](../profile_manager).

Prepare a MATLAB pool program in a MATLAB script with an appropriate filename, here named <kbd>myscript.m</kbd>:
<pre>% FILENAME:  myscript.m

% SERIAL REGION
[c name] = system('hostname');
fprintf('SERIAL REGION:  hostname:%s\n', name)
numlabs = parpool('poolsize');
fprintf('        hostname                         numlabs  labindex  iteration\n')
fprintf('        -------------------------------  -------  --------  ---------\n')
tic;

% PARALLEL LOOP
parfor i = 1:8
    [c name] = system('hostname');
    name = name(1:length(name)-1);
    fprintf('PARALLEL LOOP:  %-31s  %7d  %8d  %9d\n', name,numlabs,labindex,i)
    pause(2);
end

% SERIAL REGION
elapsed_time = toc;        % get elapsed time in parallel loop
fprintf('\n')
[c name] = system('hostname');
name = name(1:length(name)-1);
fprintf('SERIAL REGION:  hostname:%s\n', name)
fprintf('Elapsed time in parallel loop:   %f\n', elapsed_time)
</pre>

The execution of a pool job starts with a worker executing the statements of the first serial region up to the <kbd>parfor</kbd> block, when it pauses.  A set of workers (the pool) executes the <kbd>parfor</kbd> block.  When they finish, the first worker resumes by executing the second serial region.  The code displays the names of the compute nodes running the batch session and the worker pool.

Prepare a MATLAB script that calls MATLAB function <kbd>batch()</kbd> which makes a four-lab pool on which to run the MATLAB code in the file <kbd>myscript.m</kbd>.  Use an appropriate filename, here named <kbd>mylclbatch.m</kbd>:

<pre>
% FILENAME:  mylclbatch.m

!echo "mylclbatch.m"
!hostname

pjob=batch('myscript','Profile','my${resource.batchsystem}profile','Pool',4,'CaptureDiary',true);
wait(pjob);
diary(pjob);
quit;
</pre>

Prepare a job submission file with an appropriate filename, here named <kbd>myjob.sub</kbd>:

<pre>
#!/bin/bash
# FILENAME:  myjob.sub

echo "myjob.sub"
hostname

module load matlab
{::if resource.batchsystem == pbs}
cd $PBS_O_WORKDIR
{::/}
unset DISPLAY

matlab -nodisplay -r mylclbatch
</pre>

[Submit the job](/knowledge/${resource.hostname}/run/${resource.batchsystem}/submit) as a single compute node with one processor core.

One processor core runs <kbd>myjob.sub</kbd> and <kbd>mylclbatch.m</kbd>.

Once this job starts, a second job submission is made.

[View job status](/knowledge/${resource.hostname}/run/${resource.batchsystem}/status)

[View results of the job](/knowledge/${resource.hostname}/run/${resource.batchsystem}/output)

<pre>
myjob.sub

                            &lt; M A T L A B (R) &gt;
                  Copyright 1984-2013 The MathWorks, Inc.
                    R2013a (8.1.0.604) 64-bit (glnxa64)
                             February 15, 2013


To get started, type one of these: helpwin, helpdesk, or demo.
For product information, visit www.mathworks.com.


mylclbatch.m
${resource.hostname}-a000.rcac.purdue.edu
SERIAL REGION:  hostname:${resource.hostname}-a000.rcac.purdue.edu

                hostname                         numlabs  labindex  iteration
                -------------------------------  -------  --------  ---------
PARALLEL LOOP:  ${resource.hostname}-a001.rcac.purdue.edu            4         1          2
PARALLEL LOOP:  ${resource.hostname}-a002.rcac.purdue.edu            4         1          4
PARALLEL LOOP:  ${resource.hostname}-a001.rcac.purdue.edu            4         1          5
PARALLEL LOOP:  ${resource.hostname}-a002.rcac.purdue.edu            4         1          6
PARALLEL LOOP:  ${resource.hostname}-a003.rcac.purdue.edu            4         1          1
PARALLEL LOOP:  ${resource.hostname}-a003.rcac.purdue.edu            4         1          3
PARALLEL LOOP:  ${resource.hostname}-a004.rcac.purdue.edu            4         1          7
PARALLEL LOOP:  ${resource.hostname}-a004.rcac.purdue.edu            4         1          8

SERIAL REGION:  hostname:${resource.hostname}-a000.rcac.purdue.edu

Elapsed time in parallel loop:   5.411486
</pre>


To scale up this method to handle a real application, increase the wall time in the [submission](/knowledge/${resource.hostname}/run/${resource.batchsystem}/submit) command to accommodate a longer running job.  Secondly, increase the wall time of <kbd>my${resource.batchsystem}profile</kbd> by using the [Cluster Profile Manager](../profile_manager) in the <kbd>Parallel</kbd> menu to enter a new wall time in the property <kbd>SubmitArguments</kbd>.

For more information about MATLAB Parallel Computing Toolbox:
<ul>
 <li><a href="http://www.mathworks.com/help/distcomp/index.html" target="_blank" rel="noopener">MathWorks MATLAB Parallel Computing Toolbox User's Guide</a></li>
 <li><a href="http://www.mathworks.com/help/mdce/index.html" target="_blank" rel="noopener">MathWorks MATLAB Distributed Computing Server User's Guide</a></li>
 <li><a href="http://www.mathworks.com/" target="_blank" rel="noopener">MathWorks Website</a></li>
</ul>
