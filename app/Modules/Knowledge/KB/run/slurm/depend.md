---
title: Job Dependencies
tags:
 - slurm
---

# Job Dependencies

Dependencies are an automated way of holding and releasing jobs. Jobs with a dependency are held until the condition is satisfied. Once the condition is satisified jobs only then become eligible to run and must still queue as normal.

Job dependencies may be configured to ensure jobs start in a specified order. Jobs can be configured to run after other job state changes, such as when the job starts or the job ends.

These examples illustrate setting dependencies in several ways. Typically dependencies are set by capturing and using the job ID from the last job submitted.

To run a job after job <kbd>myjobid</kbd> has started:

<pre>$ sbatch --dependency=after:myjobid myjobsubmissionfile</pre>

To run a job after job <kbd>myjobid</kbd> ends without error:

<pre>$ sbatch --dependency=afterok:myjobid myjobsubmissionfile</pre>

To run a job after job <kbd>myjobid</kbd> ends with errors:
<pre>$ sbatch --dependency=afternotok:myjobid myjobsubmissionfile</pre>

To run a job after job <kbd>myjobid</kbd> ends with or without errors:

<pre>$ sbatch --dependency=afterany:myjobid myjobsubmissionfile</pre>

To set more complex dependencies on multiple jobs and conditions:

<pre>$ sbatch --dependency=after:myjobid1:myjobid2:myjobid3,afterok:myjobid4 myjobsubmissionfile</pre>
