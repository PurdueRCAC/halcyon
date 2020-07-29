---
title: Hive
tags:
 - hadoop
---

# Hive

<a href="https://hive.apache.org/">Apache Hive</a> is a data warehouse infrastructure built on top of Hadoop for providing data summarization, query, and analysis. Hive provides a mechanism to project structure onto this data and query the data using a SQL-like language called HiveQL. At the same time this language also allows traditional map/reduce programmers to plug in their custom mappers and reducers when it is inconvenient or inefficient to express this logic in HiveQL.


Load java module:

<pre>
$ module load java
</pre>

Export environment variables:

<pre>
$ source /etc/default/hadoop
$ export HIVE_HOME=/apps/hathi/hive/
$ export PATH=$HIVE_HOME/bin:$HIVE_HOME/hcatalog/bin:$PATH
</pre>

To access Hive:

<pre>
$ hive
15/09/10 15:14:16 INFO Configuration.deprecation: mapred.reduce.tasks is deprecated. Instead, use mapreduce.job.reduces
15/09/10 15:14:16 INFO Configuration.deprecation: mapred.min.split.size is deprecated. Instead, use mapreduce.input.fileinputformat.split.minsize
15/09/10 15:14:16 INFO Configuration.deprecation: mapred.reduce.tasks.speculative.execution is deprecated. Instead, use mapreduce.reduce.speculative
15/09/10 15:14:16 INFO Configuration.deprecation: mapred.min.split.size.per.node is deprecated. Instead, use mapreduce.input.fileinputformat.split.minsize.per.node
15/09/10 15:14:16 INFO Configuration.deprecation: mapred.input.dir.recursive is deprecated. Instead, use mapreduce.input.fileinputformat.input.dir.recursive
15/09/10 15:14:16 INFO Configuration.deprecation: mapred.min.split.size.per.rack is deprecated. Instead, use mapreduce.input.fileinputformat.split.minsize.per.rack
15/09/10 15:14:16 INFO Configuration.deprecation: mapred.max.split.size is deprecated. Instead, use mapreduce.input.fileinputformat.split.maxsize
15/09/10 15:14:16 INFO Configuration.deprecation: mapred.committer.job.setup.cleanup.needed is deprecated. Instead, use mapreduce.job.committer.setup.cleanup.needed

Logging initialized using configuration in jar:file:/apps/hathi/apache-hive-0.14.0-bin/lib/hive-common-0.14.0.jar!/hive-log4j.properties
SLF4J: Class path contains multiple SLF4J bindings.
SLF4J: Found binding in [jar:file:/usr/lib/gphd/hadoop/lib/slf4j-log4j12-1.7.5.jar!/org/slf4j/impl/StaticLoggerBinder.class]
SLF4J: Found binding in [jar:file:/apps/hathi/apache-hive-0.14.0-bin/lib/hive-jdbc-0.14.0-standalone.jar!/org/slf4j/impl/StaticLoggerBinder.class]
SLF4J: See http://www.slf4j.org/codes.html#multiple_bindings for an explanation.
SLF4J: Actual binding is of type [org.slf4j.impl.Log4jLoggerFactory]
hive>
</pre>

To create and browse Hive tables:

<pre>
$ hive> CREATE TABLE pokes_user (foo INT, bar STRING);
OK
Time taken: 1.56 seconds
$ hive> CREATE TABLE invites_user (foo INT, bar STRING) PARTITIONED BY (ds STRING);
OK
Time taken: 0.125 seconds
$ hive> SHOW TABLES;
OK
invites_user
pokes_user
Time taken: 0.114 seconds, Fetched: 2 row(s)
</pre>

To browse Hive tables from hdfs:
<ul>
<li>Go to <a href="http://hathi-adm.rcac.purdue.edu:50070">http://hathi-adm.rcac.purdue.edu:50070</a></li>
<li>Click on "Browse the filesystem"</li>
<li>Navigate to "/user/hive/warehouse"</li>
</ul>

To run the HCatalog Server from the command-line:
<pre>
$ cd /apps/hathi/hive
$ hcatalog/bin/hcat
</pre>

For more information, refer to <a href="https://cwiki.apache.org/confluence/display/Hive/GettingStarted#GettingStarted-SimpleExampleUseCases">GettingStarted - Apache Hive - Apache Software Foundation.</a>
