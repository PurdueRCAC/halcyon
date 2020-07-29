---
title: Numpy Parallel Behavior
tags:
 - slurm
 - wholenode
 - sharednode
 - workbench
---

### Numpy Parallel Behavior

The widely available Numpy package is the best way to handle numerical computation in Python. The `numpy` package provided by our `anaconda` modules is optimized using Intel's MKL library. It will automatically parallelize many operations to make use of all the cores available on a machine.

In many contexts that would be the ideal behavior. On the cluster however that very likely is not in fact the preferred behavior because often more than one user is present on the system and/or more than one job on a node. Having multiple processes contend for those resources will actually result in lesser performance.

Setting the `MKL_NUM_THREADS` or `OMP_NUM_THREADS` environment variable(s) allows you to control this behavior. Our anaconda modules automatically set these variables to 1 _if and only if_ you do not currently have that variable defined.

When submitting batch jobs it is always a good idea to be explicit rather than implicit. If you are submitting a job that you want to make use of the full resources available on the node, set one or both of these variables to the number
of cores you want to allow numpy to make use of.
```sh
#!/bin/bash
{::if resource.batchsystem == pbs}
#PBS: -l nodes=1:ppn=${resource.nodecores}
{::/}

module load anaconda
export MKL_NUM_THREADS=${resource.nodecores}

...
```

If you are submitting multiple jobs that you intend to be scheduled together on the same node, it is probably best to restrict numpy to a single core.
```sh
#!/bin/bash
{::if resource.batchsystem == pbs}
#PBS -l nodes=1:ppn=1
#PBS -l naccesspolicy=singleuser
{::/}

module load anaconda
export MKL_NUM_THREADS=1
```
