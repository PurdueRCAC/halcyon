---
title: Octave
tags:
 - wholenode
 - sharednode
---
# Octave

GNU Octave is a high-level, interpreted, programming language for numerical computations. Octave is a structured language (similar to C) and mostly compatible with MATLAB. You may use Octave to avoid the need for a MATLAB license, both during development and as a deployed application. By doing so, you may be able to run your application on more systems or more easily distribute it to others.

This section illustrates how to submit a small Octave job to a PBS queue. This Octave example computes the inverse of a matrix.

Prepare an Octave script file with an appropriate filename, here named <kbd>myjob.m</kbd>:
<pre>
% FILENAME:  myjob.m

% Invert matrix A.
A = [1 2 3; 4 5 6; 7 8 0]
inv(A)

quit
</pre> 

Prepare a job submission file with an appropriate filename, here named <kbd>myjob.sub</kbd>:

<pre>
#!/bin/sh -l
# FILENAME:  myjob.sub

module load octave
cd $PBS_O_WORKDIR

unset DISPLAY

# Use the -q option to suppress startup messages.
# octave -q &lt; myjob.m
octave &lt; myjob.m
</pre> 

The command <kbd>octave myjob.m</kbd> (without the redirection) also works in the preceding script.

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

View results in the file for all standard output, <kbd>myjob.sub.omyjobid</kbd>:
<pre>
A =

   1   2   3
   4   5   6
   7   8   0

ans =

  -1.77778   0.88889  -0.11111
   1.55556  -0.77778   0.22222
  -0.11111   0.22222  -0.11111
</pre> 

Any output written to standard error will appear in <kbd>myjob.sub.emyjobid</kbd>.

For more information about Octave:
<ul>
 <li><a href="http://www.octave.org" target="_blank" rel="noopener">GNU Octave Website</a></li>
 <li><a href="http://www.gnu.org/software/octave/doc/interpreter/" target="_blank" rel="noopener">Octave Online Documentation</a></li>
 <li><a href="http://wiki.octave.org/FAQ" target="_blank" rel="noopener">Porting Programs from MATLAB to Octave</a></li>
</ul>
