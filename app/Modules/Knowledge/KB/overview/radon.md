---
title: Overview of ${resource.name}
tags:
 - radon
---
# Overview of ${resource.name}

Radon was a compute cluster operated by ITaP for general campus use. Radon's final incarnation  consisted of 45 HP Moonshot compute nodes with 32 GB RAM and are connected by 10 Gigabit Ethernet (10GigE).

{::if resource.namesake != null}
# ${resource.name} Namesake

${resource.name} is named in honor of ${resource.namesake}${resource.namesakeimpact}. More information about {::/}{::if resource.namesakesex == m}his{::elseif resource.namesakesex == f}her{::else}{::/}{::if resource.namesake != null} life and impact on Purdue is available in an [ITaP Biography of ${resource.name}](/compute/${resource.dir}/bio/).
{::/}

# ${resource.name} Detailed Hardware Specification

Radon consisted of one sub-cluster "E". The nodes had 2.5 GHz quad-core, Hyper-Threading enabled Intel Xeon E3-1284 CPUs (8 logical cores), 32 GB RAM, and 10 Gigabit Ethernet.

| Sub-Cluster | Number of Nodes | Processors per Node         | Cores per Node | Memory per Node | Retired in           |
| -------------- | -------------- | --------------------------- | -------------- | -------------- | ------------------------ | ------- |
| E    | 45                | One Hyper-Threaded Quad-Core Xeon E3-1284L   | 8 (Logical)          | 32 GB          | 2018    |

At the time of retirement, ${resource.name} nodes ran Red Hat Enterprise Linux 6 (RHEL6) and used Moab Workload Manager 8 and TORQUE Resource Manager 5 as the portable batch system (PBS) for resource and job management.  

