---
title: GNU Compilers
tags:
 - linuxcluster
 - linuxclusteritar
---

# GNU Compilers

The official name of the GNU compilers is "GNU Compiler Collection" or "GCC".  To discover which versions are available:

<pre>$ module avail gcc</pre>

Choose an appropriate GCC module and load it.  For example:

<pre>$ module load gcc</pre>

<strong>An older version of the GNU compiler will be in your path by default.  Do NOT use this version.  Instead, load a newer version using the command <kbd>module load gcc</kbd>.</strong>

Here are some examples for the GNU compilers:

| Language    | Serial Program {::if resource.mesglib != null}| MPI Program {::/}| OpenMP Program |
| ----------- | -------------- | ----------- | -------------- |
| Fortran77   | <pre>$ gfortran myprogram.f -o myprogram</pre> {::if resource.mesglib != null}| <pre>$ mpif77 myprogram.f -o myprogram</pre> {::/}| <pre>$ gfortran -fopenmp myprogram.f -o myprogram</pre> |
| Fortran90   | <pre>$ gfortran myprogram.f90 -o myprogram</pre> {::if resource.mesglib != null}| <pre>$ mpif90 myprogram.f90 -o myprogram</pre> {::/}| <pre>$ gfortran -fopenmp myprogram.f90 -o myprogram</pre> |
| Fortran95   | <pre>$ gfortran myprogram.f95 -o myprogram</pre> {::if resource.mesglib != null}| <pre>$ mpif90 myprogram.f95 -o myprogram</pre> {::/}| <pre>$ gfortran -fopenmp myprogram.f95 -o myprogram</pre> |
| C	          | <pre>$ gcc myprogram.c -o myprogram</pre> {::if resource.mesglib != null}| <pre>$ mpicc myprogram.c -o myprogram</pre> {::/}| <pre>$ gcc -fopenmp myprogram.c -o myprogram</pre> |
| C++         | <pre>$ g++ myprogram.cpp -o myprogram</pre> {::if resource.mesglib != null}| <pre>$ mpiCC myprogram.cpp -o myprogram</pre> {::/}| <pre>$ g++ -fopenmp myprogram.cpp -o myprogram</pre> |

More information on compiler options appear in the official man pages, which are accessible with the <kbd>man</kbd> command after loading the appropriate compiler module.

For more documentation on the GCC compilers:

<ul>
 <li><a href="http://gcc.gnu.org/onlinedocs/">http://gcc.gnu.org/onlinedocs/</a></li>
</ul>
