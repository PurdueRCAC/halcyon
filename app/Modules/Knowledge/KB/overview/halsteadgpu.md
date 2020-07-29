---
title: Overview of ${resource.name}
tags:
 - halsteadgpu
---
# Overview of ${resource.name}

Halstead-GPU was built through a partnership with HP and Intel in May 2017. Halstead-GPU consisted of HP compute nodes with two 10-core Intel Xeon-E5 processors (20 cores per node), 256 GB of memory, and two Tesla P100 GPUs. All nodes had 100 Gbps Infiniband interconnect and a 5-year warranty.

{::if resource.namesake != null}
# ${resource.name} Namesake

${resource.name} was named in honor of ${resource.namesake}${resource.namesakeimpact}. More information about {::/}{::if resource.namesakesex == m}his{::elseif resource.namesakesex == f}her{::else}{::/}{::if resource.namesake != null} life and impact on Purdue is available in an [ITaP Biography of Halstead](/compute/${resource.dir}/bio/).
{::/}

# ${resource.name} Specifications

All ${resource.name} nodes had 20 processor cores, 256 GB of RAM, and 100 Gbps Infiniband interconnects.

${resource.name} Front-Ends

| Front-Ends  | Number of Nodes | Processors per Node                                    | Cores per Node | Memory per Node | Retires in |
| ----------- | --------------- | ------------------------------------------------------ | -------------- | --------------- | ---------- |
| With GPU    | 2               | Two Haswell CPUs @ 2.60GHZ with Two Tesla P100 GPUs  | 20             | 256 GB          | 2022       |

${resource.name} Sub-Clusters

| Sub-Cluster | Number of Nodes | Processors per Node                                     | Cores per Node | Memory per Node | Retires in |
| ----------- | --------------- | ------------------------------------------------------- | -------------- | --------------- | ---------- |
| A           | 4               | Two Haswell CPUs @ 2.60GHz with Two Tesla P100 GPUs   | 20             | 256 GB          | 2022       |

${resource.name} nodes ran CentOS 7 and use Moab Workload Manager 8 and TORQUE Resource Manager 5 as the portable batch system (PBS) for resource and job management.  
