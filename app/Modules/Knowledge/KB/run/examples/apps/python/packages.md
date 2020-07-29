---
title: Installing Packages
tags:
 - slurm
 - wholenode
 - sharednode
 - workbench
---

#Installing Packages

ITaP recommends installing Python packages in an Anaconda environment. One key advantage of Anaconda is that it allows users to install unrelated packages in separate self-contained environments. Individual packages can later be reinstalled or updated without impacting others. If you are unfamiliar with Conda environments, please check our [Conda User Guide](../conda).

To facilitate the process of creating and using Conda environments, we support a script (<kbd>rcac-conda-env</kbd>) that generates a module file for an environment, as well as an optional Jupyter kernel to use this environment in a JupyterHub notebook.

You must load one of the <kbd>anaconda</kbd> modules in order to use this script.
<pre>$ module load anaconda/5.1.0-py36</pre>

Step-by-step instructions for installing custom Python packages are presented below.


## Step 1: Create a conda environment
Users can use the <kbd>rcac-conda-env</kbd> script to create an empty conda environment. This script needs either a name or a path for the desired environment. After the environment is created, it generates a module file for using it in future. Please note that <kbd>rcac-conda-env</kbd> is different from the official <kbd>conda-env</kbd> script and supports a limited set of subcommands. Detailed instructions for using <kbd>rcac-conda-env</kbd> can be found with the command <kbd>rcac-conda-env --help</kbd>.

- **Example 1:** Create a conda environment named <kbd>mypackages</kbd> in user's home directory.
<pre>$ rcac-conda-env create -n mypackages</pre>

- **Example 2:** Create a conda environment named <kbd>mypackages</kbd> at a custom location.
<pre>$ rcac-conda-env create -p /depot/mylab/apps/mypackages</pre>
Please follow the on-screen instructions while the environment is being created. After finishing, the script will print the instructions to use this environment.
<pre>
... ... ...
Preparing transaction: ...working... done
Verifying transaction: ...working... done
Executing transaction: ...working... done
+------------------------------------------------------+
| To use this environment, load the following modules: |
|       module load use.own                            |
|       module load conda-env/mypackages-py3.6.4       |
+------------------------------------------------------+
Your environment "mypackages" was created successfully.
</pre>

Note down the module names, as you will need to load these modules every time you want to use this environment. You may also want to add the <kbd>module load</kbd> lines in your jobscript, if it depends on custom Python packages.

By default, module files are generated in your <kbd>$HOME/privatemodules</kbd> directory. The location of module files can be customized by specifying the <kbd>-m /path/to/modules</kbd> option to <kbd>rcac-conda-env</kbd>.

- **Example 3:** Create a conda environment named <kbd>labpackages</kbd> in your group's Data Depot space and place the module file at a shared location for the group to use.
<pre>$ rcac-conda-env create -p /depot/mylab/apps/labpackages -m /depot/mylab/etc/modules
... ... ...
Preparing transaction: ...working... done
Verifying transaction: ...working... done
Executing transaction: ...working... done
+-------------------------------------------------------+
| To use this environment, load the following modules:  |
|       module use /depot/mylab/etc/modules             |
|       module load conda-env/labpackages-py3.6.4       |
+-------------------------------------------------------+
Your environment "labpackages" was created successfully.
</pre>

If you used a custom module file location, you need to run the <kbd>module use</kbd> command as printed by the script.

By default, only the environment and a module file are created (no Jupyter kernel). If you plan to use your environment in a JupyterHub notebook, you need to append a <kbd>--jupyter</kbd> flag to the above commands.

- **Example 4:** Create a Jupyter-enabled conda environment named <kbd>labpackages</kbd> in your group's Data Depot space and place the module file at a shared location for the group to use.
<pre>$ rcac-conda-env create -p /depot/mylab/apps/labpackages -m /depot/mylab/etc/modules --jupyter
... ... ...
Jupyter kernel created: "Python (My labpackages Kernel)"
... ... ...
Your environment "labpackages" was created successfully.
</pre>


