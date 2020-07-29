---
title: Job Submission
tags:
 - hadoop
---
# Job Submission

First, stage input data:
<pre>
$ cd
$ pwd
/home/${user.username}
$ wget http://www.gutenberg.org/ebooks/44604.txt.utf-8
--2014-08-31 21:36:16--  http://www.gutenberg.org/ebooks/44604.txt.utf-8
Resolving www.gutenberg.org... 152.19.134.47
Connecting to www.gutenberg.org|152.19.134.47|:80... connected.
HTTP request sent, awaiting response... 302 Found
Location: http://www.gutenberg.org/cache/epub/44604/pg44604.txt [following]
--2014-08-31 21:36:16--  http://www.gutenberg.org/cache/epub/44604/pg44604.txt
Reusing existing connection to www.gutenberg.org:80.
HTTP request sent, awaiting response... 200 OK
Length: 129202 (126K) [text/plain]
Saving to: “44604.txt.utf-8”

100%[===================================================================>] 129,202      639K/s   in 0.2s

2014-08-31 21:36:16 (639 KB/s) - “44604.txt.utf-8” saved [129202/129202]
</pre>

Copy the input data into HDFS scratch:
<pre>
$ hdfs dfs -copyFromLocal 44604.txt.utf-8 $RCAC_SCRATCH/input
$ hdfs dfs -ls $RCAC_SCRATCH/input
Found 1 item
-rw-r--r-- 1 ${user.username} mygroup 129202 Aug 31 21:36 /user/${user.username}/input/44604.txt.utf-8
</pre>

Compile the [WordCount.java](/knowledge/downloads/run/hadoop/src/WordCount.java) code, and create a jar file:
<pre>
$ export CLASSPATH=$(hadoop classpath)
$ mkdir wordcount
$ javac -d  wordcount/  WordCount.java
$ jar -cvf wordcount.jar -C wordcount/ .
added manifest
adding: org/(in = 0) (out= 0)(stored 0%)
adding: org/myorg/(in = 0) (out= 0)(stored 0%)
adding: org/myorg/WordCount$Map.class(in = 1938) (out= 798)(deflated 58%)
adding: org/myorg/WordCount.class(in = 1546) (out= 749)(deflated 51%)
adding: org/myorg/WordCount$Reduce.class(in = 1611) (out= 649)(deflated 59%)
</pre>

