---
title: Quick Guide
tags:
 - slurm
 - wholenode
 - sharednode
---

# Quick Guide

This table lists the most common command, environment variables, and job specification options used by the workload management systems and their equivalents (adapted from http://www.schedmd.com/slurmdocs/rosetta.html).

Common commands across workload management systems

<table>
    <thead>
        <tr>
            <th scope="col">User Commands</th>
            <th scope="col">PBS/Torque</th>
            <th scope="col">Slurm</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <th scope="row" style="width:200px">Job submission</th>
            <td><code>qsub [script_file]</code></td>
            <td><code>sbatch [script_file]</code></td>
        </tr>
        <tr>
            <th scope="row">Interactive Job</th>
            <td><code>qsub -I</code></td>
            <td><code>sinteractive</code></td>
        </tr>
        <tr>
            <th scope="row">Job deletion</th>
            <td><code>qdel [job_id]</code></td>
            <td><code>scancel [job_id]</code></td>
        </tr>
        <tr>
            <th scope="row">Job status (by job)</th>
            <td><code>qstat [job_id]</code></td>
            <td><code>squeue [-j job_id]</code></td>
        </tr>
        <tr>
            <th scope="row">Job status (by user)</th>
            <td><code>qstat -u [user_name]</code></td>
            <td><code>squeue [-u user_name]</code></td>
        </tr>
        <tr>
            <th scope="row">Job hold</th>
            <td><code>qhold [job_id]</code></td>
            <td><code>scontrol hold [job_id]</code></td>
        </tr>
        <tr>
            <th scope="row">Job release</th>
            <td><code>qrls [job_id]</code></td>
            <td><code>scontrol release [job_id]</code></td>
        </tr>
        <tr>
            <th scope="row">Queue info</th>
            <td><code>qstat -Q</code></td>
            <td><code>squeue</code></td>
        </tr>
        <tr>
            <th scope="row">Queue access</th>
            <td><code>qlist</code></td>
            <td><code>slist</code></td>
        </tr>
        <tr>
            <th scope="row">Node list</th>
            <td><code>pbsnodes -l</code></td>
            <td><code>sinfo -N</code><br /><code>scontrol show nodes</code></td>
        </tr>
        <tr>
            <th scope="row">Cluster status</th>
            <td><code>qstat -a</code></td>
            <td><code>sinfo</code></td>
        </tr>
        <tr>
            <th scope="row">GUI</th>
            <td><code>xpbsmon</code></td>
            <td><code>sview</code></td>
        </tr>
    </tbody>
    <tbody>
        <tr class="thead">
            <th scope="col">Environment</th>
            <th scope="col">PBS/Torque</th>
            <th scope="col">Slurm</th>
        </tr>
        <tr>
            <th scope="row">Job ID</th>
            <td><code>$PBS_JOBID</code></td>
            <td><code>$SLURM_JOB_ID</code></td>
        </tr>
        <tr>
            <th scope="row">Job Name</th>
            <td><code>$PBS_JOBNAME</code></td>
            <td><code>$SLURM_JOB_NAME</code></td>
        </tr>
        <tr>
            <th scope="row">Job Queue/Account</th>
            <td><code>$PBS_QUEUE</code></td>
            <td><code>$SLURM_JOB_ACCOUNT</code></td>
        </tr>
        <tr>
            <th scope="row">Submit Directory</th>
            <td><code>$PBS_O_WORKDIR</code></td>
            <td><code>$SLURM_SUBMIT_DIR</code></td>
        </tr>
        <tr>
            <th scope="row">Submit Host</th>
            <td><code>$PBS_O_HOST</code></td>
            <td><code>$SLURM_SUBMIT_HOST</code></td>
        </tr>
        <tr>
            <th scope="row">Number of nodes</th>
            <td><code>$PBS_NUM_NODES</code></td>
            <td><code>$SLURM_JOB_NUM_NODES</code></td>
        </tr>
        <tr>
            <th scope="row">Number of Tasks</th>
            <td><code>$PBS_NP</code></td>
            <td><code>$SLURM_NTASKS</code></td>
        </tr>
        <tr>
            <th scope="row">Number of Tasks Per Node</th>
            <td><code>$PBS_NUM_PPN</code></td>
            <td><code>$SLURM_NTASKS_PER_NODE</code></td>
        </tr>
        <tr>
            <th scope="row">Node List (Compact)</th>
            <td>n/a</td>
            <td><code>$SLURM_JOB_NODELIST</code></td>
        </tr>
        <tr>
            <th scope="row">Node List (One Core Per Line)</th>
            <td><code>LIST=$(cat $PBS_NODEFILE)</code></td>
            <td><code>LIST=$(srun hostname)</code></td>
        </tr>
        <tr>
            <th scope="row">Job Array Index</th>
            <td><code>$PBS_ARRAYID</code></td>
            <td><code>$SLURM_ARRAY_TASK_ID</code></td>
        </tr>
    </tbody>
    <tbody>
        <tr class="thead">
            <th scope="col">Job Specification</th>
            <th scope="col">PBS/Torque</th>
            <th scope="col">Slurm</th>
        </tr>
        <tr>
            <th scope="row">Script directive</th>
            <td><code>#PBS</code></td>
            <td><code>#SBATCH</code></td>
        </tr>
        <tr>
            <th scope="row">Queue</th>
            <td><code>-q [queue]</code></td>
            <td><code>-A [queue]</code></td>
        </tr>
        <tr>
            <th scope="row">Node Count</th>
            <td><code>-l nodes=[count]</code></td>
            <td><code>-N [min[-max]]</code></td>
        </tr>
        <tr>
            <th scope="row">CPU Count</th>
            <td><code>-l ppn=[count]</code></td>
            <td><code>-n [count]</code><br />Note: total, not per node</td>
        </tr>
        <tr>
            <th scope="row">Wall Clock Limit</th>
            <td><code>-l walltime=[hh:mm:ss]</code></td>
            <td><code>-t [min]</code> OR<br /><code>-t [hh:mm:ss]</code> OR<br /><code>-t [days-hh:mm:ss]</code></td>
        </tr>
        <tr>
            <th scope="row">Standard Output FIle</th>
            <td><code>-o [file_name]</code></td>
            <td><code>-o [file_name]</code></td>
        </tr>
        <tr>
            <th scope="row">Standard Error File</th>
            <td><code>-e [file_name]</code></td>
            <td><code>-e [file_name]</code></td>
        </tr>
        <tr>
            <th scope="row">Combine stdout/err</th>
            <td><code>-j oe</code> (both to stdout) OR<br /><code>-j eo</code> (both to stderr)</td>
            <td><code>(use -o without -e)</code></td>
        </tr>
        <tr>
            <th scope="row">Copy Environment</th>
            <td><code>-V</code></td>
            <td><code>--export=[ALL | NONE | variables]</code><br />Note: default behavior is <code>ALL</code></td>
        </tr>
        <tr>
            <th scope="row">Copy Specific Environment Variable</th>
            <td><code>-v myvar=somevalue</code></td>
            <td><code>--export=NONE,myvar=somevalue</code> OR<br /><code>--export=ALL,myvar=somevalue</code></td>
        </tr>
        <tr>
            <th scope="row">Event Notification</th>
            <td><code>-m abe</code></td>
            <td><code>--mail-type=[events]</code></td>
        </tr>
        <tr>
            <th scope="row">Email Address</th>
            <td><code>-M [address]</code></td>
            <td><code>--mail-user=[address]</code></td>
        </tr>
        <tr>
            <th scope="row">Job Name</th>
            <td><code>-N [name]</code></td>
            <td><code>--job-name=[name]</code></td>
        </tr>
        <tr>
            <th scope="row">Job Restart</th>
            <td><code>-r [y|n]</code></td>
            <td><code>--requeue</code> OR<br /><code>--no-requeue</code></td>
        </tr>
        <tr>
            <th scope="row">Working Directory</th>
            <td> </td>
            <td><code>--workdir=[dir_name]</code></td>
        </tr>
        <tr>
            <th scope="row">Resource Sharing</th>
            <td><code>-l naccesspolicy=singlejob</code></td>
            <td><code>--exclusive</code> OR<br /><code>--shared</code></td>
        </tr>
        <tr>
            <th scope="row">Memory Size</th>
            <td><code>-l mem=[MB]</code></td>
            <td><code>--mem=[mem][M|G|T]</code> OR<br /><code>--mem-per-cpu=[mem][M|G|T]</code></td>
        </tr>
        <tr>
            <th scope="row">Account to charge</th>
            <td><code>-A [account]</code></td>
            <td><code>-A [account]</code></td>
        </tr>
        <tr>
            <th scope="row">Tasks Per Node</th>
            <td><code>-l ppn=[count]</code></td>
            <td><code>--tasks-per-node=[count]</code></td>
        </tr>
        <tr>
            <th scope="row">CPUs Per Task</th>
            <td> </td>
            <td><code>--cpus-per-task=[count]</code></td>
        </tr>
        <tr>
            <th scope="row">Job Dependency</th>
            <td><code>-W depend=[state:job_id]</code></td>
            <td><code>--depend=[state:job_id]</code></td>
        </tr>
        <tr>
            <th scope="row">Job Arrays</th>
            <td><code>-t [array_spec]</code></td>
            <td><code>--array=[array_spec]</code></td>
        </tr>
        <tr>
            <th scope="row">Generic Resources</th>
            <td><code>-l other=[resource_spec]</code></td>
            <td><code>--gres=[resource_spec]</code></td>
        </tr>
        <tr>
            <th scope="row">Licenses</th>
            <td> </td>
            <td><code>--licenses=[license_spec]</code></td>
        </tr>
        <tr>
            <th scope="row">Begin Time</th>
            <td><code>-A &quot;y-m-d h:m:s&quot;</code></td>
            <td><code>--begin=y-m-d[Th:m[:s]]</code></td>
        </tr>
    </tbody>
</table>

See the official [Slurm Documentation](http://slurm.schedmd.com/documentation.html) for further details.

