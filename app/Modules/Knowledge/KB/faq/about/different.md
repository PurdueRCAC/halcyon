---
title: How is ${resource.name} different than other Community Clusters?
tags:
 - rice
 - snyder
 - hammer
 - brown
 - gilbreth
 - scholar
---

### How is ${resource.name} different from the previous Community Clusters?

{::if resource.name == Rice }
* ${resource.name} is optimised for high-performance multi-node parallel computations. The scheduler is configured to favor starting jobs quickly and ensure maximum job independence with emphasis on larger multi-node jobs.
{::elseif resource.name == Snyder }
* ${resource.name} is optimised for large-memory, single-node life science computation. The scheduler is configured to favor starting jobs quickly and ensure maximum utilization.
{::elseif resource.name == Hammer }
* Hammer is optimised for loosely-coupled, high-throughput computation. The scheduler is configured to favor starting jobs quickly and ensure maximum utilization.
* The maximum job size is 8 processor cores. If you require resources with a greater degree of parallelism, please consider an alternate commnity cluster system optimized for high-performance, parallel computing.
{::elseif resource.name == Brown }
* Each Brown node contains the latest generation of Intel Xeon processor, codenamed Skylake.
{::elseif resource.name == Gilbreth }
${resource.name} differs from the previous Community Clusters in many significant aspects:
* Each ${resource.name} node contains ${resource.nodegpus} ${resource.gpuname} accelerator cards which can significantly improve performance of compute-intensive workloads.
* Each ${resource.name} front-end contains one ${resource.gpuname} accelerator card. This makes GPU code development and testing much simpler.
* GPU-enabled applications have both non-gpu and gpu-enabled versions installed. Typically, gpu-enabled versions are tagged with <kbd>gpu</kbd> in their module name, e.g., <kbd>lammps/31Mar17_gpu</kbd> is the GPU-enabled version of LAMMPS, while <kbd>lammps/31Mar17</kbd> is the non-gpu version of LAMMPS.
* An exception to the above rule is that for licensed softwares like Abaqus, Ansys, and Matlab, a single module contains both non-gpu and gpu-enabled versions.
{::elseif resource.name == Scholar }
${resource.name} differs from the previous Community Clusters in many significant aspects:
* ${resource.name} is a hybrid cluster for teaching courses that require high-performance computing.
* A subset of ${resource.name} front-ends contain ${resource.gpuname} accelerator cards. You can access these front ends by logging in to <kbd>gpu.scholar.rcac.purdue.edu</kbd>.
* A subset of ${resource.name} compute nodes contain ${resource.gpuname} accelerator cards which can significantly improve performance of compute-intensive workloads. These can be utilized by submitting jobs to the <kbd>gpu</kbd> queue (add <kbd>-q gpu</kbd> to your job submission command).
{::/}

{::if resource.naccesspolicy == singlejob }
* Jobs are scheduled on a whole-node basis and will not share nodes with other jobs by default. You may submit jobs that use less than one node, however, you will be allocated a whole node from your queue unless node sharing is enabled. Node sharing is enabled by adding <kbd>&#8209;l&nbsp;naccesspolicy=singleuser</kbd> to your job's requirements.
{::/}
* Fortress directories are not directly available on ${resource.name} front-ends. Fortress should be accessed with the recommended method of using [HSI or HTAR](/knowledge/fortress/storage/transfer)
