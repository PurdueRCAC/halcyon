---
title: Intel MKL Library
tags:
 - linuxcluster
 - linuxclusteritar
---

# Intel MKL Library

Intel Math Kernel Library (MKL) contains ScaLAPACK, LAPACK, Sparse Solver, BLAS, Sparse BLAS, CBLAS, GMP, FFTs, DFTs, VSL, VML, and Interval Arithmetic routines.  MKL resides in the directory stored in the environment variable <kbd>MKL_HOME</kbd>, after loading a version of the Intel compiler with <kbd>module</kbd>. 

By using <kbd>module load</kbd> to load an Intel compiler your environment will have several variables set up to help link applications with MKL. Here are some example combinations of simplified linking options:

<pre>
$ module load intel
$ echo $LINK_LAPACK
-L${MKL_HOME}/lib/intel64 -lmkl_intel_lp64 -lmkl_intel_thread -lmkl_core -liomp5 -lpthread

$ echo $LINK_LAPACK95
-L${MKL_HOME}/lib/intel64 -lmkl_lapack95_lp64 -lmkl_blas95_lp64 -lmkl_intel_lp64 -lmkl_intel_thread -lmkl_core -liomp5 -lpthread
</pre>

ITaP recommends you use the provided variables to define MKL linking options in your compiling procedures. The Intel compiler modules also provide two other environment variables, <kbd>LINK_LAPACK_STATIC</kbd> and <kbd>LINK_LAPACK95_STATIC</kbd> that you may use if you need to link MKL statically.

ITaP recommends that you use dynamic linking of libguide.  If so, define LD_LIBRARY_PATH such that you are using the correct version of libguide at run time.  If you use static linking of libguide, then:

<ul>
 <li>If you use the Intel compilers, link in the libguide version that comes with the compiler (use the -openmp option).</li>
 <li>If you do not use the Intel compilers, link in the libguide version that comes with the Intel MKL above.</li>
</ul>

Here are some more documentation from other sources on the Intel MKL:

<ul>
 <li><a href="http://software.intel.com/en-us/articles/intel-math-kernel-library-documentation" target="_blank" rel="noopener">Intel MKL Documentation</a></li>
</ul>
