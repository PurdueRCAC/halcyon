---
title: Example: Create and Use Biopython Environment with Conda 
tags:
 - slurm
 - wholenode
 - sharednode
 - workbench
---
###Using conda to create an environment that uses the biopython package

To use Conda you must first load the anaconda module:
<pre>
$ module load anaconda
</pre>

Create an empty conda environment to install biopython:
<pre>
$ rcac-conda-env create -n biopython
</pre> 

Now activate the biopython environment:
<pre>
$ module load use.own
$ module load conda-env/biopython-py2.7.14
</pre>

Install the biopython packages in your environment:
<pre>
$ conda install --channel anaconda biopython -y
Fetching package metadata ..........
Solving package specifications .........
.......
Linking packages ...
[    COMPLETE    ]|################################################################
</pre>

The <kbd>--channel</kbd> option specifies that it searches the anaconda channel for the biopython package. The <kbd>-y</kbd> argument is optional and allows you to skip the installation prompt.  A list of packages will be displayed as they are installed.  

Remember to add the following lines to your job submission script to use the custom environment in your jobs:
<pre>
module load anaconda
module load use.own
module load conda-env/biopython-py2.7.14
</pre>

If you need further help or run into any issues with creating environments contact us at <a href="mailto:rcac-help@purdue.edu">rcac-help@purdue.edu</a> or drop by [Coffee Hour](/coffee) for in-person help.

For more information about Python:
<ul>
 <li><a href="http://www.python.org/" target="_blank" rel="noopener">The Python Programming Language - Official Website</a></li>
 <li><a href="https://store.continuum.io/cshop/anaconda/" target="_blank" rel="noopener">Anaconda Python Distribution - Official Website</a></li>
 <li><a href="https://conda.io/docs/user-guide/" target="_blank" rel="noopener">Conda User Guide</a></li>
<ul>
