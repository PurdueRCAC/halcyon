---
title: Spark
tags:
 - wholenode
 - sharednode
---
# Spark

<a href="http://spark.apache.org/docs/latest/index.html">Spark</a> is a fast and general engine for large-scale data processing. This section walks through how to submit and run a Spark job using PBS on the compute nodes of ${resource.name}.

<kbd>pbs-spark-submit</kbd> launches an Apache Spark program within a PBS job, including starting the Spark master and worker processes in standalone mode, running a user supplied Spark job, and stopping the Spark master and worker processes. The Spark program and its associated services will be constrained by the resource limits of the job and will be killed off when the job ends. This effectively allows PBS to act as a Spark cluster manager.

The following steps assume that you have a Spark program that can run without errors.

To use Spark and pbs-spark-submit, you need to load the following two modules to setup SPARK_HOME and PBS_SPARK_HOME environment variables.
<pre>
module load spark
module load pbs-spark-submit
</pre>

The following example submission script serves as a template to build your customized, more complex Spark job submission. This job requests 2 whole compute nodes for 10 minutes, and submits to the default queue.
<pre>
#PBS -N spark-pi
#PBS -l nodes=2:ppn=${resource.nodecores}

#PBS -l walltime=00:10:00
#PBS -q ${resource.queue}
#PBS -o spark-pi.out
#PBS -e spark-pi.err

cd $PBS_O_WORKDIR
module load spark
module load pbs-spark-submit
pbs-spark-submit $SPARK_HOME/examples/src/main/python/pi.py 1000
</pre>

In the submission script above, this command submits the <kbd>pi.py</kbd> program to the nodes that are allocated to your job.
<pre>
pbs-spark-submit $SPARK_HOME/examples/src/main/python/pi.py 1000
</pre>

You can set various environment variables in your submission script to change the setting of Spark program. For example, the following line sets the SPARK_LOG_DIR to $HOME/log. The default value is current working directory.
<pre>
export SPARK_LOG_DIR=$HOME/log
</pre>

The same environment variables can be set via the pbs-spark-submit command line argument. For example, the following line sets the SPARK_LOG_DIR to $HOME/log2.

<pre>
pbs-spark-submit --log-dir $HOME/log2
</pre>



<div class="inrows-wide">
<table class="inrows-wide">
<caption>The following table summarizes the environment variables that can be set. Please note that setting them from the command line arguments overwrites the ones that are set via shell export. Setting them from shell export overwrites the system default values.</caption>
        <tr>
                <th scope="col">Environment Variable</th>
                <th scope="col">Default</th>
                <th scope="col">Shell Export</th>
                <th scope="col">Command Line Args</th>
        </tr>
        <tr>
                <td>SPAKR_CONF_DIR</td>
                <td>$SPARK_HOME/conf</td>
                <td>export SPARK_CONF_DIR=$HOME/conf</td>
                <td>--conf-dir <confdir> or -C <confdir></td>
        </tr>
        <tr>
                <td>SPAKR_LOG_DIR</td>
                <td>Current Working Directory</td>
                <td>export SPARK_LOG_DIR=$HOME/log</td>
                <td>--log-dir <logdir> or -L <logdir></td>
        </tr>
        <tr>
                <td>SPAKR_LOCAL_DIR</td>
                <td>/tmp</td>
                <td>export SPARK_LOCAL_DIR=$RCAC_SCRATCH/local</td>
                <td>NA</td>
        </tr>
        <tr>
                <td>SCRATCHDIR</td>
                <td>Current Working Directory</td>
                <td>export SCRATCHDIR=$RCAC_SCRATCH/scratch</td>
                <td>--work-dir <workdir> or -d <workdir></td>
        </tr>
        <tr>
                <td>SPARK_MASTER_PORT</td>
                <td>7077</td>
                <td>export SPARK_MASTER_PORT=7078</td>
                <td>NA</td>
        </tr>
        <tr>
                <td>SPARK_DAEMON_JAVA_OPTS</td>
                <td>None</td>
                <td>export SPARK_DAEMON_JAVA_OPTS="-Dkey=value"</td>
                <td>-D key=value</td>
        </tr>

</table>
</div>

Note that SCRATCHDIR must be a shared scratch directory across all nodes of a job.

In addition, <kbd>pbs-spark-submit</kbd> supports command line arguments to change the properties of the Spark daemons and the Spark jobs. For example, the --no-stop argument tells Spark to not stop the master and worker daemons after the Spark application is finished, and the --no-init argument tells Spark to not initialize the Spark master and worker processes. This is intended for use in a sequence of invocations of Spark programs within the same job.

<pre>
pbs-spark-submit --no-stop   $SPARK_HOME/examples/src/main/python/pi.py 800
pbs-spark-submit --no-init   $SPARK_HOME/examples/src/main/python/pi.py 1000
</pre>

Use the following command to see the complete list of command line arguments.
<pre>
pbs-spark-submit -h
</pre>

To learn programming in Spark, refer to <a href="http://spark.apache.org/docs/latest/programming-guide.html">Spark Programming Guide</a>

To learn submitting Spark applications, refer to <a href="http://spark.apache.org/docs/latest/submitting-applications.html">Submitting Applications</a>
