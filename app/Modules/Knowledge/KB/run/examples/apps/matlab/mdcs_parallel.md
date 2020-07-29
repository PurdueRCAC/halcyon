---
title: Distributed Computing Server (parallel job)
tags:
 - slurm
 - wholenode
 - sharednode
---
# Distributed Computing Server (parallel job)

The MATLAB <em>Parallel Computing Toolbox (PCT)</em> enables a <em>parallel job</em> via the MATLAB <em>Distributed Computing Server (DCS)</em>.  The tasks of a parallel job are identical, run simultaneously on several MATLAB workers (labs), and communicate with each other.  This section illustrates an MPI-like program.

This section illustrates how to submit a small, MATLAB <em>parallel job</em> with four workers running one MPI-like task to a batch queue. The MATLAB program broadcasts an integer to four workers and gathers the names of the compute nodes running the workers and the lab IDs of the workers.

This example uses the job submission command to submit a Matlab script with a [user-defined cluster profile](../profile_manager) which scatters the MATLAB workers onto different compute nodes.  This method uses the MATLAB interpreter, the Parallel Computing Toolbox, and the Distributed Computing Server; so, it requires and checks out six licenses: one MATLAB license for the client running on the compute node, one PCT license, and four DCS licenses.  Four DCS licenses run the four copies of the parallel job.  This job is completely off the front end.

Prepare a MATLAB script named <kbd>myscript.m</kbd> :
<pre>
% FILENAME:  myscript.m

% Specify pool size.
% Convert the parallel job to a pool job.
parpool('4');
spmd


if labindex == 1
    % Lab (rank) #1 broadcasts an integer value to other labs (ranks).
    N = labBroadcast(1,int64(1000));
else
    % Each lab (rank) receives the broadcast value from lab (rank) #1.
    N = labBroadcast(1);
end

% Form a string with host name, total number of labs, lab ID, and broadcast value.
[c name] =system('hostname');
name = name(1:length(name)-1);
fmt = num2str(floor(log10(numlabs))+1);
str = sprintf(['%s:%d:%' fmt 'd:%d   '], name,numlabs,labindex,N);

% Apply global concatenate to all str's.
% Store the concatenation of str's in the first dimension (row) and on lab #1.
result = gcat(str,1,1);
if labindex == 1
    disp(result)
end


end   % spmd
matlabpool close force;
quit;
</pre>

Also, prepare a job submission, here named <kbd>myjob.sub</kbd>.  Run with the name of the script:

<pre>
# FILENAME:  myjob.sub

echo "myjob.sub"

module load matlab
{::if resource.batchsystem == pbs}
cd $PBS_O_WORKDIR
{::/}
unset DISPLAY

# -nodisplay: run MATLAB in text mode; X11 server not needed
# -r:         read MATLAB program; use MATLAB JIT Accelerator
matlab -nodisplay -r myscript
</pre>

Run MATLAB to set the default parallel configuration to your appropriate Profile:

<pre>
$ matlab -nodisplay
>> defaultParallelConfig('my${resource.batchsystem}profile');
>> quit;
$
</pre>

[Submit the job](/knowledge/${resource.hostname}/run/${resource.batchsystem}/submit) as a single compute node with one processor core.

Once this job starts, a second job submission is made.

[View job status](/knowledge/${resource.hostname}/run/${resource.batchsystem}/status)

[View results of the job](/knowledge/${resource.hostname}/run/${resource.batchsystem}/output)

<pre>
myjob.sub

                            < M A T L A B (R) >
                  Copyright 1984-2011 The MathWorks, Inc.
                    R2011b (7.13.0.564) 64-bit (glnxa64)
                              August 13, 2011


To get started, type one of these: helpwin, helpdesk, or demo.
For product information, visit www.mathworks.com.

>Starting matlabpool using the 'my${resource.batchsystem}profile' configuration ... connected to 4 labs.
Lab 1:
  ${resource.hostname}-a006.rcac.purdue.edu:4:1:1000
  ${resource.hostname}-a007.rcac.purdue.edu:4:2:1000
  ${resource.hostname}-a008.rcac.purdue.edu:4:3:1000
  ${resource.hostname}-a009.rcac.purdue.edu:4:4:1000
Sending a stop signal to all the labs ... stopped.
Did not find any pre-existing parallel jobs created by matlabpool.
</pre>

Output shows the name of one compute node (a006) that processed the job submission file <kbd>myjob.sub</kbd>.  The job submission scattered four processor cores (four MATLAB labs) among four different compute nodes (a006,a007,a008,a009) that processed the four parallel regions.

To scale up this method to handle a real application, increase the wall time in the submission command to accommodate a longer running job.  Secondly, increase the wall time of <kbd>my${resource.batchsystem}profile</kbd> by using the <kbd>Cluster Profile Manager</kbd> in the <kbd>Parallel</kbd> menu to enter a new wall time in the property <kbd>SubmitArguments</kbd>.

For more information about parallel jobs:
<ul>
 <li><a href="http://www.mathworks.com/help/distcomp/index.html" target="_blank" rel="noopener">MathWorks MATLAB Parallel Computing Toolbox User's Guide</a></li>
 <li><a href="http://www.mathworks.com/help/mdce/index.html" target="_blank" rel="noopener">MathWorks MATLAB Distributed Computing Server User's Guide</a></li>
 <li><a href="http://www.mathworks.com/" target="_blank" rel="noopener">MathWorks Website</a></li>
</ul>