## Step 2: Load the conda environment

- The following instructions assume that you have used <kbd>rcac-conda-env</kbd> script to create an environment named <kbd>mypackages</kbd> (Examples 1 or 2 above). If you used <kbd>conda create</kbd> instead, please use <kbd>conda activate mypackages</kbd>.
<pre>
$ module load use.own
$ module load conda-env/mypackages-py3.6.4
</pre>
Note that the <kbd>conda-env</kbd> module name includes the Python version that it supports (Python 3.6.4 in this example). This is same as the Python version in the <kbd>anaconda</kbd> module.

- If you used a custom module file location (Example 3 above), please use <kbd>module use</kbd> to load the <kbd>conda-env</kbd> module.
<pre>
$ module use /depot/mylab/etc/modules
$ module load conda-env/mypackages-py3.6.4
</pre>

{::if resource.name != Weber}
## Step 3: Install packages

Now you can install custom packages in the environment using either <kbd>conda install</kbd> or <kbd>pip install</kbd>.

### Installing with conda

- **Example 1:** Install OpenCV (open-source computer vision library) using conda.
<pre>$ conda install opencv</pre>

- **Example 2:** Install a specific version of OpenCV using conda.
<pre>$ conda install opencv=3.1.0</pre>

- **Example 3:** Install OpenCV from a specific anaconda channel.
<pre>$ conda install -c anaconda opencv</pre>

### Installing with pip

- **Example 4:** Install mpi4py using pip.
<pre>$ pip install mpi4py</pre>

- **Example 5:** Install a specific version of mpi4py using pip.
<pre>$ pip install mpi4py==3.0.1</pre>
Follow the on-screen instructions while the packages are being installed. If installation is successful, please proceed to the next section to test the packages.

**Note:** Do **NOT** run Pip with the <kbd>--user</kbd> argument, as that will install packages in a different location.
{::/}

{::if resource.name != Weber}
## Step 4: Test the installed packages
{::else}
## Step 3: Test the installed packages
{::/}

To use the installed Python packages, you must load the module for your conda environment. If you have not loaded the <kbd>conda-env</kbd> module, please do so following the instructions at the end of Step 1.
<pre>
$ module load use.own
$ module load conda-env/mypackages-py3.6.4
</pre>

- **Example 1:** Test that OpenCV is available.
<pre>
$ python -c "import cv2; print(cv2.__version__)"
</pre>
- **Example 2:** Test that mpi4py is available.
<pre>
$ python -c "import mpi4py; print(mpi4py.__version__)"
</pre>

If the commands finished without errors, then the installed packages can be used in your program.


## Additional capabilities of rcac-conda-env script

The <kbd>rcac-conda-env</kbd> tool is intended to facilitate creation of a minimal Anaconda environment, matching module file and optionally a Jupyter kernel.  Once created, the environment can then be accessed via familiar <kbd>module load</kbd> command, tuned and expanded as necessary.  Additionally, the script provides several auxilliary functions to help managing environments, module files and Jupyter kernels.

General usage for the tool adheres to the following pattern:
<pre>
$ rcac-conda-env help
$ rcac-conda-env &lt;subcommand&gt; &lt;required argument&gt; [optional arguments]
</pre>

where required arguments are one of
- <kbd>-n|--name ENV_NAME</kbd> (name of the environment)
- <kbd>-p|--prefix ENV_PATH</kbd> (location of the environment)

and optional arguments further modify behavior for specific actions (e.g. <kbd>-m</kbd> to specify alternative location for generated module file).

Given a required name or prefix for an environment, the <kbd>rcac-conda-env</kbd> script supports the following subcommands:
- <kbd>create</kbd> - to create a new environment, its corresponding module file and optional Jupyter kernel.
- <kbd>delete</kbd> - to delete existing environment along with its module file and Jupyter kernel.
- <kbd>module</kbd> - to generate just the module file for a given existing environment.
- <kbd>kernel</kbd> - to generate just the Jupyter kernel for a given existing environment (note that the environment has to be created with a <kbd>--jupyter</kbd> option).
- <kbd>help</kbd> - to display script usage help.

