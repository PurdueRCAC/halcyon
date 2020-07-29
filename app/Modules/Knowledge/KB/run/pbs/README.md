---
title: Basics of PBS Jobs
order:
 - script
 - submit
 - status
 - output
 - hold
 - depend
 - cancel
expandtoc: true
---

# Basics of PBS Jobs

The *Portable Batch System (PBS)* is a system providing job scheduling and job management on compute clusters. With PBS, a user requests resources and submits a job to a queue. The system will then take jobs from queues, allocate the necessary nodes, and execute them.

**Do NOT run large, long, multi-threaded, parallel, or CPU-intensive jobs on a front-end login host.** All users share the front-end hosts, and running anything but the smallest test job will negatively impact everyone's ability to use ${resource.name}. Always use PBS to submit your work as a job. 

### Submitting a Job

There main steps to submitting a job are:

* [Create job submission script](script)
* [Submit job script](submit)
* [Monitor job status](status)
* [Check output](output)

Follow the links below for information on these steps, and other basic information about jobs. A number of [example PBS jobs](../examples) are also available.



