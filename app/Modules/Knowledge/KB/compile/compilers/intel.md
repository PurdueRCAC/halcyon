---
title: Intel Compilers
tags:
 - linuxcluster
 - linuxclusteritar
---

# Intel Compilers

One or more versions of the Intel compiler are available on ${resource.name}.  To discover which ones:

<pre>$ module avail intel</pre>

Choose an appropriate Intel module and load it.  For example:

<pre>$ module load intel</pre>


<div class="inrows-wide">
<table class="inrows-wide">
<caption>Here are some examples for the Intel compilers:</caption>
<thead>
 <tr>
 <th scope="col">Language</th>
 <th scope="col">Serial Program</th>
{::if resource.mesglib != null}
 <th scope="col">MPI Program</th>
{::/}
 <th scope="col">OpenMP Program</th>
 </tr>
</thead>
<tbody>
 <tr>
 <td>Fortran77</td>
 <td>
<pre>$ ifort myprogram.f -o myprogram
</pre> </td>
{::if resource.mesglib != null}
 <td>
<pre>$ mpiifort myprogram.f -o myprogram
</pre> </td>
{::/}
 <td>
<pre>$ ifort -openmp myprogram.f -o myprogram
</pre> </td>
 </tr>
 <tr>
 <td>
 Fortran90
 </td>
 <td>
<pre>$ ifort myprogram.f90 -o myprogram
</pre> </td>
{::if resource.mesglib != null}
 <td>
<pre>$ mpiifort myprogram.f90 -o myprogram
</pre> </td>
{::/}
 <td>
<pre>$ ifort -openmp myprogram.f90 -o myprogram
</pre> </td>
 </tr>
 <tr>
 <td>
 Fortran95
 </td>
 <td>
 (same as Fortran 90)
 </td>
{::if resource.mesglib != null}
 <td>
 (same as Fortran 90)
 </td>
{::/}
 <td>
 (same as Fortran 90)
 </td>
 </tr>
 <tr>
 <td>
 C
 </td>
 <td>
<pre>$ icc myprogram.c -o myprogram
</pre> </td>
{::if resource.mesglib != null}
 <td>
<pre>$ mpiicc myprogram.c -o myprogram
</pre> </td>
{::/}
 <td>
<pre>$ icc -openmp myprogram.c -o myprogram
</pre> </td>
 </tr>
 <tr>
 <td>
 C++
 </td>
 <td>
<pre>$ icpc myprogram.cpp -o myprogram
</pre> </td>
{::if resource.mesglib != null}
 <td>
<pre>$ mpiicpc myprogram.cpp -o myprogram
</pre> </td>
{::/}
 <td>
<pre>$ icpc -openmp myprogram.cpp -o myprogram
</pre> </td>
 </tr>
</tbody>
</table>
</div>

More information on compiler options appear in the official man pages, which are accessible with the <kbd>man</kbd> command after loading the appropriate compiler module.

For more documentation on the Intel compilers:

<ul>
 <li><a href="http://software.intel.com/en-us/articles/intel-software-technical-documentation/" target="_blank" rel="noopener">Intel Software Documentation Library</a></li>
</ul>
