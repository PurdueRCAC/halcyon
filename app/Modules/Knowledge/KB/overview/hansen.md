---
title: Overview of ${resource.name}
tags:
 - hansen
---
# Overview of ${resource.name}

Hansen was a compute cluster operated by ITaP and a member of Purdue's Community Cluster Program. Hansen went into production on September 15, 2011. Hansen consisted of Dell compute nodes with four 12-core AMD Opteron 6176 processors (48 cores per node) and either 96 GB, 192 GB, or 512 GB of memory. All nodes had 10 Gigabit Ethernet interconnects and a 5-year warranty. Hansen was decommissioned on October 1, 2016.

{::if resource.namesake != null}
# ${resource.name} Namesake

${resource.name} is named in honor of ${resource.namesake}${resource.namesakeimpact}. More information about {::/}{::if resource.namesakesex == m}his{::elseif resource.namesakesex == f}her{::else}{::/}{::if resource.namesake != null} life and impact on Purdue is available in an [ITaP Biography of ${resource.name}](/compute/${resource.dir}/bio/).
{::/}

# ${resource.name} Detailed Hardware Specification

Hansen consisted of three logical sub-clusters, each with a different amount of memory. Hansen-A nodes had 96 GB RAM; Hansen-B, 192 GB RAM; Hansen-C, 512 GB RAM. All nodes had 10 Gigabit Ethernet interconnects.

| Sub-Cluster | Number of Nodes | Processors per Node         | Cores per Node | Memory per Node | Retired in            |
| -------------- | -------------- | --------------------------- | -------------- | -------------- | ------------------------ |
| A    | 103                | Four 2.3 GHz 12-Core AMD Opteron 6176   | 48             | 96 GB          | 2016   |
| B    | 93                | Four 2.3 GHz 12-Core AMD Opteron 6176   | 48             | 192 GB          | 2016   |
| C    | 5                | Four 2.3 GHz 12-Core AMD Opteron 6176   | 48             | 512 GB          | 2016   |

${resource.name} nodes ran Red Hat Enterprise Linux 6 (RHEL6) and used Moab Workload Manager 8 and TORQUE Resource Manager 5 as the portable batch system (PBS) for resource and job management.

