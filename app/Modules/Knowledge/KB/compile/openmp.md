---
title: Compiling OpenMP Programs
tags:
 - linuxcluster
 - linuxclusteritar
---

# Compiling OpenMP Programs

All compilers installed on ${resource.name} include OpenMP functionality for C, C++, and Fortran. An OpenMP program is a single process that takes advantage of a multi-core processor and its shared memory to achieve a form of parallel computing called multithreading. It distributes the work of a process over processor cores in a single compute node without the need for MPI communications.


<div class="inrows-wide">
<table class="inrows-wide">
<caption>OpenMP programs require including a header file:</caption>
<thead>
 <tr>
  <th scope="col">Language</th>
  <th scope="col">Header Files</th>
 </tr>
</thead>
<tbody>
 <tr>
 <td>Fortran 77</td>
 <td><pre>INCLUDE 'omp_lib.h'</pre></td>
 </tr>
 <tr>
 <td>Fortran 90</td>
 <td><pre>use omp_lib</pre></td>
 </tr>
 <tr>
 <td>Fortran 95</td>
 <td><pre>use omp_lib</pre></td>
 </tr>
 <tr>
 <td>C</td>
 <td><pre>#include &lt;omp.h&gt;</pre></td>
 </tr>
 <tr>
 <td>C++</td>
 <td><pre>#include &lt;omp.h&gt;</pre></td>
 </tr>
</tbody>
</table>
</div>

Sample programs illustrate task parallelism of OpenMP:

<ul>
 <li><a href="/knowledge/downloads/compile/src/omp_hello.f" target="_blank"><kbd>omp_hello.f</kbd></a></li>
 <li><a href="/knowledge/downloads/compile/src/omp_hello.f90" target="_blank"><kbd>omp_hello.f90</kbd></a></li>
 <li><a href="/knowledge/downloads/compile/src/omp_hello.f95" target="_blank"><kbd>omp_hello.f95</kbd></a></li>
 <li><a href="/knowledge/downloads/compile/src/omp_hello.c" target="_blank"><kbd>omp_hello.c</kbd></a></li>
 <li><a href="/knowledge/downloads/compile/src/omp_hello.cpp" target="_blank"><kbd>omp_hello.cpp</kbd></a></li>
</ul>

A sample program illustrates loop-level (data) parallelism of OpenMP:

<ul>
 <li><a href="/knowledge/downloads/compile/src/omp_loop.c" target="_blank"><kbd>omp_loop.c</kbd></a></li>
</ul>

To load a compiler, enter one of the following:

<pre>
$ module load intel
$ module load gcc
</pre>



<div class="inrows-wide">
<table class="inrows-wide">
<caption>The following table illustrates how to compile your shared-memory program. Any compiler flags accepted by ifort/icc compilers are compatible with OpenMP.</caption>
<thead>
 <tr>
  <th scope="col">Language</th>
  <th scope="col">Intel Compiler</th>
  <th scope="col">GNU Compiler</th>
 </tr>
</thead>
<tbody>
 <tr>
 <td>Fortran 77</td>
 <td><pre>$ ifort -openmp myprogram.f -o myprogram</pre></td>
 <td><pre>$ gfortran -fopenmp myprogram.f -o myprogram</pre></td>
 </tr>
 <tr>
 <td>Fortran 90</td>
 <td><pre>$ ifort -openmp myprogram.f90 -o myprogram</pre></td>
 <td><pre>$ gfortran -fopenmp myprogram.f90 -o myprogram</pre></td>
 </tr>
 <tr>
 <td>Fortran 95</td>
 <td><pre>$ ifort -openmp myprogram.f90 -o myprogram</pre></td>
 <td><pre>$ gfortran -fopenmp myprogram.f95 -o myprogram</pre></td>
 </tr>
 <tr>
 <td>C</td>
 <td><pre>$ icc -openmp myprogram.c -o myprogram</pre></td>
 <td><pre>$ gcc -fopenmp myprogram.c -o myprogram</pre></td>
 </tr>
 <tr>
 <td>C++</td>
 <td><pre>$ icc -openmp myprogram.cpp -o myprogram</pre></td>
 <td><pre>$ g++ -fopenmp myprogram.cpp -o myprogram</pre></td>
 </tr>
</tbody>
</table>
</div>

The Intel and GNU compilers will not output anything for a successful compilation. Also, the Intel compiler does not recognize the suffix ".f95".

Here is some more documentation from other sources on OpenMP:
<ul>
 <li><a href="http://www.openmp.org/" target="_blank" rel="noopener">OpenMP Home</a></li>
 <li><a href="http://www.compunity.org/" target="_blank" rel="noopener">Community of OpenMP Users</a></li>
 <li><a href="http://software.intel.com/en-us/articles/getting-started-with-openmp/" target="_blank" rel="noopener">Intel OpenMP</a></li>
 <li><a href="http://gcc.gnu.org/wiki/openmp" target="_blank" rel="noopener">GCC OpenMP</a></li>
</ul>