Using these subcommands, you can iteratively fine-tune your environments, module files and Jupyter kernels, as well as delete and re-create them with ease. Below we cover several commonly occuring scenarios.


### Generating module file for an existing environment

If you already have an existing configured Anaconda environment and want to generate a module file for it, follow appropriate examples from **Step 1** above, but use the <kbd>module</kbd> subcommand instead of the <kbd>create</kbd> one.  E.g.

<pre>$ rcac-conda-env module -n mypackages</pre>

and follow printed instructions on how to load this module.  With an optional <kbd>--jupyter</kbd> flag, a Jupyter kernel will also be generated.

Note that if you intend to proceed with a Jupyter kernel generation (via the <kbd>--jupyter</kbd> flag or a <kbd>kernel</kbd> subcommand later), you will have to ensure that your environment has <kbd>ipython</kbd> and <kbd>ipykernel</kbd> packages installed into it.  To avoid this and other related complications, we highly recommend making a fresh environment using a suitable <kbd>rcac-conda-env create .... --jupyter</kbd> commmand instead.



### Generating Jupyter kernel for an existing environment

If you already have an existing configured Anaconda environment and want to generate a Jupyter kernel file for it, you can use the <kbd>kernel</kbd> subcommand.  E.g.

<pre>$ rcac-conda-env kernel -n mypackages</pre>

This will add a <kbd>"Python (My mypackages Kernel)"</kbd> item to the dropdown list of available kernels upon your next login to the [JupyterHub](https://notebook.${resource.frontend}.rcac.purdue.edu/).

Note that generated Jupiter kernels are always personal (i.e. each user has to make their own, even for shared environments).  Note also that you (or the creator of the shared environment) will have to ensure that your environment has <kbd>ipython</kbd> and <kbd>ipykernel</kbd> packages installed into it.


### Managing and using shared Python environments

Here is a suggested workflow for a common group-shared Anaconda environment with Jupyter capabilities:

**The PI or lab software manager:**
  - Creates the environment and module file (once):
    <pre>
    $ module purge
    $ module load anaconda
    $ rcac-conda-env create -p /depot/mylab/apps/labpackages -m /depot/mylab/etc/modules --jupyter
    </pre>

  - Installs required Python packages into the environment (as many times as needed):
    <pre>
    $ module use /depot/mylab/etc/modules
    $ module load conda-env/labpackages-py3.6.4
    $ conda install  .......                       # all the necessary packages
    </pre>

**Lab members:**
  - Lab members can start using the environment in their command line scripts or batch jobs simply by loading the corresponding module:
    <pre>
    $ module use /depot/mylab/etc/modules
    $ module load conda-env/labpackages-py3.6.4
    $ python my_data_processing_script.py .....
    </pre>

  - To use the environment in Jupyter notebooks, each lab member will need to create his/her own Jupyter kernel (once). This is because Jupyter kernels are private to individuals, even for shared environments.
    <pre>
    $ module use /depot/mylab/etc/modules
    $ module load conda-env/labpackages-py3.6.4
    $ rcac-conda-env kernel -p /depot/mylab/apps/labpackages
    </pre>

A similar process can be devised for instructor-provided or individually-managed class software, etc.


## Troubleshooting

- Python packages often fail to install or run due to dependency with other packages. More specifically, if you previously installed packages in your home directory it is safer to clean those installations.
<pre>
$ mv ~/.local ~/.local.bak
$ mv ~/.cache ~/.cache.bak
</pre>
- Unload all the modules.
<pre>
$ module purge
</pre>
- Clean up PYTHONPATH.
<pre>
$ unset PYTHONPATH
</pre>
- Next load the modules (e.g. anaconda) that you need.
<pre>
$ module load anaconda/5.1.0-py36
$ module load use.own
$ module load conda-env/mypackages-py3.6.4
</pre>
- Now try running your code again.
- Few applications only run on specific versions of Python (e.g. Python 3.6). Please check the documentation of your application if that is the case.
