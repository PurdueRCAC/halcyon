---
title: Compiling MPI Programs
tags:
 - mpi
 - mpislurm
---

# Compiling MPI Programs

OpenMPI and Intel MPI (IMPI) are implementations of the Message-Passing Interface (MPI) standard. Libraries for these MPI implementations and compilers for C, C++, and Fortran are available on all clusters.  A full list of MPI library versions installed on ${resource.name} is available in the <a href="/software/?c=28">software catalog</a>.



<div class="inrows-wide">
<table class="inrows-wide">
<caption>MPI programs require including a header file:</caption>
<thead>
 <tr>
  <th scope="col">Language</th>
  <th scope="col">Header Files</th>
 </tr>
</thead>
<tbody>
 <tr>
 <td>Fortran 77</td>
 <td><pre>INCLUDE 'mpif.h'</pre></td>
 </tr>
 <tr>
 <td>Fortran 90</td>
 <td><pre>INCLUDE 'mpif.h'</pre></td>
 </tr>
 <tr>
 <td>Fortran 95</td>
 <td><pre>INCLUDE 'mpif.h'</pre></td>
 </tr>
 <tr>
 <td>C</td>
 <td><pre>#include &lt;mpi.h&gt;</pre></td>
 </tr>
 <tr>
 <td>C++</td>
 <td><pre>#include &lt;mpi.h&gt;</pre></td>
 </tr>
</tbody>
</table>
</div>

Here are a few sample programs using MPI:

<ul>
 <li><a href="/knowledge/downloads/compile/src/mpi_hello.f" target="_blank"><kbd>mpi_hello.f</kbd></a></li>
 <li><a href="/knowledge/downloads/compile/src/mpi_hello.f90" target="_blank"><kbd>mpi_hello.f90</kbd></a></li>
 <li><a href="/knowledge/downloads/compile/src/mpi_hello.f95" target="_blank"><kbd>mpi_hello.f95</kbd></a></li>	
 <li><a href="/knowledge/downloads/compile/src/mpi_hello.c" target="_blank"><kbd>mpi_hello.c</kbd></a></li>
 <li><a href="/knowledge/downloads/compile/src/mpi_hello.cpp" target="_blank"><kbd>mpi_hello.cpp</kbd></a></li>
</ul>

To see the available MPI libraries:

<pre>
$ module avail openmpi 
$ module avail impi
</pre>


<div class="inrows-wide">
<table class="inrows-wide">
<caption>The following table illustrates how to compile your MPI program.  Any compiler flags accepted by Intel ifort/icc compilers are compatible with their respective MPI compiler.</caption>
<thead>
 <tr>
  <th scope="col">Language</th>
  <th scope="col">Intel MPI</th>
  <th scope="col">OpenMPI or Intel MPI (IMPI)</th>
 </tr>
</thead>
<tbody>
 <tr>
 <td>Fortran 77</td>
 <td><pre>$ mpiifort program.f -o program</pre></td>
 <td><pre>$ mpif77 program.f -o program</pre></td>
 </tr>
 <tr>
 <td>Fortran 90</td>
 <td><pre>$ mpiifort program.f90 -o program</pre></td>
 <td><pre>$ mpif90 program.f90 -o program</pre></td>
 </tr>
 <tr>
 <td>Fortran 95</td>
 <td><pre>$ mpiifort program.f95 -o program</pre></td>
 <td><pre>$ mpif90 program.f95 -o program</pre></td>
 </tr>
 <tr>
 <td>C</td>
 <td><pre>$ mpiicc program.c -o program</pre></td>
 <td><pre>$ mpicc program.c -o program</pre></td>
 </tr>
 <tr>
 <td>C++</td>
 <td><pre>$ mpiicpc program.C -o program</pre></td>
 <td><pre>$ mpiCC program.C -o program</pre></td>
 </tr>
</tbody>
</table>
</div>

The Intel and GNU compilers will not output anything for a successful compilation. Also, the Intel compiler does not recognize the suffix ".f95".

Here is some more documentation from other sources on the MPI libraries:

<ul>
 <li><a href="http://www.mpi-forum.org/" target="_blank" rel="noopener">Message Passing Interface Forum</a></li>
 <li><a href="http://www.open-mpi.org/" target="_blank" rel="noopener">Open MPI Home</a></li>
 <li><a href="http://www.open-mpi.org/doc/" target="_blank" rel="noopener">Open MPI Documentation</a></li>
</ul>
