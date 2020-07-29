---
title: Job Status
tags:
 - hadoop
---
# Job Status
To access HDFS and MapReduce jobs:

<pre>
Hadoop NameNode: <a href="http://hathi-adm.rcac.purdue.edu:50070">http://hathi-adm.rcac.purdue.edu:50070</a>
All Applications: <a href="http://hathi-adm.rcac.purdue.edu:8088">http://hathi-adm.rcac.purdue.edu:8088</a>
</pre>

To list MapReduce tasks and check their status:

<pre>
$ hadoop job -list all
2 jobs submitted
States are:
        Running : 1     Succeded : 2    Failed : 3      Prep : 4
JobId   State   StartTime       UserName        Priority        SchedulingInfo
job_201407221042_0088   3       1409538179006   ${user.username}      NORMAL  NA
job_201407221042_0090   2       1409538297352   ${user.username}      NORMAL  NA
</pre>

To view the status of a single MapReduce job:
<pre>
$ hadoop job -status job_201407221042_0090

Job: job_201407221042_0090
file: hdfs://hathi.rcac.purdue.edu/tmp/hadoop-mapred/mapred/staging/${user.username}/.staging/job_201407221042_0090/job.xml
tracking URL: http://hathi.rcac.purdue.edu:50030/jobdetails.jsp?jobid=job_201407221042_0090
map() completion: 1.0
reduce() completion: 1.0
Counters: 28
        Job Counters
                Launched reduce tasks=95
                SLOTS_MILLIS_MAPS=144846
                Total time spent by all reduces waiting after reserving slots (ms)=0
                Total time spent by all maps waiting after reserving slots (ms)=0
                Rack-local map tasks=59
                Launched map tasks=95
                Data-local map tasks=36
                SLOTS_MILLIS_REDUCES=818724
        FileSystemCounters
                FILE_BYTES_READ=170026
                HDFS_BYTES_READ=395641
                FILE_BYTES_WRITTEN=9229657
                HDFS_BYTES_WRITTEN=50396
        Map-Reduce Framework
                Map input records=3070
                Reduce shuffle bytes=223606
                Spilled Records=27712
                Map output bytes=212306
                CPU time spent (ms)=152550
                Total committed heap usage (bytes)=32210419712
                Map input bytes=129202
                Combine input records=22421
                SPLIT_RAW_BYTES=10640
                Reduce input records=13856
                Reduce input groups=5270
                Combine output records=13856
                Physical memory (bytes) snapshot=29026689024
                Reduce output records=5270
                Virtual memory (bytes) snapshot=900152020992
                Map output records=22421
</pre>