Submit the job:
<pre>
$ hadoop jar wordcount.jar org.myorg.WordCount /user/${user.username}/input /user/${user.username}/output
14/08/31 22:24:56 WARN mapred.JobClient: Use GenericOptionsParser for parsing the arguments. Applications should implement Tool for the same.
14/08/31 22:24:57 WARN util.NativeCodeLoader: Unable to load native-hadoop library for your platform... using builtin-java classes where applicable
14/08/31 22:24:57 WARN snappy.LoadSnappy: Snappy native library not loaded
14/08/31 22:24:57 INFO mapred.FileInputFormat: Total input paths to process : 1
14/08/31 22:24:57 INFO mapred.JobClient: Running job: job_201407221042_0090
14/08/31 22:24:58 INFO mapred.JobClient:  map 0% reduce 0%
14/08/31 22:25:04 INFO mapred.JobClient:  map 12% reduce 0%
14/08/31 22:25:05 INFO mapred.JobClient:  map 74% reduce 0%
14/08/31 22:25:06 INFO mapred.JobClient:  map 79% reduce 0%
14/08/31 22:25:07 INFO mapred.JobClient:  map 84% reduce 0%
14/08/31 22:25:08 INFO mapred.JobClient:  map 100% reduce 0%
14/08/31 22:25:12 INFO mapred.JobClient:  map 100% reduce 13%
14/08/31 22:25:13 INFO mapred.JobClient:  map 100% reduce 19%
14/08/31 22:25:14 INFO mapred.JobClient:  map 100% reduce 58%
14/08/31 22:25:15 INFO mapred.JobClient:  map 100% reduce 67%
14/08/31 22:25:20 INFO mapred.JobClient:  map 100% reduce 69%
14/08/31 22:25:21 INFO mapred.JobClient:  map 100% reduce 77%
14/08/31 22:25:22 INFO mapred.JobClient:  map 100% reduce 100%
14/08/31 22:25:23 INFO mapred.JobClient: Job complete: job_201407221042_0090
14/08/31 22:25:23 INFO mapred.JobClient: Counters: 28
14/08/31 22:25:23 INFO mapred.JobClient:   Job Counters
14/08/31 22:25:23 INFO mapred.JobClient:     Launched reduce tasks=95
14/08/31 22:25:23 INFO mapred.JobClient:     SLOTS_MILLIS_MAPS=144846
14/08/31 22:25:23 INFO mapred.JobClient:     Total time spent by all reduces waiting after reserving slots (ms)=0
14/08/31 22:25:23 INFO mapred.JobClient:     Total time spent by all maps waiting after reserving slots (ms)=0
14/08/31 22:25:23 INFO mapred.JobClient:     Rack-local map tasks=59
14/08/31 22:25:23 INFO mapred.JobClient:     Launched map tasks=95
14/08/31 22:25:23 INFO mapred.JobClient:     Data-local map tasks=36
14/08/31 22:25:23 INFO mapred.JobClient:     SLOTS_MILLIS_REDUCES=818724
14/08/31 22:25:23 INFO mapred.JobClient:   FileSystemCounters
14/08/31 22:25:23 INFO mapred.JobClient:     FILE_BYTES_READ=170026
14/08/31 22:25:23 INFO mapred.JobClient:     HDFS_BYTES_READ=395641
14/08/31 22:25:23 INFO mapred.JobClient:     FILE_BYTES_WRITTEN=9229657
14/08/31 22:25:23 INFO mapred.JobClient:     HDFS_BYTES_WRITTEN=50396
14/08/31 22:25:23 INFO mapred.JobClient:   Map-Reduce Framework
14/08/31 22:25:23 INFO mapred.JobClient:     Map input records=3070
14/08/31 22:25:23 INFO mapred.JobClient:     Reduce shuffle bytes=223606
14/08/31 22:25:23 INFO mapred.JobClient:     Spilled Records=27712
14/08/31 22:25:23 INFO mapred.JobClient:     Map output bytes=212306
14/08/31 22:25:23 INFO mapred.JobClient:     CPU time spent (ms)=152550
14/08/31 22:25:23 INFO mapred.JobClient:     Total committed heap usage (bytes)=32210419712
14/08/31 22:25:23 INFO mapred.JobClient:     Map input bytes=129202
14/08/31 22:25:23 INFO mapred.JobClient:     Combine input records=22421
14/08/31 22:25:23 INFO mapred.JobClient:     SPLIT_RAW_BYTES=10640
14/08/31 22:25:23 INFO mapred.JobClient:     Reduce input records=13856
14/08/31 22:25:23 INFO mapred.JobClient:     Reduce input groups=5270
14/08/31 22:25:23 INFO mapred.JobClient:     Combine output records=13856
14/08/31 22:25:23 INFO mapred.JobClient:     Physical memory (bytes) snapshot=29026689024
14/08/31 22:25:23 INFO mapred.JobClient:     Reduce output records=5270
14/08/31 22:25:23 INFO mapred.JobClient:     Virtual memory (bytes) snapshot=900152020992
14/08/31 22:25:23 INFO mapred.JobClient:     Map output records=22421
</pre>

Examine the output of the WordCount application:
<pre>
$ hadoop  fs -cat $RCAC_SCRATCH/output/part-00000 | head
1.E     1
18,     3
26      4
31.]    1
58      2
Brokers;        1
Deadshot,       1
Director        1
FITNESS 1
Interesting     1
</pre>

Remove the output directory so that the example can be run again.
<pre>
$ hadoop fs -rm -r $RCAC_SCRATCH/output
</pre>
