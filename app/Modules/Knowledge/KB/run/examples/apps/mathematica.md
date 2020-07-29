---
title: Mathematica
tags:
 - wholenode
 - sharednode
---
# Mathematica

Mathematica implements numeric and symbolic mathematics. This section illustrates how to submit a small Mathematica job to a PBS queue. This Mathematica example finds the three roots of a third-degree polynomial.

Prepare a Mathematica input file with an appropriate filename, here named <kbd>myjob.in</kbd>:

<pre>
(* FILENAME:  myjob.in *)

(* Find roots of a polynomial. *)
p=x^3+3*x^2+3*x+1
Solve[p==0]
Quit
</pre> 

Prepare a job submission file with an appropriate filename, here named <kbd>myjob.sub</kbd>:

<pre>
#!/bin/sh -l
# FILENAME:  myjob.sub

module load mathematica
cd $PBS_O_WORKDIR

math &lt; myjob.in
</pre>

Submit the job:

<pre>
{::if resource.qsub_needs_gpu == 1}
$ qsub -l nodes=1:ppn=1:gpus=1 myjob.sub
{::else}
$ qsub -l nodes=1:ppn=${resource.nodecores} myjob.sub
{::/}
</pre>

View job status:

<pre>
$ qstat -u ${user.username}
</pre>

View results in the file for all standard output, here named <kbd>myjob.sub.omyjobid</kbd>:
<pre>
Mathematica 5.2 for Linux x86 (64 bit)
Copyright 1988-2005 Wolfram Research, Inc.
 -- Terminal graphics initialized --

In[1]:=
In[2]:=
In[2]:=
In[3]:=
                     2    3
Out[3]= 1 + 3 x + 3 x  + x

In[4]:=
Out[4]= {{x -> -1}, {x -> -1}, {x -> -1}}

In[5]:=
</pre> 

View the standard error file, <kbd>myjob.sub.emyjobid</kbd>:
<pre>
rmdir: ./ligo/rengel/tasks: Directory not empty
rmdir: ./ligo/rengel: Directory not empty
rmdir: ./ligo: Directory not empty
</pre> 

For more information about Mathematica:
<ul>
 <li><a href="http://www.wolfram.com/products/mathematica/index.html" target="_blank" rel="noopener">Wolfram Research Website</a></li>
</ul>
