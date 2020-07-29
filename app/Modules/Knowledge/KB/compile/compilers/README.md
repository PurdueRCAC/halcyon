---
title: Provided Compilers
---

# Provided Compilers on ${resource.name}

Compilers are available on ${resource.name} for Fortran, C, and C++.  Compiler sets from Intel and GNU are installed. 

{::if resource.name != Weber}
A full list of compiler versions installed on ${resource.name} is available in the <a href="/software/?c=27">software catalog</a>. More detailed documentation on each compiler set available on ${resource.name} follows.

On ${resource.name}, ITaP recommends the following set of compiler and libraries for building code:
{::/}
<ul>
{::if resource.name != Weber}
	<li>${resource.compiler}</li>
{::else}
	<li>Intel</li>
{::/}
	<li>${resource.mathlib}</li>
{::if resource.mesglib != null}
	<li>${resource.mesglib} (impi)</li>
{::/}
</ul>

To load the recommended set:

<pre>
$ module load rcac
$ module list
</pre>

More information about using these compilers:
