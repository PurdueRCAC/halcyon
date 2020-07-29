---
title: Managing Environments with Conda
tags:
 - slurm
 - wholenode
 - sharednode
 - workbench
---
###Managing Environments with Conda

Conda is a package manager in Anaconda that allows you to create and manage multiple environments where you can pick and choose which packages you want to use. To use Conda you must load an Anaconda module:
<pre>
$ module load anaconda
</pre>
Many packages are pre-installed in the global environment. To see these packages:
<pre>
$ conda list
</pre>
To create your own custom environment:
<pre>
$ conda create --name MyEnvName python=2.7 FirstPackageName SecondPackageName -y
</pre>
The <kbd>--name</kbd> option specifies that the environment created will be named MyEnvName. You can include as many packages as you require separated by a space.  Including the <kbd>-y</kbd> option lets you skip the prompt to install the package.  By default environments are created and stored in the $HOME/.conda directory. 

To create an environment at a custom location:
<pre>
$ conda create --prefix=$HOME/MyEnvName python=2.7 PackageName -y
</pre>

To see a list of your environments:
<pre>
$ conda env list
</pre>
To remove unwanted environments:
<pre>
$ conda remove --name MyEnvName --all
</pre>
{::if resource.name != Weber}
To add packages to your environment:
<pre>
$ conda install --name MyEnvName PackageNames
</pre>
{::/}
To remove a package from an environment:
<pre>
$ conda remove --name MyEnvName PackageName
</pre>

Installing packages when creating your environment, instead of one at a time, will help you avoid dependency issues.

To activate or deactivate an environment you have created:
<pre>
$ source activate MyEnvName
$ source deactivate MyEnvName
</pre>

If you created your conda environment at a custom location using <kbd>--prefix</kbd> option, then you can activate or deactivate it using the full path.
<pre>
$ source activate $HOME/MyEnvName
$ source deactivate $HOME/MyEnvName
</pre>

To use a custom environment inside a job you must load the module and activate the environment inside your job submission script.  Add the following lines to your submission script:
<pre>
module load anaconda
source activate MyEnvName
</pre>
For more information about Python:
<ul>
 <li><a href="http://www.python.org/" target="_blank" rel="noopener">The Python Programming Language - Official Website</a></li>
 <li><a href="https://store.continuum.io/cshop/anaconda/" target="_blank" rel="noopener">Anaconda Python Distribution - Official Website</a></li>
 <li><a href="https://conda.io/docs/user-guide/" target="_blank" rel="noopener">Conda User Guide</a></li>
<ul>
