---
title: Overview of ${resource.name}
tags:
 - weber
---
# Overview of ${resource.name}

${resource.name} is Purdue's new specialty high performance computing cluster for data, applications, and research which are covered by export control regulations such as EAR, ITAR, or requiring compliance with the NIST SP 800-171. ${resource.name} was built through a partnership with HP and AMD in August 2019. ${resource.name} consists of HP compute nodes with two 10-core Intel Xeon-E5 "Haswell" processors (20 cores per node) and 64 GB of memory. All nodes have 56 Gbps EDR Infiniband interconnect.

To purchase access to ${resource.name} today, please contact the Export Controls office at exportcontrols@purdue.edu, or contact us via email at <rcac-cluster-purchase@lists.purdue.edu> if you have any questions.

{::if resource.namesake != null}
# ${resource.name} Namesake

${resource.name} is named in honor of ${resource.namesake}${resource.namesakeimpact}. More information about {::/}{::if resource.namesakesex == m}his{::elseif resource.namesakesex == f}her{::else}{::/}{::if resource.namesake != null} life and impact on Purdue is available in an [ITaP Biography of ${resource.name}](/compute/${resource.dir}/bio/).
{::/}

# ${resource.name} Specifications

All ${resource.name} nodes have 20 processor cores, 64 GB of RAM, and 56 Gbps Infiniband interconnects.

${resource.name} Front-Ends

| Front-Ends | Number of Nodes | Processors per Node          | Cores per Node | Memory per Node | Retires in |
| ---------- | --------------- | ---------------------------- | -------------- | --------------- | ---------- |
| Interim    | 2               | Two Sky Lake CPUs @ 2.10GHz  | 16             | 192 GB          | 2020       |
| Coming     | 4               | AMD Rome CPUs                | 64             | 256 GB          | 2023       |

${resource.name} Sub-Clusters

| Sub-Cluster | Number of Nodes | Processors per Node         | Cores per Node | Memory per Node | Retires in |
| ----------- | --------------- | --------------------------- | -------------- | --------------- | ---------- |
| A           | 4               | Two Haswell CPUs @ 2.60GHz  | 20             | 64 GB           | 2023       | 

${resource.name} nodes run CentOS 7 and use SLURM as the batch system for resource and job management.  The application of operating system patches occurs as security needs dictate.  All nodes allow for unlimited stack usage, as well as unlimited core dump size (though disk space and server quotas may still be a limiting factor).

On ${resource.name}, ITaP recommends the following set of compiler, math library, and message-passing library for parallel code:

{::if resource.name != Weber}
* ${resource.compiler}
{::else}
* Intel
{::/}
* ${resource.mathlib}
* ${resource.mesglib}

This compiler and these libraries are loaded by default. To load the recommended set again:

    $ module load rcac

To verify what you loaded:

    $ module list
