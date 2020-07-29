---
title: Overview of ${resource.name}
tags:
 - conte
---
# Overview of ${resource.name}

Conte was built through a partnership with HP and Intel in June 2013, and was the largest of Purdue's flagship community clusters. Conte consisted of HP compute nodes with two 8-core Intel Xeon-E5 processors (16 cores per node) and 64 GB of memory. Each node was also equipped with two 60-core Xeon Phi coprocessors. All nodes had 40 Gbps FDR10 Infiniband connections and a 5-year warranty. Conte was decommissioned on August 1, 2018.

{::if resource.namesake != null}
# ${resource.name} Namesake

${resource.name} is named in honor of ${resource.namesake}${resource.namesakeimpact}. More information about {::/}{::if resource.namesakesex == m}his{::elseif resource.namesakesex == f}her{::else}{::/}{::if resource.namesake != null} life and impact on Purdue is available in an [ITaP Biography of ${resource.name}](/compute/${resource.dir}/bio/).
{::/}

# ${resource.name} Specifications

Most Conte nodes consisted of identical hardware. All Conte nodes had 16 processor cores, 64 GB RAM, and 40 Gbps Infiniband interconnects. Conte nodes were also each equipped with two 60-core Xeon Phi Coprocessors that could be used to further accelerate work tailored to these.

| Sub-Cluster | Number of Nodes | Processors per Node         | Cores per Node | Memory per Node | Retired in        |
| -------------- | -------------- | --------------------------- | -------------- | -------------- | ------------------------ |
| A    | 580                | Two 8-Core Intel Xeon-E5 + Two 60-Core Xeon Phi   | 16             | 64 GB          | 2018   |

At the time of retirement, ${resource.name} nodes ran Red Hat Enterprise Linux 6 (RHEL6) and used Moab Workload Manager 8 and TORQUE Resource Manager 5 as the portable batch system (PBS) for resource and job management.

