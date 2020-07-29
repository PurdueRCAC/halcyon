---
title: Running Jobs
order:
 - slurm
 - pbs
 - rosetta
 - examples
---

# Running Jobs

{::if resource.batchsystem == pbs}
There is one method for submitting jobs to ${resource.name}. You may use PBS to submit jobs to a queue on ${resource.name}. PBS performs job scheduling. Jobs may be any type of program. You may use either the batch or interactive mode to run your jobs. Use the batch mode for finished programs; use the interactive mode only for debugging. 

In this section, you'll find a few pages describing the basics of creating and submitting PBS jobs. As well, a number of example PBS jobs that you may be able to adapt to your own needs.
{::/}
{::if resource.batchsystem == slurm}
There is one method for submitting jobs to ${resource.name}. You may use SLURM to submit jobs to a partition on ${resource.name}. SLURM performs job scheduling. Jobs may be any type of program. You may use either the batch or interactive mode to run your jobs. Use the batch mode for finished programs; use the interactive mode only for debugging. 

In this section, you'll find a few pages describing the basics of creating and submitting SLURM jobs. As well, a number of example SLURM jobs that you may be able to adapt to your own needs.
{::/}
