---
title: Overview of ${resource.name}
tags:
 - carter
---
# Overview of ${resource.name}

Carter was launched through an ITaP partnership with Intel in November 2011 and was a member of Purdue's Community Cluster Program. Carter consisted of HP compute nodes with two 8-core Intel Xeon-E5 processors (16 cores per node) and between 32 GB and 256 GB of memory. A few NVIDIA GPU-accelerated nodes were also available. All nodes had 56 Gbps FDR Infiniband connections and a 5-year warranty. Carter was decommissioned on April 30, 2017.

{::if resource.namesake != null}
# ${resource.name} Namesake


${resource.name} was named in honor of ${resource.namesake}${resource.namesakeimpact}. More information about {::/}{::if resource.namesakesex == m}his{::elseif resource.namesakesex == f}her{::else}{::/}{::if resource.namesake != null} life and impact on Purdue is available in an [ITaP Biography of ${resource.name}](/compute/${resource.dir}/bio/).
{::/}

# ${resource.name} Detailed Hardware Specification

All Carter nodes had 16 processor cores, between 32 GB and 256 GB RAM, and 56 Gbps Infiniband interconnects.  Carter-G nodes were each equipped with three NVIDIA Tesla GPUs.

| Sub-Cluster | Number of Nodes | Processors per Node         | Cores per Node | Memory per Node | Retired on             |
| -------------- | -------------- | --------------------------- | -------------- | -------------- | ------------------------ |
| A    | 556            | Two 8-Core Intel Xeon-E5    | 16             | 32 GB          | 2017   |
| B    | 80             | Two 8-Core Intel Xeon-E5    | 16             | 64 GB          | 2017   |
| C    | 12             | Two 8-Core Intel Xeon-E5    | 16             | 256 GB         | 2017   |
| G    | 12             | Two 8-Core Intel Xeon-E5 + Three NVIDIA Tesla M2090 GPUs   | 16             | 128 GB          | 2017   |


