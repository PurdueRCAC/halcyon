---
title: Managing Packages with Pip
tags:
 - slurm
 - wholenode
 - sharednode
 - workbench
---

##Managing Packages with Pip

Pip is a Pythom package manager. Many Python package documentation provide `pip` instructions that result in permission errors because by default `pip` will install in a system-wide location and fail. 
<pre>
Exception:
Traceback (most recent call last):
... ... stack trace ... ...
OSError: [Errno 13] Permission denied: '/apps/cent7/anaconda/5.1.0-py27/lib/python2.7/site-packages/mpi4py-3.0.1.dist-info'
</pre>

If you encounter this error, it means that you cannot modify the global Python installation. We recommend installing Python packages in a conda environment. Detailed instructions for installing packages with <kbd>pip</kbd> can be found in our [Python package installation page](../packages).

Below we list some other useful <kbd>pip</kbd> commands.

- Search for a package in PyPI channels:
<pre>
$ pip search packageName
</pre>
- Check which packages are installed globally:
<pre>
$ pip list
</pre>
- Check which packages you have personally installed:
<pre>
$ pip list --user
</pre>
- Snapshot installed packages:
<pre>
$ pip freeze > requirements.txt
</pre>
- You can install packages from a snapshot inside a new conda environment. Make sure to load the appropriate conda environment first.
<pre>
$ pip install -r requirements.txt
</pre>

For more information about Python:
<ul>
 <li><a href="http://www.python.org/" target="_blank" rel="noopener">The Python Programming Language - Official Website</a></li>
 <li><a href="https://store.continuum.io/cshop/anaconda/" target="_blank" rel="noopener">Anaconda Python Distribution - Official Website</a></li>
 <li><a href="https://conda.io/docs/user-guide/" target="_blank" rel="noopener">Conda User Guide</a></li>
<ul>
