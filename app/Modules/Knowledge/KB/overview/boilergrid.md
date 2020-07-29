---
title: BoilerGrid Overview
tags: 
 - boilergrid
---

# ${resource.name} Overview

${resource.name} was a large, high-throughput, distributed computing system operated by ITaP, and used the <a href="http://www.cs.wisc.edu/condor/" target="_blank" rel="noopener">HTCondor system</a> developed by the HTCondor Project at the University of Wisconsin.  ${resource.name} provided a way for you to run programs on large numbers of otherwise idle computers in various locations, including any temporarily under-utilized high-performance cluster resources as well as some desktop machines not currently in use.

${resource.name} scavenged cycles from many ITaP research systems.  ${resource.name} also used idle time of machines around the Purdue West Lafayette campus.  Whenever the primary scheduling system on any of these machines needed a compute node back or a user sat down and started to use a desktop computer, HTCondor would stop its job and, if possible, checkpointed its work.  HTCondor then immediately tried to restart this job on some other available compute node in ${resource.name}. Because this model limited the ability to do parallel processing and communications, ${resource.name} was only appropriate for relatively quick serial jobs.

${resource.name} used <a href="http://research.cs.wisc.edu/htcondor/manual/">HTCondor 7.8.7</a>.
