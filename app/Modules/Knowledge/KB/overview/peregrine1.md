---
title: Overview of ${resource.name}
tags:
 - peregrine1
---
# Overview of ${resource.name}

Peregrine 1 was a state-of-the-art cluster for the Purdue Calumet campus operated by ITaP from the West Lafayette campus. Installed on June 26, 2012, Peregrine 1 was the second major research cluster to have been hosted on the Calumet campus. The cluster was consequently relocated to the West Lafayette campus on August 19, 2015. Peregrine 1 consisted of HP compute nodes with two 8-core Intel Xeon-E5 processors (16 cores per node) and either 32 GB or 64 GB of memory. All nodes also featured 56 Gbps FDR Infiniband connections.  Peregrine 1 was retired on October 12, 2016.

{::if resource.namesake != null}
# ${resource.name} Namesake

${resource.name} is named in honor of ${resource.namesake}${resource.namesakeimpact}. More information about {::/}{::if resource.namesakesex == m}his{::elseif resource.namesakesex == f}her{::else}{::/}{::if resource.namesake != null} life and impact on Purdue is available in an [ITaP Biography of ${resource.name}](/compute/${resource.dir}/bio/).
{::/}

# ${resource.name} Detailed Hardware Specification

All Peregrine 1 nodes consisted of identical hardware. All Peregrine 1 nodes had 16 processor cores, 32 GB RAM, and 56 Gbps Infiniband interconnects.

| Sub-Cluster | Number of Nodes | Processors per Node         | Cores per Node | Memory per Node | Retired in |
| -------------- | -------------- | --------------------------- | -------------- | -------------- | --------------------|
| A    | 29                | Two 8-Core Intel Xeon-E5   | 16             | 32 GB          | 2016 |

${resource.name} nodes ran Red Hat Enterprise Linux 6 (RHEL6) and used Moab Workload Manager 8 and TORQUE Resource Manager 5 as the portable batch system (PBS) for resource and job management.
