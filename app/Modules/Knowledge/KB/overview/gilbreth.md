---
title: Overview of ${resource.name}
tags:
 - gilbreth
---
# Overview of ${resource.name}

${resource.name} is a Community Cluster optimized for communities running GPU intensive applications such as machine learning.  ${resource.name} consists of Dell compute nodes with Intel Xeon processors and Nvidia Tesla GPUs. 

To purchase access to ${resource.name} today, go to the [Cluster Access Purchase](https://www.rcac.purdue.edu/purchase/) page. Please subscribe to our Community Cluster Program Mailing List to stay informed on the latest purchasing developments or contact us via email at <rcac-cluster-purchase@lists.purdue.edu> if you have any questions.

{::if resource.namesake != null}
# ${resource.name} Namesake

${resource.name} is named in honor of ${resource.namesake}${resource.namesakeimpact}. More information about {::/}{::if resource.namesakesex == m}his{::elseif resource.namesakesex == f}her{::else}{::/}{::if resource.namesake != null} life and impact on Purdue is available in an [ITaP Biography of ${resource.namesake}](/compute/${resource.dir}/bio/).
{::/}

# ${resource.name} Detailed Hardware Specification

${resource.name} nodes have at least 192 GB of RAM, and 100 Gbps Infiniband interconnects.

${resource.name} Front-Ends

| Front-Ends | Number of Nodes | Cores per Node  | Memory per Node | GPUs per node | Retires in |
| ---------- | --------------- | --------------- | --------------- | ------------- | ---------- | 
| With GPU   | 2               |  20             | 96 GB           | 1 P100        | 2024       |

${resource.name} Sub-Clusters

| Sub-Cluster | Number of Nodes | Cores per Node | Memory per Node | GPUs per node | Retires in | 
| ----------- | --------------- | -------------- | --------------- | ------------- | ---------- |
| A           | 4               | 20             | 256 GB          | 2 P100        | 2022       |
| B           | 16              | 24             | 192 GB          | 2 P100        | 2023       |
| C           | 3               | 20             | 768 GB          | 4 V100        | 2024       |
| D           | 8               | 16             | 192 GB          | 2 P100        | 2024       |
| E           | 16              | 16             | 192 GB          | 2 V100        | 2024       |

${resource.name} nodes run CentOS 7 and use Slurm (Simple Linux Utility for Resource Management) as the batch scheduler for resource and job management.  The application of operating system patches occurs as security needs dictate.  All nodes allow for unlimited stack usage, as well as unlimited core dump size (though disk space and server quotas may still be a limiting factor).

On ${resource.name}, ITaP recommends the following set of compiler, math library, and message-passing library for parallel code:

* ${resource.compiler}
* ${resource.mathlib}
* ${resource.mesglib}

This compiler and these libraries are loaded by default. To load the recommended set again:

    $ module load rcac

To verify what you loaded:

    $ module list
