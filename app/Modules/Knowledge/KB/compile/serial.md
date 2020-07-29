---
title: Compiling Serial Programs
tags:
 - linuxcluster
 - linuxclusteritar
---

A serial program is a single process which executes as a sequential stream of instructions on one processor core. Compilers capable of serial programming are available for C, C++, and versions of Fortran.

Here are a few sample serial programs:

- [serial_hello.f](/knowledge/downloads/compile/src/serial_hello.f)
- [serial_hello.f90](/knowledge/downloads/compile/src/serial_hello.f90)
- [serial_hello.f95](/knowledge/downloads/compile/src/serial_hello.f95)
- [serial_hello.c](/knowledge/downloads/compile/src/serial_hello.c)
- [serial_hello.cpp](/knowledge/downloads/compile/src/serial_hello.cpp)

 To load a compiler, enter one of the following:
 
<pre>
$ module load intel
$ module load gcc
</pre>



<div class="inrows-wide">
<table class="inrows-wide">
<caption>The following table illustrates how to compile your serial program:</caption>
<thead>
 <tr>
  <th scope="col">Language</th>
  <th scope="col">Intel Compiler</th>
  <th scope="colgroup" colspan="2">GNU Compiler</th>
 </tr>
</thead>
<tbody>
 <tr>
 <td>Fortran 77</td>
 <td><pre>$ ifort myprogram.f -o myprogram</pre></td>
 <td><pre>$ gfortran myprogram.f -o myprogram</pre></td>
 <td></td>
 </tr>
 <tr>
 <td>Fortran 90</td>
 <td><pre>$ ifort myprogram.f90 -o myprogram</pre></td>
 <td><pre>$ gfortran myprogram.f90 -o myprogram</pre></td>
 <td></td>
 </tr>
 <tr>
 <td>Fortran 95</td>
 <td><pre>$ ifort myprogram.f90 -o myprogram</pre></td>
 <td><pre>$ gfortran myprogram.f95 -o myprogram</pre></td>
 <td></td>
 </tr>
 <tr>
 <td>C</td>
 <td><pre>$ icc myprogram.c -o myprogram</pre></td>
 <td><pre>$ gcc myprogram.c -o myprogram</pre></td>
 <td><pre>$ pgcc myprogram.c -o myprogram</pre></td>
 </tr>
 <tr>
 <td>C++</td>
 <td><pre>$ icc myprogram.cpp -o myprogram</pre></td>
 <td><pre>$ g++ myprogram.cpp -o myprogram</pre></td>
 <td><pre>$ pgCC myprogram.cpp -o myprogram</pre></td>
 </tr>
</tbody>
</table>
</div>

The Intel and GNU compilers will not output anything for a successful compilation. Also, the Intel compiler does not recognize the suffix ".f95".
