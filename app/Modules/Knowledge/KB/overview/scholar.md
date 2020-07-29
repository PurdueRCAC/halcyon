---
title: Overview of ${resource.name}
tags:
 - scholar
---
# Overview of ${resource.name}

Scholar is a small computer cluster, suitable for classroom learning about high performance computing (HPC).  It consists of 7 interactive login servers and 8 batch worker nodes.

It can be accessed as a typical cluster, with a job scheduler distributing batch jobs onto its worker nodes, or as an interactive resource, with software packages available through a desktop-like environment on its login servers.

If you have a class that you think will benefit from the use of Scholar, you can schedule it for your class through our web page at: [Class Account Request](/account/class). 
You only need to register your class itself. All students who register for the class will automatically get login privileges to the Scholar cluster.
As a batch resource, the cluster has access to typical HPC software packages and tool chains; as an interactive resource, Scholar provides a Linux remote desktop, or a Jupyter notebook server, or an R Studio server.  Jupyter and R Studio can be used by students without any reliance on Linux knowledge or experience.

# ${resource.name} Specifications

All Scholar compute nodes have 20 processor cores, 64 GB RAM, and 56 Gbps Infiniband interconnects.

${resource.name} Front-Ends

| Front-Ends  | Number of Nodes | Processors per Node                                    | Cores per Node | Memory per Node | Retires in |
| ----------  | --------------- | ------------------------------------------------------ | -------------- | --------------- | ---------- |
| No GPU      | 4               | Two Haswell CPUs @ 2.60GHz                           | 20             | 512 GB          | 2023       |
| With GPU    | 3               | Two Sky Lake CPUs @ 2.60GHz with one NVIDIA Tesla V100 | 20             | 754 GB          | 2023       |

${resource.name} Sub-Clusters

| Sub-Cluster | Number of Nodes | Processors per Node          | Cores per Node | Memory per Node | Retires in |
| ----------- | --------------- | ---------------------------- | -------------- | --------------- | ---------- |
| A           | 24              | Two Haswell CPUs @ 2.60GHz | 20             | 64 GB           | 2023       |
| G           | 4              | Two Skylake CPUs @ 2.10GHz with one NVIDIA Tesla V100 32GB | 16             | 192 GB           | 2023       |
Faculty who would like to know more about Scholar, please read the [Faculty Guide](/policies/faculty)
