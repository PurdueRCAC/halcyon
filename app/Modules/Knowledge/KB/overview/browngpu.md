---
title: Overview of ${resource.name}
tags:
 - browngpu
---
# Overview of ${resource.name}

${resource.name} was optimized for communities running traditional, tightly-coupled science and engineering applications. ${resource.name} was built through a partnership with Dell and Intel in March 2018. ${resource.name} consisted of Dell compute nodes with two 12-core Intel Xeon Gold "Sky Lake" processors (24 cores per node), 96 GB of memory, and three Tesla P100 GPUs. All nodes had 100 Gbps EDR Infiniband interconnect and a 5-year warranty.

{::if resource.namesake != null}
# ${resource.name} Namesake

${resource.name} was named in honor of ${resource.namesake}${resource.namesakeimpact}. More information about {::/}{::if resource.namesakesex == m}his{::elseif resource.namesakesex == f}her{::else}{::/}{::if resource.namesake != null} life and impact on Purdue is available in an [ITaP Biography of ${resource.namesake}](/compute/${resource.dir}/bio/).
{::/}

# ${resource.name} Detailed Hardware Specification

All ${resource.name} nodes had 24 processor cores, 96 GB of RAM, and 100 Gbps Infiniband interconnects.

${resource.name} Front-Ends

| Front-Ends | Number of Nodes | Processors per Node                                    | Cores per Node  | Memory per Node | Retires in |
| ---------- | --------------- | ------------------------------------------------------ | --------------- | --------------- | ---------- |
| With GPU   | 1               | Two Sky Lake CPUs @ 2.60GHz with three Tesla P100 GPUs | 24              | 192 GB          | 2023       |

${resource.name} Sub-Clusters

| Sub-Cluster | Number of Nodes | Processors per Node                                    | Cores per Node | Memory per Node | Retires in | 
| ----------- | --------------- | ------------------------------------------------------ | -------------- | --------------- | ---------- |
| A           | 15              | Two Sky Lake CPUs @ 2.60GHz with three Tesla P100 GPUs | 24             | 192 GB          | 2023       |

${resource.name} nodes ran CentOS 7 and used Moab Workload Manager 8 and TORQUE Resource Manager 5 as the portable batch system (PBS) for resource and job management.
