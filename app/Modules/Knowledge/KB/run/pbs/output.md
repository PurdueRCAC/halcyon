---
title: Checking Job Output
tags:
 - wholenode
 - sharednode
---

# Checking Job Output

Once a job is [submitted](../submit), has run to completion, and is no longer in [`qstat`](../status) output your job is complete and ready to have its output examined.

PBS catches output written to standard output and standard error - what would be printed to your screen if you ran your program interactively. Unless you specfied otherwise, PBS will put the output in the directory where you submitted the job.

Standard out will appear in a file whose extension begins with the letter "o", for example `myjobsubmissionfile.o1234`, where "1234" represents the PBS job ID.  Errors that occurred during the job run and written to standard error will appear in your directory in a file whose extension begins with the letter "e", for example <kbd>myjobsubmissionfile.e1234</kbd>.  

If your program writes its own output files, those files will be created as defined by the program. This may be in the directory where the program was run, or may be defined in a configuration or input file. You will need to check the documentation for your program for more details.

### Redirecting Job Output

It is possible to redirect job output to somewhere other than the default location with the `-e` and `-o` directives:

<pre>
#! /bin/sh -l
#PBS -o /home/${user.username}/joboutput/myjob.out
#PBS -e /home/${user.username}/joboutput/myjob.out

# This job prints "Hello World" to output and exits
echo "Hello World"
</pre>



