---
title: Monitoring Resources
tags:
 - wholenode
 - sharednode
 - slurm
---
# Collecting System Resource Utilization Data

Knowing the precise resource utilization an application had during a job,
{::if resource.qsub_needs_gpu == 1}
such as GPU load or memory, 
{::else}
such as CPU load or memory, 
{::/}
can be incredibly useful. This is especially the
case when the application isn't performing as expected. 

One approach is to run a program like `htop` during an interactive job and keep
an eye on system resources. You can get precise time-series data from nodes
associated with your job using [XDmod](xdmod.rcac.purdue.edu) as well, online.
But these methods don't gather telemetry in an automated fashion, nor do they
give you control over the resolution or format of the data.

As a matter of course, a robust implementation of some HPC workload would
include resource utilization data as a diagnostic tool in the event of some
failure.

The `monitor` utility is a simple command line system resource monitoring tool
for gathering such telemetry and is available as a module.

```
$ module load utilities monitor
```

Complete documentation is available online  at
[resource-monitor.readthedocs.io](https://resource-monitor.readthedocs.io). 
A full manual page is also available for reference, `man monitor`.

In the context of a SLURM job you will need to put this monitoring task in the
background to allow the rest of your job script to proceed. Be sure to 
interrupt these tasks at the end of your job.

{::if resource.qsub_needs_gpu == 1}
```bash
#!/bin/bash
# FILENAME: monitored_job.sh

module load utilities monitor

# track GPU load
monitor gpu percent >gpu-percent.log &
GPU_PID=$!

# track CPU load
monitor cpu percent >cpu-percent.log &
CPU_PID=$!

# your code here

# shut down the resource monitors
kill -s INT $GPU_PID $CPU_PID
```
{::else}
```bash
#!/bin/bash
# FILENAME: monitored_job.sh

module load utilities monitor

# track per-code CPU load
monitor cpu percent --all-cores >cpu-percent.log &
CPU_PID=$!

# track memory usage
monitor cpu memory >cpu-memory.log &
MEM_PID=$!

# your code here

# shut down the resource monitors
kill -s INT $CPU_PID $MEM_PID
```
{::/}

A particularly elegant solution would be to include such tools in your
_prologue_ script and have the tear down in your _epilogue_ script. 

For large distributed jobs spread across multiple nodes, `mpiexec` can be
used to gather telemetry from all nodes in the job. The hostname is included
in each line of output so that data can be grouped as such. A concise way of
constructing the needed list of hostnames in SLURM is to simply use 
`srun hostname | sort -u`.

{::if resource.qsub_needs_gpu == 1}
```bash
#!/bin/bash
# FILENAME: monitored_job.sh

module load utilities monitor

# track all GPUs (one monitor per host)
mpiexec -machinefile <(srun hostname | sort -u) \
	monitor gpu percent >gpu-percent.log &
GPU_PID=$!

# track all CPUs (one monitor per host)
mpiexec -machinefile <(srun hostname | sort -u) \
	monitor cpu percent --all-cores >cpu-percent.log &
CPU_PID=$!

# your code here

# shut down the resource monitors
kill -s INT $GPU_PID $CPU_PID
```
{::else}
```bash
#!/bin/bash
# FILENAME: monitored_job.sh

module load utilities monitor

# track all CPUs (one monitor per host)
mpiexec -machinefile <(srun hostname | sort -u) \
	monitor cpu percent --all-cores >cpu-percent.log &
CPU_PID=$!

# track memory on all hosts (one monitor per host)
mpiexec -machinefile <(srun hostname | sort -u) \
	monitor cpu memory >cpu-memory.log &
MEM_PID=$!

# your code here

# shut down the resource monitors
kill -s INT $CPU_PID $MEM_PID
```
{::/}

To get resource data in a more readily computable format, the `monitor` program
can be told to output in CSV format with the `--csv` flag.

{::if resource.qsub_needs_gpu == 1}
```bash
monitor gpu memory --csv >gpu-memory.csv
```
{::else}
```bash
monitor cpu memory --csv >cpu-memory.csv
```
{::/}

For a distributed job you will need to suppress the header lines otherwise one
will be created by each host.

{::if resource.qsub_needs_gpu == 1}
```bash
monitor gpu memory --csv | head -1 >gpu-memory.csv
mpiexec -machinefile <(srun hostname | sort -u) \
	monitor gpu memory --csv --no-header >>gpu-memory.csv
```
{::else}
```bash
monitor cpu memory --csv | head -1 >cpu-memory.csv
mpiexec -machinefile <(srun hostname | sort -u) \
	monitor cpu memory --csv --no-header >>cpu-memory.csv

```
{::/}
