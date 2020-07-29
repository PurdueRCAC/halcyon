---
title: Overview of ${resource.name}
tags:
 - rice
---
# Overview of ${resource.name}

Rice is a Purdue Community Cluster, optimized for Purdue's communities running traditional, tightly-coupled science and engineering applications. Rice was built through a partnership with HP and Intel in April 2015. Rice consists of HP compute nodes with two 10-core Intel Xeon-E5 processors (20 cores per node) and 64 GB of memory. All nodes have 56 Gb FDR Infiniband interconnect and a 5-year warranty.

{::if resource.namesake != null}
# ${resource.name} Namesake

${resource.name} is named in honor of ${resource.namesake}${resource.namesakeimpact}. More information about {::/}{::if resource.namesakesex == m}his{::elseif resource.namesakesex == f}her{::else}{::/}{::if resource.namesake != null} life and impact on Purdue is available in an [ITaP Biography of ${resource.name}](/compute/${resource.dir}/bio/).
{::/}

# ${resource.name} Specifications

All Rice nodes have 20 processor cores, 64 GB of RAM, and 56 Gbps Infiniband interconnects.

${resource.name} Front-Ends

| Front-Ends | Number of Nodes | Processors per Node           | Cores per Node | Memory per Node | Retires in |
| ---------- | --------------- | ----------------------------- | -------------- | --------------  | ---------- |
|            | 4               | Two Haswell CPUs @ 2.60GHz  | 20             | 64 GB           | 2020       |

${resource.name} Sub-Clusters

| Sub-Cluster | Number of Nodes | Processors per Node          | Cores per Node | Memory per Node | Retires in |
| ----------- | --------------- | ---------------------------- | -------------- | --------------  | ---------- |
| A           | 576             | Two Haswell CPUs @ 2.60GHz | 20             | 64 GB           | 2020       |

${resource.name} nodes run CentOS 7 and use Slurm (Simple Linux Utility for Resource Management) as the batch scheduler for resource and job management.  The application of operating system patches occurs as security needs dictate.  All nodes allow for unlimited stack usage, as well as unlimited core dump size (though disk space and server quotas may still be a limiting factor).

On ${resource.name}, ITaP recommends the following set of compiler, math library, and message-passing library for parallel code:

* ${resource.compiler}
* ${resource.mathlib}
* ${resource.mesglib}

This compiler and these libraries are loaded by default. To load the recommended set again:

    $ module load rcac

To verify what you loaded:

    $ module list
