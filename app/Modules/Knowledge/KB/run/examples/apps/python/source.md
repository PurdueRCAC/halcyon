---
title: Installing Packages from Source
tags:
 - slurm
 - wholenode
 - sharednode
 - workbench
---
###Installing Packages from Source

We maintain several [Anaconda](https://www.continuum.io/anaconda-overview) installations. Anaconda maintains numerous popular scientific Python libraries in a single installation. If you need a Python library not included with normal Python we recommend first checking Anaconda. For a list of modules currently installed in the Anaconda Python distribution:

<pre>$ module load anaconda
$ conda list
# packages in environment at /apps/cent7/anaconda/5.1.0-py27:
#
# Name                    Version                   Build  Channel
_ipyw_jlab_nb_ext_conf    0.1.0            py27h08a7f0c_0  
alabaster                 0.7.10           py27he5a193a_0  
anaconda                  5.1.0                    py27_2  
...
</pre>

If you see the library in the list, you can simply import it into your Python code after loading the Anaconda module.

If you do not find the package you need, you should be able to install the library in your own Anaconda customization. First try to install it with [Conda or Pip](../packages). If the package is not available from either Conda or Pip, you may be able to install it from source.

Use the following instructions as a guideline for installing packages from source. Make sure you have a download link to the software (usually it will be a `tar.gz` archive file). You will substitute it on the wget line below.

We also assume that you have already created an empty conda environment as described in our [Python package installation guide](../packages).
<pre>
$ mkdir ~/src
$ cd ~/src
$ wget http://path/to/source/tarball/app-1.0.tar.gz
$ tar xzvf app-1.0.tar.gz
$ cd app-1.0
$ module load anaconda
$ module load use.own
$ module load conda-env/mypackages-py2.7.14
$ python setup.py install
$ cd ~
$ python
>>> import app
>>> quit()
</pre>

The "import app" line should return without any output if installed successfully. You can then import the package in your python scripts.

If you need further help or run into any issues installing a library contact us at <a href="mailto:rcac-help@purdue.edu">rcac-help@purdue.edu</a> or drop by [Coffee Hour](/coffee) for in-person help.

For more information about Python:
<ul>
 <li><a href="http://www.python.org/" target="_blank" rel="noopener">The Python Programming Language - Official Website</a></li>
 <li><a href="https://store.continuum.io/cshop/anaconda/" target="_blank" rel="noopener">Anaconda Python Distribution - Official Website</a></li>
 <li><a href="https://conda.io/docs/user-guide/" target="_blank" rel="noopener">Conda User Guide</a></li>
<ul>
