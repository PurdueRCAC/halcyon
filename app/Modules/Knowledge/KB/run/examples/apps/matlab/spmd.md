---
title: Parallel Toolbox (spmd)
tags:
 - slurm
 - wholenode
 - sharednode
---
# Parallel Toolbox (spmd)

The MATLAB <em>Parallel Computing Toolbox (PCT)</em> extends the MATLAB language with high-level, parallel-processing features such as parallel for loops, parallel regions, message passing, distributed arrays, and parallel numerical methods. It offers a shared-memory computing environment with a maximum of eight MATLAB workers (labs, threads; versions R2009a) and 12 workers (labs, threads; version R2011a) running on the local configuration in addition to your MATLAB client.  Moreover, the MATLAB <em>Distributed Computing Server (DCS)</em> scales PCT applications up to the limit of your DCS licenses.

This section illustrates how to submit a small, parallel, MATLAB program with a parallel region (<kbd>spmd</kbd> statement) as a MATLAB pool job to a batch queue.

This example uses the [submission command](/knowledge/${resource.hostname}/run/${resource.batchsystem}/submit) to submit to compute nodes a MATLAB client which interprets a Matlab .m with a [user-defined cluster profile](../profile_manager) which scatters the MATLAB workers onto different compute nodes.  This method uses the MATLAB interpreter, the Parallel Computing Toolbox, and the Distributed Computing Server; so, it requires and checks out six licenses: one MATLAB license for the client running on the compute node, one PCT license, and four DCS licenses.  Four DCS licenses run the four copies of the <kbd>spmd</kbd> statement.  This job is completely off the front end.

Prepare a MATLAB script called <kbd>myscript.m</kbd>:
<pre>
% FILENAME:  myscript.m

% SERIAL REGION
[c name] = system('hostname');
fprintf('SERIAL REGION:  hostname:%s\n', name)
p = parpool('4');
fprintf('                    hostname                         numlabs  labindex\n')
fprintf('                    -------------------------------  -------  --------\n')
tic;

% PARALLEL REGION
spmd
    [c name] = system('hostname');
    name = name(1:length(name)-1);
    fprintf('PARALLEL REGION:  %-31s  %7d  %8d\n', name,numlabs,labindex)
    pause(2);
end

% SERIAL REGION
elapsed_time = toc;          % get elapsed time in parallel region
delete(p);
fprintf('\n')
[c name] = system('hostname');
name = name(1:length(name)-1);
fprintf('SERIAL REGION:  hostname:%s\n', name)
fprintf('Elapsed time in parallel region:   %f\n', elapsed_time)
quit;
</pre>

Prepare a job submission file with an appropriate filename, here named <kbd>myjob.sub</kbd>.  Run with the name of the script:

<pre>
#!/bin/bash 
# FILENAME:  myjob.sub

echo "myjob.sub"

module load matlab
{::if resource.batchsystem == pbs}
cd $PBS_O_WORKDIR
{::/}
unset DISPLAY

matlab -nodisplay -r myscript
</pre>

Run MATLAB to set the default parallel configuration to your job configuration:

<pre>
$ matlab -nodisplay
>> parallel.defaultClusterProfile('my${resource.batchsystem}profile');
>> quit;
$
</pre>

[Submit the job](/knowledge/${resource.hostname}/run/${resource.batchsystem}/submit)


Once this job starts, a second job submission is made.

[View job status](/knowledge/${resource.hostname}/run/${resource.batchsystem}/status)

[View results for the job](/knowledge/${resource.hostname}/run/${resource.batchsystem}/output)
<pre>
myjob.sub

                            &lt; M A T L A B (R) &gt;
                  Copyright 1984-2011 The MathWorks, Inc.
                    R2011b (7.13.0.564) 64-bit (glnxa64)
                              August 13, 2011


To get started, type one of these: helpwin, helpdesk, or demo.
For product information, visit www.mathworks.com.

SERIAL REGION:  hostname:${resource.hostname}-a001.rcac.purdue.edu

Starting matlabpool using the 'my${resource.batchsystem}profile' profile ... connected to 4 labs.
                    hostname                         numlabs  labindex
                    -------------------------------  -------  --------
Lab 2:
  PARALLEL REGION:  ${resource.hostname}-a002.rcac.purdue.edu            4         2
Lab 1:
  PARALLEL REGION:  ${resource.hostname}-a001.rcac.purdue.edu            4         1
Lab 3:
  PARALLEL REGION:  ${resource.hostname}-a003.rcac.purdue.edu            4         3
Lab 4:
  PARALLEL REGION:  ${resource.hostname}-a004.rcac.purdue.edu            4         4

Sending a stop signal to all the labs ... stopped.


SERIAL REGION:  hostname:${resource.hostname}-a001.rcac.purdue.edu
Elapsed time in parallel region:   3.382151
</pre>

Output shows the name of one compute node (a001) that processed the job submission file <kbd>myjob.sub</kbd> and the two serial regions.  The job submission scattered four processor cores (four MATLAB labs) among four different compute nodes (a001,a002,a003,a004) that processed the four parallel regions.  The total elapsed time demonstrates that the jobs ran in parallel.


For more information about MATLAB Parallel Computing Toolbox:

<ul>
 <li><a href="http://www.mathworks.com/help/distcomp/index.html" target="_blank" rel="noopener">MathWorks MATLAB Parallel Computing Toolbox User's Guide</a></li>
 <li><a href="http://www.mathworks.com/help/mdce/index.html" target="_blank" rel="noopener">MathWorks MATLAB Distributed Computing Server User's Guide</a></li>
 <li><a href="http://www.mathworks.com/" target="_blank" rel="noopener">MathWorks Website</a></li>
</ul>
