---
title: Overview of ${resource.name}
tags:
 - snyder
---
# Overview of ${resource.name}

${resource.name} is a Purdue Community Cluster which is continually expanded and refreshed, optimized for data intensive applications requiring large amounts of shared memory per node, such as life sciences. ${resource.name} was originally built through a partnership with HP and Intel in April 2015, though it has been most recently expanded with nodes from Dell. ${resource.name} consists of a variety of compute node configurations as shown in the table below. All nodes have 40 Gbps Ethernet connections and a 5-year warranty. ${resource.name} is expanded annually, with each year's purchase of nodes to remain in production for 5 years from their initial purchase.

To purchase access to ${resource.name} today, go to the [Cluster Access Purchase](https://www.rcac.purdue.edu/purchase/) page. Please subscribe to our Community Cluster Program Mailing List to stay informed on the latest purchasing developments or contact us via email at <rcac-cluster-purchase@lists.purdue.edu> if you have any questions.

{::if resource.namesake != null}
# ${resource.name} Namesake

${resource.name} is named in honor of ${resource.namesake}${resource.namesakeimpact}. More information about {::/}{::if resource.namesakesex == m}his{::elseif resource.namesakesex == f}her{::else}{::/}{::if resource.namesake != null} life and impact on Purdue is available in an [ITaP Biography of ${resource.name}](/compute/${resource.dir}/bio/).
{::/}

# ${resource.name} Specifications

${resource.name} compute node hardware varies.  See below.

${resource.name} Front-Ends

| Front-Ends  | Number of Nodes | Processors per Node           | Cores per Node | Memory per Node | Retires in |
| ----------- | --------------- | ----------------------------- | -------------- | --------------- | ---------- |
|             | 2               | Two Haswell CPUs @ 2.60GHz  | 20             | 64 GB           | 2020       |

${resource.name} Sub-Clusters

| Sub-Cluster | Number of Nodes | Processors per Node           | Cores per Node | Memory per Node | Retires in |
| ----------- | --------------- | ----------------------------- | -------------- | --------------- | ---------- |
| A           | 52              | Two Haswell CPUs @ 2.60GHz  | 20             | 256 GB          | 2020       |
| B           | 7               | Two Haswell CPUs @ 2.60GHz  | 20             | 512 GB          | 2020       |
| C           | 10              | Two Haswell CPUs @ 2.60GHz  | 20             | 512 GB          | 2021       |
| D           | 2               | Two Haswell CPUs @ 2.60GHz  | 20             | 1 TB            | 2021       |
| E           | 8               | Two Sky Lake CPUs @ 2.60Hz    | 24             | 384 GB          | 2022       |

${resource.name} nodes run CentOS 7 and use Slurm (Simple Linux Utility for Resource Management) as the batch scheduler for resource and job management.  The application of operating system patches occurs as security needs dictate.  All nodes allow for unlimited stack usage, as well as unlimited core dump size (though disk space and server quotas may still be a limiting factor).

On ${resource.name}, ITaP recommends the following set of compiler, math library, and message-passing library for parallel code:

* ${resource.compiler}
* ${resource.mathlib}
* ${resource.mesglib}

This compiler and these libraries are loaded by default. To load the recommended set again:

    $ module load rcac

To verify what you loaded:

    $ module list
