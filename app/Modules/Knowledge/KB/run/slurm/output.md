---
title: Checking Job Output
tags:
 - slurm
---

# Checking Job Output

Once a job is [submitted](../submit), and has started,  it will write its standard output and standard error to files that you can read.

SLURM catches output written to standard output and standard error - what would be printed to your screen if you ran your program interactively. Unless you specfied otherwise, SLURM will put the output in the directory where you submitted the job in a file named `slurm-` followed by the `job id`, with the extension `out`.  For example `slurm-3509.out`.  Note that both stdout and stderr will be written into the same file, unless you specify otherwise.

If your program writes its own output files, those files will be created as defined by the program. This may be in the directory where the program was run, or may be defined in a configuration or input file. You will need to check the documentation for your program for more details.

### Redirecting Job Output

It is possible to redirect job output to somewhere other than the default location with the `--error` and `--output` directives:

<pre>
#! /bin/sh -l
#SBATCH --output=/home/${user.username}/joboutput/myjob.out
#SBATCH --error=/home/${user.username}/joboutput/myjob.out

# This job prints "Hello World" to output and exits
echo "Hello World"
</pre>



