---
title: Overview of ${resource.name}
tags:
 - hammer
---
# Overview of ${resource.name}

Hammer is optimized for Purdue's communities utilizing loosely-coupled, high-throughput computing. Hammer was initially built through a partnership with HP and Intel in April 2015. Hammer was expanded again in late 2016. Hammer will be expanded annually, with each year's purchase of nodes to remain in production for 5 years from their initial purchase.

To purchase access to Hammer today, go to the [Cluster Access Purchase](/purchase/) page. Please subscribe to our Community Cluster Program Mailing List to stay informed on the latest purchasing developments or contact us via email at <rcac-cluster-purchase@lists.purdue.edu> if you have any questions.

{::if resource.namesake != null}
# ${resource.name} Namesake

${resource.name} is named in honor of ${resource.namesake}${resource.namesakeimpact}. More information about {::/}{::if resource.namesakesex == m}his{::elseif resource.namesakesex == f}her{::else}{::/}{::if resource.namesake != null} life and impact on Purdue is available in an [ITaP Biography of ${resource.name}](/compute/${resource.dir}/bio/).
{::/}

# ${resource.name} Specifications

Most Hammer nodes consist of identical hardware. All Hammer nodes have variable numbers of processor cores, and 10 Gbps or 25 Gbps Ethernet interconnects.

${resource.name} Front-Ends

| Front-Ends  | Number of Nodes | Processors per Node               | Cores per Node | Memory per Node | Retires in |
| ----------- | --------------- | --------------------------------- | -------------- | --------------- | ---------- |
|             | 2               | Two Haswell CPUs @ 2.60GHz        | 20             | 64 GB           | 2020       |

${resource.name} Sub-Clusters

| Sub-Cluster | Number of Nodes | Processors per Node                | Cores per Node | Memory per Node | Retires in |
| ----------- | --------------- | ---------------------------------- | -------------- | --------------- | ---------- |
| A           | 198             | Two Haswell CPUs @ 2.60GHz         | 20             | 64 GB           | 2020       |
| B           | 40              | Two Haswell CPUs @ 2.60GHz         | 40 (Logical)   | 128 GB          | 2021       |
| C           | 27              | Two Sky Lake CPUs @ 2.60GHz        | 48 (Logical)   | 192 GB          | 2022       |
| D           | 18              | Two Sky Lake CPUs @ 2.60GHz        | 48 (Logical)   | 192 GB          | 2023       |
| E           | 15              | Two Intel Xeon Gold CPUs @ 2.60GHz | 48 (Logical)   | 96 GB           | 2024       |

${resource.name} nodes run CentOS 7 and use Slurm (Simple Linux Utility for Resource Management) as the batch scheduler for resource and job management.  The application of operating system patches occurs as security needs dictate.  All nodes allow for unlimited stack usage, as well as unlimited core dump size (though disk space and server quotas may still be a limiting factor).

On ${resource.name}, ITaP recommends the following set of compiler and math libraries.

* ${resource.compiler}
* ${resource.mathlib}

This compiler and these libraries are loaded by default. To load the recommended set again:

    $ module load rcac

To verify what you loaded:

    $ module list
