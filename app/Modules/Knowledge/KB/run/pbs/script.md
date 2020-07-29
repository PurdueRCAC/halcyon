---
title: Job Submission Script
tags:
 - wholenode
 - sharednode
---

# Job Submission Script

To submit work to a PBS queue, you must first create a <em>job submission file</em>.  This job submission file is essentially a simple shell script.  It will set any required environment variables, load any necessary modules, create or modify files and directories, and run any applications that you need:

<pre>
#!/bin/sh -l
# FILENAME:  myjobsubmissionfile

# Loads Matlab and sets the application up
module load matlab

# Change to the directory from which you originally submitted this job.
cd $PBS_O_WORKDIR

# Runs a Matlab script named 'myscript'
matlab -nodisplay -singleCompThread -r myscript
</pre> 

Once your script is prepared, you are ready to [submit your job](../submit).

### Job Script Environment Variables


<div class="inrows-wide">
<table class="inrows-wide">
<caption>PBS sets several potentially useful environment variables which you may use within your job submission files.  Here is a list of some:</caption>
 <tr>
 <th scope="col">Name</th>
 <th scope="col">Description</th>
 </tr>
 <tr>
 <td>PBS_O_WORKDIR</td>
 <td>Absolute path of the current working directory when you submitted this job</td>
 </tr>
 <tr>
 <td>PBS_JOBID</td>
 <td>Job ID number assigned to this job by the batch system</td>
 </tr>
 <tr>
 <td>PBS_JOBNAME</td>
 <td>Job name supplied by the user</td>
 </tr>
 <tr>
 <td>PBS_NODEFILE</td>
 <td>File containing the list of nodes assigned to this job</td>
 </tr>
 <tr>
 <td>PBS_O_HOST</td>
 <td>Hostname of the system where you submitted this job</td>
 </tr>
 <tr>
 <td>PBS_O_QUEUE</td>
 <td>Name of the original queue to which you submitted this job</td>
 </tr>
 <tr>
 <td>PBS_O_SYSTEM</td>
 <td>Operating system name given by <kbd>uname -s</kbd> where you submitted this job</td>
 </tr>
 <tr>
 <td>PBS_ENVIRONMENT</td>
 <td>"PBS_BATCH" if this job is a batch job, or "PBS_INTERACTIVE" if this job is an interactive job</td>
 </tr>
</table>
</div>
