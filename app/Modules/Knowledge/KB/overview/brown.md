---
title: Overview of ${resource.name}
tags:
 - brown
---
# Overview of ${resource.name}

${resource.name} is a Community Cluster optimized for communities running traditional, tightly-coupled science and engineering applications. ${resource.name} built through a partnership with Dell and Intel in October 2017. ${resource.name} consists of Dell compute nodes with two 12-core Intel Xeon Gold "Sky Lake" processors (24 cores per node) and 96 GB of memory. All nodes have 100 Gbps EDR Infiniband interconnect and a 5-year warranty.

To purchase access to ${resource.name} today, go to the [Cluster Access Purchase](https://www.rcac.purdue.edu/purchase/) page. Please subscribe to our Community Cluster Program Mailing List to stay informed on the latest purchasing developments or contact us via email at <rcac-cluster-purchase@lists.purdue.edu> if you have any questions.

{::if resource.namesake != null}
# ${resource.name} Namesake

${resource.name} is named in honor of ${resource.namesake}${resource.namesakeimpact}. More information about {::/}{::if resource.namesakesex == m}his{::elseif resource.namesakesex == f}her{::else}{::/}{::if resource.namesake != null} life and impact on Purdue is available in an [ITaP Biography of ${resource.name}](/compute/${resource.dir}/bio/).
{::/}

# ${resource.name} Specifications

All ${resource.name} nodes have 24 processor cores, 96 GB of RAM, and 100 Gbps Infiniband interconnects.

${resource.name} Front-Ends

| Front-Ends | Number of Nodes | Processors per Node          | Cores per Node | Memory per Node | Retires in |
| ---------- | --------------- | ---------------------------- | -------------- | --------------- | ---------- |
| No GPU     | 1               | Two Sky Lake CPUs @ 2.60GHz  | 24             | 96 GB           | 2023       |

${resource.name} Sub-Clusters

| Sub-Cluster | Number of Nodes | Processors per Node         | Cores per Node | Memory per Node | Retires in |
| ----------- | --------------- | --------------------------- | -------------- | --------------- | ---------- |
| A           | 550             | Two Sky Lake CPUs @ 2.60GHz | 24             | 96 GB           | 2023       | 

${resource.name} nodes run CentOS 7 and use Slurm (Simple Linux Utility for Resource Management) as the batch scheduler for resource and job management.  The application of operating system patches occurs as security needs dictate.  All nodes allow for unlimited stack usage, as well as unlimited core dump size (though disk space and server quotas may still be a limiting factor).

On ${resource.name}, ITaP recommends the following set of compiler, math library, and message-passing library for parallel code:

* ${resource.compiler}
* ${resource.mathlib}
* ${resource.mesglib}

This compiler and these libraries are loaded by default. To load the recommended set again:

    $ module load rcac

To verify what you loaded:

    $ module list
