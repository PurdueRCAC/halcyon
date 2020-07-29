---
title: Xeon Phi
tags:
 - phi
---
# Xeon Phi

The ${resource.name} cluster has two Xeon Phi coprocessors in each node.  Each Phi contains 60 processors (240 threads) and 8GB of memory. Phi cards support API extensions, such as <em>OpenCL</em>, to many programming languages including C, C++, and Fortran.

This section illustrates how to use PBS to submit a simple Phi program.

Suppose that you named your executable file <kbd>phi_hello</kbd> from the sample code [`phi_hello.c`](/knowledge/downloads/compile/src/phi_hello.c).  Prepare a job submission file with an appropriate name, here named <kbd>phi_hello.sub</kbd>:

<pre>
#!/bin/sh -l
# FILENAME:  phi_hello.sub

cd $PBS_O_WORKDIR

host=`hostname -s`

./phi_hello
</pre>

Since PBS always sets the working directory to your home directory, you should either execute the <kbd>cd $PBS_O_WORKDIR</kbd> command, which will set the run-time current working directory to the directory from which you submitted the job submission, or give the full path to the directory containing the program.

The PBS system provides several mechanisms to aid in the request, allocation, and use of Phis on a compute node. The option <kbd>mics</kbd> is used to select the desired number of Phis per compute node. On ${resource.name} nodes, up to two Phis can be selected per compute node. The <kbd>mics</kbd> option operates very similarly to the <kbd>ppn</kbd> option used to select the desired number of processors cores per compute node. The option <kbd>mics</kbd> can not be larger than the number of Phis in the compute nodes.</p>
 
During job run-time, PBS sets a environment variable <kbd>$PBS_MICFILE</kbd> that contains a file listing the Phis allocated to this job. This file is very similar to the <kbd>$PBS_NODEFILE</kbd> environment variable. The <kbd>$PBS_MICFILE</kbd> variable will only be set if the <kbd>mics</kbd> option is provided with job submission. More detailed information on using allocated Phis can be found in this example code and below.
 
 Submit the Phi job to a queue on ${resource.name}, such as <kbd>${resource.queue}</kbd>, and request one compute node and one Phi with one minute of wall time.
 
<pre>
$ qsub -q ${resource.queue} -l nodes=1:ppn=${resource.nodecores}:mics=1,walltime=00:01:00 phi_hello.sub
</pre> 

View two new files in your directory (<kbd>.o</kbd> and <kbd>.e</kbd>):
<pre>
$ ls -l
phi_hello
phi_hello.c
phi_hello.sub
phi_hello.sub.emyjobid
phi_hello.sub.omyjobid
</pre> 

View results in the file for all standard output, phi_hello.sub.omyjobid

<pre>
hello from ${resource.hostname}-a000-mic0.rcac.purdue.edu
</pre> 

If the job failed to run, then view error messages in the file <kbd>phi_hello.sub.emyjobid</kbd>.

A few examples of Phi job submission and Phi allocation follow/

Using multiple Phis within a program will require more complex processing of the <kbd>$PBS_MICFILE</kbd> file.

<strong>You must use <kbd>nodes=N:ppn=16:mics=2</kbd> to enable native mode.</strong>

To execute a native binary on the Phi, ssh can be used to log into the Phi itself.
<pre>
$ ssh mic0
$ ssh mic1
</pre>

The `module` command can also be used on the Phi itself, however it currently has a very limited number of native modules.

<pre>
module avail
module load python
</pre>

To run a program in symmetric mode, that is using the host and either one or both Phis on the same node, you must first compile a binary for the host (<kbd>test</kbd>) and also one for the Phi (<kbd>test.mic</kbd>). For details on how to compile programs for the Phi, see the [Compiling Xeon Phi Programs](../../../../compile/phi) section</a>.

Once both binaries are available, an interactive job can be used to run the program on both the host and the device. First, request a full node with 2 Phis in an interactive job:

<pre>
$ qsub -q ${resource.queue} -l nodes=1:ppn=${resource.nodecores}:mics=2 -I
</pre>

Once the job starts and a node is allocated, prepare a <kbd>mympihosts</kbd> hosts file using the head node, and both Phis. For example:

<pre>
${resource.hostname}-a000
mic0
mic1
</pre>

Then, make sure the appropriate modules are loaded and that the following variables are set:
<pre>
${resource.hostname}-a000:~ $ module load impi
${resource.hostname}-a000:~ $ export I_MPI_MIC=enable
${resource.hostname}-a000:~ $ export I_MPI_FABRICS=shm:tcp
${resource.hostname}-a000:~ $ export I_MPI_MIC_POSTFIX=.mic
</pre> 

To start ranks on both the host and the Phis:

<pre>
${resource.hostname}-a000:~ $ mpiexec -n 4 -machinefile ./mympihosts ${resource.scratch}/${user.usernameletter}/${user.username}/test
My rank is: 0/4 host ${resource.hostname}-a000.rcac.purdue.edu
My rank is: 3/4 host ${resource.hostname}-a000.rcac.purdue.edu
My rank is: 2/4 host ${resource.hostname}-a000-mic1.rcac.purdue.edu
My rank is: 1/4 host ${resource.hostname}-a000-mic0.rcac.purdue.edu
</pre>

The example above illustrates one way to start symmetric jobs interactively.

You may find that you run into errors when trying to run a Phi-enabled binary. If a Phi is not installed or improperly configured, you may see these error messages.

If you're running MKL offload with host fallback disabled:

<pre>
AO library failed to initialize!
Aborted (core dumped)
</pre>

If you're running OpenMP offload:
<pre>
offload error: cannot offload to MIC - device is not available
</pre>

If you're running a symmetric program and I_MPI_FABRIC is not changed to <kbd>shm:tcp</hbd>:
<pre>
[1] MPI startup(): dapl fabric is not available and fallback fabric is not enabled
APPLICATION TERMINATED WITH THE EXIT STRING: Interrupt (signal 2)
</pre>

If you see these error messages, please contact us at <a href="mailto:rcac-help@purdue.edu">rcac-help@purdue.edu</a> with your Job ID and which node it occurred on, if available.
