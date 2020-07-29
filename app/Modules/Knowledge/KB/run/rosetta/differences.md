---
title: Notable Differences
tags:
 - slurm
 - wholenode
 - sharednode
---

# Notable Differences

* ** Separate commands for Batch and Interactive jobs **

    Unlike PBS, in Slurm interactive jobs and batch jobs are launched with completely distinct commands.<br />
    Use <pre>sbatch [allocation request options] script</pre> to submit a job to the batch scheduler, and <pre>sinteractive [allocation request options]</pre> to launch an interactive job. <kbd>sinteractive</kbd> accepts most of the same allocation request options as <kbd>sbatch</kbd> does.
    
* **No need for `cd $PBS_O_WORKDIR`**
  
  In Slurm your batch job starts to run in the directory from which you submitted the script whereas in PBS/Torque you need to explicitly move back to that directory with `cd $PBS_O_WORKDIR`.
* **No need to manually export environment**
  
  The environment variables that are defined in your shell session at the time that you submit the script are exported into your batch job, whereas in PBS/Torque you need to use the `-V` flag to export your environment.
* **Location of output files**

  The output and error files are created in their final location immediately that the job begins or an error is generated, whereas in PBS/Torque temporary files are created that are only moved to the final location at the end of the job. Therefore in Slurm you can examine the output and error files from your job during its execution.

See the official [Slurm Documentation](http://slurm.schedmd.com/documentation.html) for further details.
