---
title: Compiling Hybrid Programs
tags:
 - mpi
 - mpislurm
---

# Compiling Hybrid Programs

A hybrid program combines both MPI and shared-memory to take advantage of compute clusters with multi-core compute nodes. Libraries for OpenMPI and Intel MPI (IMPI) and compilers which include OpenMP for C, C++, and Fortran are available.



<div class="inrows-wide">
<table class="inrows-wide">
<caption>Hybrid programs require including header files:</caption>
<thead>
 <tr>
  <th scope="col">Language</th>
  <th scope="col">Header Files</th>
 </tr>
</thead>
<tbody>
 <tr>
 <td>Fortran 77</td>
 <td><pre>INCLUDE 'omp_lib.h'
INCLUDE 'mpif.h'
</pre></td>
 </tr>
 <tr>
 <td>Fortran 90</td>
 <td><pre>use omp_lib
INCLUDE 'mpif.h'
</pre></td>
 </tr>
 <tr>
 <td>Fortran 95</td>
 <td><pre>use omp_lib
INCLUDE 'mpif.h'
</pre></td>
 </tr>
 <tr>
 <td>C</td>
 <td><pre>#include &lt;mpi.h&gt;
#include &lt;omp.h&gt;
</pre></td>
 </tr>
 <tr>
 <td>C++</td>
 <td><pre>#include &lt;mpi.h&gt;
#include &lt;omp.h&gt;
</pre></td>
 </tr>
</tbody>
</table>
</div>

A few examples illustrate hybrid programs with task parallelism of OpenMP:

<ul>
 <li><a href="/knowledge/downloads/compile/src/hybrid_hello.f" target="_blank"><kbd>hybrid_hello.f</kbd></a></li>
 <li><a href="/knowledge/downloads/compile/src/hybrid_hello.f90" target="_blank"><kbd>hybrid_hello.f90</kbd></a></li>
 <li><a href="/knowledge/downloads/compile/src/hybrid_hello.f95" target="_blank"><kbd>hybrid_hello.f95</kbd></a></li>
 <li><a href="/knowledge/downloads/compile/src/hybrid_hello.c" target="_blank"><kbd>hybrid_hello.c</kbd></a></li>
 <li><a href="/knowledge/downloads/compile/src/hybrid_hello.cpp" target="_blank"><kbd>hybrid_hello.cpp</kbd></a></li>
</ul>

<p>This example illustrates a hybrid program with loop-level (data) parallelism of OpenMP:</p>
<ul>
 <li><a href="/knowledge/downloads/compile/src/hybrid_loop.c" target="_blank"><kbd>hybrid_loop.c</kbd></a></li>
</ul>

To see the available MPI libraries:

<pre>
$ module avail impi
$ module avail openmpi
</pre> 



<div class="inrows-wide">
<table class="inrows-wide">
<caption>The following table illustrates how to compile your hybrid (MPI/OpenMP) program.  Any compiler flags accepted by Intel ifort/icc compilers are compatible with their respective MPI compiler.</caption>
<thead>
 <tr>
  <th scope="col">Language</th>
  <th scope="col">Intel MPI</th>
  <th scope="col">OpenMPI or Intel MPI (IMPI) with Intel Compiler</th>
 </tr>
</thead>
<tbody>
 <tr>
  <td>Fortran 77</td>
  <td><pre>$ mpiifort -openmp myprogram.f -o myprogram</pre></td>
  <td><pre>$ mpif77 -openmp myprogram.f -o myprogram</pre></td>
 </tr>
 <tr>
  <td>Fortran 90</td>
  <td><pre>$ mpiifort -openmp myprogram.f90 -o myprogram</pre></td>
  <td><pre>$ mpif90 -openmp myprogram.f90 -o myprogram</pre></td>
 </tr>
 <tr>
  <td>Fortran 95</td>
  <td><pre>$ mpiifort -openmp myprogram.f90 -o myprogram</pre></td>
  <td><pre>$ mpif90 -openmp myprogram.f90 -o myprogram</pre></td>
 </tr>
 <tr>
  <td>C</td>
  <td><pre>$ mpiicc -openmp myprogram.c -o myprogram</pre></td>
  <td><pre>$ mpicc -openmp myprogram.c -o myprogram</pre></td>
 </tr>
 <tr>
  <td>C++</td>
  <td><pre>$ mpiicpc -openmp myprogram.C -o myprogram</pre></td>
  <td><pre>$ mpiCC -openmp myprogram.C -o myprogram</pre></td>
 </tr>
 <tr>
  <th scope="col">Language</th>
  <th scope="col">OpenMPI or Intel MPI (IMPI) with GNU Compiler</th>
  <th scope="col"></th>
 </tr>
 <tr>
  <td>Fortran 77</td>
  <td><pre>$ mpif77 -fopenmp myprogram.f -o myprogram</pre></td>
  <td></td>
 </tr>
 <tr>
  <td>Fortran 90</td>
  <td><pre>$ mpif90 -fopenmp myprogram.f90 -o myprogram</pre></td>
  <td></td>
 </tr>
 <tr>
  <td>Fortran 95</td>
  <td><pre>$ mpif90 -fopenmp myprogram.f95 -o myprogram</pre></td>
  <td></td>
 </tr>
 <tr>
  <td>C</td>
  <td><pre>$ mpicc -fopenmp myprogram.c -o myprogram</pre></td>
  <td></td>
 </tr>
 <tr>
  <td>C++</td>
  <td><pre>$ mpiCC -fopenmp myprogram.C -o myprogram</pre></td>
  <td></td>
 </tr>
</tbody>
</table>
</div>

The Intel and GNU compilers will not output anything for a successful compilation. Also, the Intel compiler does not recognize the suffix ".f95".
