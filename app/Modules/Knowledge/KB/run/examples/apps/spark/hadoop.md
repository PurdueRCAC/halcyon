---
title: Spark
tags:
---

# Spark

<a href="http://spark.apache.org">Apache Spark</a> is an open-source data analytics cluster computing framework. Spark is not tied to the two-stage MapReduce paradigm, and promises performance up to 100 times faster than Hadoop MapReduce for certain applications. Spark provides primitives for in-memory cluster computing that allows user programs to load data into a cluster's memory and query it repeatedly, making it well suited to machine learning algorithms.

Before to submit a Spark application to a YARN cluster, export environment variables:

<pre>
$ source /etc/default/hadoop
</pre>

To submit a Spark application to a YARN cluster:

<pre>
$ cd /apps/hathi/spark
$ ./bin/spark-submit --master yarn --deploy-mode cluster examples/src/main/python/pi.py 100
</pre>

Please note that there are two ways to specify the master: yarn-cluster and yarn-client. In cluster mode, your driver program will run on the worker nodes; while in client mode, your driver program will run within the spark-submit process which runs on the hathi front end. We recommand that you always use the cluster mode on hathi to avoid overloading the front end nodes.

To write your own spark jobs, use the Spark Pi as a baseline to start.

Spark can work with input files from both HDFS and local file system. The default after exporting the environment variables is from HDFS. To use input files that are on the cluster storage (e.g., data depot), specify: file:///path/to/file.

Note: when reading input files from cluster storage, the files must be accessible from any node in the cluster.

To run an interactive analysis or to learn the API with Spark Shell:

<pre>
$ cd /apps/hathi/spark
$ ./bin/pyspark
</pre>

Create a Resilient Distributed Dataset (RDD) from Hadoop InputFormats (such as HDFS files):

<pre>
>>> textFile = sc.textFile("derby.log")
15/09/22 09:31:58 INFO storage.MemoryStore: ensureFreeSpace(67728) called with curMem=122343, maxMem=278302556
15/09/22 09:31:58 INFO storage.MemoryStore: Block broadcast_1 stored as values in memory (estimated size 66.1 KB, free 265.2 MB)
15/09/22 09:31:58 INFO storage.MemoryStore: ensureFreeSpace(14729) called with curMem=190071, maxMem=278302556
15/09/22 09:31:58 INFO storage.MemoryStore: Block broadcast_1_piece0 stored as bytes in memory (estimated size 14.4 KB, free 265.2 MB)
15/09/22 09:31:58 INFO storage.BlockManagerInfo: Added broadcast_1_piece0 in memory on localhost:57813 (size: 14.4 KB, free: 265.4 MB)
15/09/22 09:31:58 INFO spark.SparkContext: Created broadcast 1 from textFile at NativeMethodAccessorImpl.java:-2
</pre>

Note: derby.log is a file on hdfs://hathi-adm.rcac.purdue.edu:8020/user/${user.username}/derby.log

Call the count() action on the RDD:

<pre>
>>> textFile.count()
15/09/22 09:32:01 INFO mapred.FileInputFormat: Total input paths to process : 1
15/09/22 09:32:01 INFO spark.SparkContext: Starting job: count at <stdin>:1
15/09/22 09:32:01 INFO scheduler.DAGScheduler: Got job 0 (count at <stdin>:1) with 2 output partitions (allowLocal=false)
15/09/22 09:32:01 INFO scheduler.DAGScheduler: Final stage: ResultStage 0(count at <stdin>:1)
......
15/09/22 09:32:03 INFO executor.Executor: Finished task 1.0 in stage 0.0 (TID 1). 1870 bytes result sent to driver
15/09/22 09:32:04 INFO scheduler.TaskSetManager: Finished task 0.0 in stage 0.0 (TID 0) in 2254 ms on localhost (1/2)
15/09/22 09:32:04 INFO scheduler.TaskSetManager: Finished task 1.0 in stage 0.0 (TID 1) in 2220 ms on localhost (2/2)
15/09/22 09:32:04 INFO scheduler.TaskSchedulerImpl: Removed TaskSet 0.0, whose tasks have all completed, from pool 
15/09/22 09:32:04 INFO scheduler.DAGScheduler: ResultStage 0 (count at <stdin>:1) finished in 2.317 s
15/09/22 09:32:04 INFO scheduler.DAGScheduler: Job 0 finished: count at <stdin>:1, took 2.548350 s
93
</pre>

To learn programming in Spark, refer to <a href="http://spark.apache.org/docs/latest/programming-guide.html">Spark Programming Guide</a> 

To learn submitting Spark applications, refer to <a href="http://spark.apache.org/docs/latest/submitting-applications.html">Submitting Applications</a> 
