---
title: Overview of ${resource.name}
tags:
 - workbench
---
# Overview of ${resource.name}

The Data Workbench is an interactive compute environment for non-batch big data analysis and simulation, and is a part of Purdue's Community Cluster Program. The Data Workbench consists of Dell compute nodes with 24-core AMD EPYC 7401P processors (24 cores per node), and 512 GB of memory. All nodes are interconnected with 10 Gigabit Ethernet. The Data Workbench entered production on October 1, 2017.

To purchase access to ${resource.name} today, go to the [Cluster Access Purchase](https://www.rcac.purdue.edu/purchase/) page. Please subscribe to our Community Cluster Program Mailing List to stay informed on the latest purchasing developments or contact us via email at <rcac-cluster-purchase@lists.purdue.edu> if you have any questions.

# ${resource.name} Specifications

The Data Workbench consists of Dell Servers with one 24-core AMD EPYC 7401P CPU, 512 GB of memory, and 10 Gigabit Ethernet network.

| Front-Ends | Number of Nodes | Processors per Node                   | Cores per Node | Memory per Node | Retires in  |
| ---------- | --------------- | ------------------------------------- | -------------- | ----------------| ----------- | 
| 1          | 6               | One AMD EPYC 7401P CPU @ 2.00GHz      | 24             | 512 GB          | 2024        |

${resource.name} nodes run CentOS 7 and are  intended for interactive work via the Thinlinc remote desktop software, Jupyterhub, or Rstudio Server. ${resource.name} provides no batch system.

The application of operating system patches occurs as security needs dictate.  All nodes allow for unlimited stack usage, as well as unlimited core dump size (though disk space and server quotas may still be a limiting factor). All nodes guarantee even access to CPU and memory resources via Linux cgroups.

On ${resource.name}, ITaP recommends the following set of compiler and math libraries:

* ${resource.compiler}
* ${resource.mathlib}

This compiler and these libraries are loaded by default. To load the recommended set again:

    $ module load rcac

To verify what you loaded:

    $ module list

{::if resource.regmaint != null}
# ${resource.name} Regular Maintenance

Regular planned maintenance on ${resource.name} is scheduled for ${resource.regmaint}.
{::/}
