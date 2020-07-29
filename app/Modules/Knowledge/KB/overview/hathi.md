---
title: Overview of ${resource.name}
tags:
 - hathi
---
# Overview of ${resource.name}

Hathi was a shared Hadoop cluster operated by ITaP, and was a shared resource available to partners in Purdue's Community Cluster Program. Hathi went into production on September 8, 2014. 

Hathi consisted of two components: the Hadoop Distributed File System (HDFS), and a MapReduce framework for job and task tracking.

The Hadoop Distributed File System (HDFS) was a distributed file system designed to run on commodity hardware. It had many similarities with existing distributed file systems. However, the differences from other distributed file systems were significant. HDFS was highly fault-tolerant and was designed to be deployed on low-cost hardware. HDFS provided high throughput access to application data and was suitable for applications that have large data sets. HDFS relaxed a few POSIX requirements to enable streaming access to file system data.

A Hadoop cluster had several components:

* Name Node
* Resource Manager
* Data Node
* Task Manager

{::if resource.namesake != null}
# ${resource.name} Namesake

${resource.name} was named in honor of ${resource.namesake}${resource.namesakeimpact}. More information about {::/}{::if resource.namesakesex == m}his{::elseif resource.namesakesex == f}her{::else}{::/}{::if resource.namesake != null} life and impact on Purdue is available in an [ITaP Biography of ${resource.name}](/compute/${resource.dir}/bio/).
{::/}

# ${resource.name} Detailed Hardware Specification

Hathi consisted of 4 Dell compute nodes with two 8-core Intel E5-2650v2 CPUs, 32 GB of memory, and 48TB of local storage per node for a total cluster capacity of 288TB. All nodes had 40 Gigabit Ethernet interconnects and a 5-year warranty.

| Sub-Cluster | Number of Nodes | Processors per Node         | Cores per Node | Memory per Node | Retired in  |
| -------------- | -------------- | --------------------------- | -------------- | -------------- | ------------------------ | 
| A    | 4                | Two 8-core Intel E5-2650v2 + 48TB storage  | 16          | 256 GB          | 2018    | 

