---
title: Environment Management with the Module Command
tags:
 - linuxcluster
 - linuxclusteritar
---

# Provided Applications

The ${resource.name} cluster provides a number of software packages to users of the system via the `module` command.

# Environment Management with the Module Command

ITaP uses the <kbd>module</kbd> command as the preferred method to manage your processing environment.  With this command, you may load applications and compilers along with their libraries and paths.  Modules are packages which you load and unload as needed.

Please use the <kbd>module</kbd> command and do not manually configure your environment, as ITaP staff may make changes to the specifics of various packages.  If you use the <kbd>module</kbd> command to manage your environment, these changes will not be noticeable.

# Hierarchy

Many modules have dependencies on other modules. For example, a particular openmpi module requires a specific version of the Intel compiler to be loaded. Often, these dependencies are not clear for users of the module, and there are many modules which may conflict. Arranging modules in an hierarchical fashion makes this dependency clear. This arrangement also helps make the software stack easy to understand - your view of the modules will not be cluttered with a bunch of conflicting packages.

Your default module view on ${resource.name} will include a set of compilers and the set of basic software that has no dependencies (such as Matlab and Fluent). To make software available that depends on a compiler, you must first load the compiler, and then software which depends on it becomes available to you. In this way, all software you see when doing "module avail" is completely compatible with each other.

# Using the Hierarchy

Your default module view on ${resource.name} will include a set of compilers, and the set of basic software that has no dependencies (such as Matlab and Fluent).

To see what modules are available on this system by default:

<pre>$ module avail</pre> 

To see which versions of a specific compiler are available on this system:

<pre>
$ module avail gcc
$ module avail intel
</pre>

To continue further into the hierarchy of modules, you will need to choose a compiler. As an example, if you are planning on using the Intel compiler you will first want to `load` the Intel compiler:

<pre>
$ module load intel
</pre>

With `intel` loaded, you can repeat the `avail` command and at the bottom of the output you will see the a section of additional software that the intel module provides:

<pre>
$ module avail
</pre>

Several of these new packages also provide additional software packages, such as MPI libraries. You can repeat the last two steps with one of the MPI packages such as `openmpi` and you will have a few more software packages available to you.

If you are looking for a specific software package and do not see it in your default view, the module command provides a search function for searching the entire hierarchy tree of modules without need for you to manually `load` and `avail` on every module.
{::if resource.name != Weber}
To search for a software package:

<pre>
$ module spider openmpi
----------------------------------------------------------------------------
  openmpi:
----------------------------------------------------------------------------
     Versions:
        openmpi/1.10.1
        openmpi/2.1.0
</pre>

This will search for the `openmpi` software package. If you do not specify a specific version of the package, you will be given a list of versions available on the system. Select the version you wish to use and `spider` that to see how to access the module:

<pre>
$ module spider openmpi/2.1.0
...
  You will need to load one of the set of module(s) below before the "openmpi/2.1.0" module is available to load.

      gcc/4.8.5
      gcc/5.2.0
      gcc/6.3.0
      intel/16.0.1.150
      intel/17.0.1.132
      intel/18.0.1.163
...
</pre>

The output of this command will instruct you that you can load the this module directly, or in case of the above example, that you will need to first load a module or two. With the information provide with this command, you can now construct a `load` command to load a version of OpenMPI into your environment:

<pre>
$ module load intel/18.0.1.163 openmpi/2.1.0
</pre>
{::/}

{::if resource.name!=Weber}
Some user communities may maintain copies of their domain software for others to use. For example, the Purdue Bioinformatics Core provides a wide set of bioinformatcs software for use by any user of ITaP clusters via the `bioinfo` module. The `spider` command will also search this repository of modules. If it finds a software package available in the bioinfo module repository, the `spider` command will instruct you to load the bioinfo module first.
{::/}

# Load / Unload a Module

All modules consist of both a name and a version number.  When loading a module, you may use only the name to load the default version, or you may specify which version you wish to load.

For each cluster, ITaP makes a recommendation regarding the set of compiler, math library, and MPI library for parallel code.  To load the recommended set:

<pre>$ module load rcac</pre>

To verify what you loaded:
<pre>$ module list</pre> 

To load the default version of a specific compiler, choose one of the following commands:
<pre>
$ module load gcc
$ module load intel
</pre> 
{::if resource.name != Weber}
To load a specific version of a compiler, include the version number:

<pre>$ module load intel/18.0.1.163</pre> 
{::/}
<strong>When running a job, you must use the job submission file to load on the compute node(s) any relevant modules.  Loading modules on the front end before submitting your job makes the software available to your session on the front-end, but not to your job submission script environment.  You must load the necessary modules in your job submission script.</strong>

To unload a compiler or software package you loaded previously:
<pre>
$ module unload gcc
$ module unload intel
$ module unload matlab
</pre> 

To unload all currently loaded modules and reset your environment:

<pre>module purge</pre>

# Show Module Details

To learn more about what a module does to your environment, you may use the <kbd>module show</kbd> command. 
{::if resource.name != Weber}
Here is an example showing what loading the default Matlab does to the processing environment:

<pre>
----------------------------------------------------------------------------
   /opt/modulefiles/core/matlab/R2017a.lua:
----------------------------------------------------------------------------
whatis("invoke MATLAB Release R2017a")
setenv("MATLAB","/apps/cent7/matlab/R2017a")
setenv("MLROOT","/apps/cent7/matlab/R2017a")
setenv("ARCH","glnxa64")
append_path("PATH","/apps/cent7/matlab/R2017a/bin/glnxa64")
append_path("PATH","/apps/cent7/matlab/R2017a/bin")
append_path("LD_LIBRARY_PATH","/apps/cent7/matlab/R2017a/runtime/glnxa64")
append_path("LD_LIBRARY_PATH","/apps/cent7/matlab/R2017a/bin/glnxa64")
</pre>
{::/}
