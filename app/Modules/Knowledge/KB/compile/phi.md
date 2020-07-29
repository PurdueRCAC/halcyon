---
title: Xeon Phi
tags:
 - phi
---
# Xeon Phi

The Xeon Phi is a parallel coprocessor card that works similarly to a GPU used for compute, but has several differences. Rather than have many specialized cores/threads that a GPU has, the Xeon Phi has many (60) x86 cores. Each x86 core is comparable to a 1GHz Pentium Pro with hyperthreading enabled (4 threads per core) which gives each card a total of 240 threads to work with. The Phi also has a specialized 512-bit SIMD unit that can handle vector operations. The ${resource.name} cluster nodes have two Xeon Phi coprocessors with 8GB of memory each.

The Phi has several different methods of programming and running. They are OpenMP offload, MKL offload, native execution, and OpenCL.

Here is a simple OpenMP-offloading Phi program: [`phi_hello.c`](/knowledge/downloads/compile/src/phi_hello.c)

Both front-ends and Phi-enabled compute nodes have the Intel tools and libraries available to compile Phi programs. To compile a Phi program, load the intel compiler, and use <kbd>icc</kbd> to compile the program:
<pre>
$ module load intel
$ cd myworkingdirectory
$ icc phi_hello.c -o phi_hello
</pre>

This will produce an offload-capable binary. It is run from the host node where Phis are installed. This program runs gethostname() on the host and each of the two Phis installed in the machine and prints the output. You can see that the phi has a naming scheme of <kbd>&lt;hostname&gt;-micN</kbd>, where N will be either 0 or 1, denoting either the first or second Phi installed.

The next examples show how to compile a more advanced program for the various modes for the Phi. To see this example in greater detail, please see "<a href="http://www.drdobbs.com/parallel/programming-intels-xeon-phi-a-jumpstart/240144160" target="_blank" rel="noopener">Programming Intel's Xeon Phi: A Jumpstart Introduction</a>".

A more complex Phi program: [`matrix.c`](/knowledge/downloads/compile/src/matrix.c) 

This example uses MKL offload with host-based OpenMP, so the MKL_MIC_ENABLE option is enabled.

Compile for host-based OpenMP (MKL offload):
<pre>
$ icc -mkl -O3 -no-offload -openmp -Wno-unknown-pragmas -std=c99 -vec-report3 matrix.c -o matrix.omp
</pre>

<pre>
#!/bin/bash
# mkloffload.sh
export MKL_MIC_ENABLE=1 # enable MKL offload
export OFFLOAD_DEVICES=0,1 # specify which phis to run on
export MKL_MIC_DISABLE_HOST_FALLBACK=1 # prevent running when phis are not offloading
export MIC_ENV_PREFIX=PHI
export PHI_KMP_AFFINITY="granularity=thread,balanced"

nThreads=16 # number of cpu cores
i=500
while [ $i -le 11000 ] # increases the size of the matrix on each run
do
   echo -n "mklOffload "
   ./matrix.omp $i $nThreads 5
   let i+=500
done
</pre>

The following example is OpenMP offload mode. Since it is using OpenMP rather than MKL offloading, MKL_MIC_ENABLE is disabled. In this mode the number of threads in use on the Phi is specified with PHI_OMP_NUM_THREADS. Keep in mind that this specific mode requires compiling on a Phi-enabled node and will fail on a front end.

Compile for OpenMP offload mode:
<pre>
$ icc -mkl -O3 -offload-build -Wno-unknown-pragmas -std=c99 -vec-report3 matrix.c -o matrix.off
</pre>

<pre>
#!/bin/bash
# ompoffload.sh
export MKL_MIC_ENABLE=0 # disable MKL offload since OpenMP offload is being used
export MIC_ENV_PREFIX=PHI
export PHI_OMP_NUM_THREADS=240 # saturates all threads on one phi (60 cores x 4 threads/core)
export PHI_KMP_AFFINITY="granularity=thread,balanced"

nThreads=16 # number of cpu cores
i=500
while [ $i -le 11000 ] # increases the size of the matrix on each run
do
   echo -n "OMP_OFFLOAD "
   echo -n "PHI_THREADS " $PHI_OMP_NUM_THREADS " "
   ./matrix.off $i $nThreads 5
   let i+=500
done
</pre>

The following example is native mode execution directly on the Phi itself.

<strong>You must use <kbd>nodes=N:ppn=16:mics=2</kbd> to enable native mode.</strong>

Compile to run natively on the Xeon Phi:
<pre>
$ icc -mkl -O3 -mmic -openmp -L  /opt/intel/lib/mic -Wno-unknown-pragmas -std=c99 -vec-report3 matrix.c -o matrix.mic -liomp5
</pre>

<pre>
#!/bin/bash
# native.sh
export MKL_MIC_ENABLE=0 # disable MKL offload since this is natively run
export KMP_AFFINITY="granularity=thread,balanced"

export LD_LIBRARY_PATH=/tmp # this path will change once native is supported

nThreads=240 # saturates all threads on one phi (60 cores x 4 threads/core)
i=500
while [ $i -le 11000 ] # increases the size of the matrix on each run
do
   echo -n "mic "
   ./matrix.mic $i $nThreads 5
   let i+=500
done
</pre>

Once the code is compiled for the Phi, you need to log into the Phi to run the code. You will want to be running in your scratch directory for best results.

<pre>
$ ssh mic0
$ ssh mic1
</pre>

It is also possible to compile OpenCL programs for the Xeon Phi. Depending on how the code was written, it may be possible to do a simple port to make a GPU-compatible program work on the Phi. In your OpenCL source code, make a copy of the source, and convert all instances of <kbd>CL_DEVICE_TYPE_GPU</kbd> to <kbd>CL_DEVICE_TYPE_ACCELERATOR</kbd>. This will allow the code to treat the Phi as a GPU, which works in some cases, however it is likely the code will require more modification to become Phi-compatible.

Applying the basic workflow to even a highly parallel algorithm is relatively easy but can achieve only a low level of parallelism and some improvement in performance.  Performance on the Phi is sensitive; a small change to the code can dramatically change performance.  To achieve the full potential of a Phi, you must fashion an algorithm to match the architecture of a Phi to reap the benefits.

For best performance, the input array or matrix must be sufficiently large to overcome the overhead in copying the input and output data to and from the Phi.

For more information about compiling on the Xeon Phi:
<ul>
 <li><a href="http://software.intel.com/mic-developer" target="_blank" rel="noopener">Intel&reg; Developer Zone: Intel&reg; Xeon Phi&trade; Coprocessor</a></li>
 <li><a href="http://www.drdobbs.com/parallel/programming-intels-xeon-phi-a-jumpstart/240144160" target="_blank" rel="noopener">Programming Intel's Xeon Phi: A Jumpstart Introduction</a></li>
 <li><a href="http://software.intel.com/en-us/articles/intel-xeon-phi-programming-environment" target="_blank" rel="noopener">Intel&reg; Xeon Phi&trade; Programming Environment</a></li>
 <li><a href="http://www.drdobbs.com/parallel/cuda-vs-phi-phi-programming-for-cuda-dev/240144545" target="_blank" rel="noopener">CUDA vs. Phi: Phi Programming for CUDA Developers</a></li>
</ul>
