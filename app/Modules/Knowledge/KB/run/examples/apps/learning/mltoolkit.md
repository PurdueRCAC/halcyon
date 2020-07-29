---
title: ML-Toolkit
tags:
 - slurm
 - wholenode
 - sharednode
 - internal
---

ITaP maintains a set of popular machine learning (ML) applications on ${resource.name}. These are Anaconda/Python based distribution of the respective applications. Currently, applications are supported for two major Python versions (2.7 and 3.6). Detailed instructions for searching and using the installed ML applications are presented below.

**Important:** You must load one of the <kbd>learning</kbd> modules described below before loading the ML applications.


# Instructions for using ML packages
## Prerequisite
**Make sure your Python environment is clean.** Python is very sensitive about packages installed in your local pip folder or in your Conda environments. It is always safer to start with a clean environment. The steps below archive all your existing python packages to backup directories reducing chances of conflict.
<pre>
$ mv ~/.conda ~/.conda.bak
$ mv ~/.local ~/.local.bak
$ mv ~/.cache ~/.cache.bak  

</pre>

## Find installed ML applications

To search or load a machine learning application, you must first load one of the <kbd>learning</kbd> modules. The <kbd>learning</kbd> module loads the prerequisites (such as anaconda{::if resource.nodegpus > 0} and cudnn{::/}) and makes ML applications visible to the user.

**Step 1.** Find and load a preferred <kbd>learning</kbd> module.

{::if resource.nodegpus > 0}
There are four <kbd>learning</kbd> modules available on ${resource.hostname}, each corresponding to a specific Python version and whether the ML applications have GPU support or not. In the example below, we want to use the <kbd>learning</kbd> module for Python 3.6 that has GPU support.
<pre>
$ module spider learning

----------------------------------------------------------------------------
  learning:
----------------------------------------------------------------------------
     Versions:
        learning/conda-5.1.0-py27-cpu
        learning/conda-5.1.0-py27-gpu
        learning/conda-5.1.0-py36-cpu
        learning/conda-5.1.0-py36-gpu

.........
$ module load learning/conda-5.1.0-py36-gpu

</pre>
{::else}
There are two <kbd>learning</kbd> modules available on ${resource.hostname}, each corresponding to a specific Python version. In the example below, we want to use the <kbd>learning</kbd> module for Python 3.6. 
<pre>
$ module spider learning

----------------------------------------------------------------------------
  learning:
----------------------------------------------------------------------------
     Versions:
        learning/conda-5.1.0-py27-cpu
        learning/conda-5.1.0-py36-cpu

.........
$ module load learning/conda-5.1.0-py36-cpu

</pre>
{::/}

**Step 2.** Find a machine learning application.

You can now use the <kbd>module spider</kbd> command to find installed applications. The following example searches for available PyTorch installations.
<pre>
$ module spider pytorch
{::if resource.nodegpus > 0}
---------------------------------------------------------------------------------
  ml-toolkit-gpu/pytorch: ml-toolkit-gpu/pytorch/1.0.0
---------------------------------------------------------------------------------

    This module can be loaded directly: module load ml-toolkit-gpu/pytorch/1.0.0
{::else}
---------------------------------------------------------------------------------
  ml-toolkit-cpu/pytorch: ml-toolkit-cpu/pytorch/0.4.0
---------------------------------------------------------------------------------

    This module can be loaded directly: module load ml-toolkit-cpu/pytorch/0.4.0
{::/}

</pre>

**Step 3.** List all machine learning applications.

{::if resource.nodegpus > 0}
Note that the ML packages are installed under the common application name <kbd>ml-toolkit-X</kbd>, where <kbd>X</kbd> can be <kbd>cpu</kbd> or <kbd>gpu</kbd>. To list all GPU versions of machine learning packages installed on ${resource.hostname}, run the command:
<pre>
$ module spider ml-toolkit-gpu

</pre>
Currently, <kbd>ml-toolkit-gpu</kbd> includes 9 popular ML packages listed below.
<pre>
ml-toolkit-gpu/caffe/1.0.0
ml-toolkit-gpu/cntk/2.6
ml-toolkit-gpu/gym/0.10.9
ml-toolkit-gpu/keras/2.2.4
ml-toolkit-gpu/opencv/3.4.3
ml-toolkit-gpu/pytorch/1.0.0
ml-toolkit-gpu/tensorflow/1.12.0
ml-toolkit-gpu/tflearn/0.3.2
ml-toolkit-gpu/theano/1.0.3

</pre>
{::else}   
Note that the ML packages are installed under the common application name <kbd>ml-toolkit-cpu</kbd>. To list all machine learning packages installed on ${resource.hostname}, run the command:
<pre>
$ module spider ml-toolkit-cpu

</pre>
Currently, <kbd>ml-toolkit-cpu</kbd> includes 9 popular ML packages listed below.
<pre>
ml-toolkit-cpu/caffe/1.0.0
ml-toolkit-cpu/cntk/2.3
ml-toolkit-cpu/gym/0.10.5
ml-toolkit-cpu/keras/2.1.5
ml-toolkit-cpu/opencv/3.4.1
ml-toolkit-cpu/pytorch/0.4.0
ml-toolkit-cpu/tensorflow/1.4.0
ml-toolkit-cpu/tflearn/0.3.2
ml-toolkit-cpu/theano/1.0.2

</pre>
{::/}

## Load and use the ML applications

**Step 4.** After loading a preferred <kbd>learning</kbd> module in Step 1, you can now load the desired ML applications in your environment. In the following example, we load the OpenCV and PyTorch modules.
{::if resource.nodegpus > 0}
<pre>
$ module load ml-toolkit-gpu/opencv/3.4.3
$ module load ml-toolkit-gpu/pytorch/1.0.0

</pre>
{::else}
<pre>
$ module load ml-toolkit-cpu/opencv/3.4.1
$ module load ml-toolkit-cpu/pytorch/0.4.0

</pre>
{::/}

**Step 5.** You can list which ML applications are loaded in your environment using the command
<pre>
$ module list

</pre>

## Verify application import
**Step 6.** The next step is to check that you can actually use the desired ML application. You can do this by running the <kbd>import</kbd> command in Python.
<pre>
$ python -c "import torch; print(torch.__version__)"

</pre>
If the import operation succeeded, then you can run your own ML codes. Few ML applications (such as tensorflow) print diagnostic warnings while loading--this is the expected behavior.

If the import failed with an error, please see the troubleshooting information below.

**Step 7.** To load a different set of applications, unload the previously loaded applications and load the new applications. The example below loads Tensorflow and Keras instead of PyTorch and OpenCV.
{::if resource.nodegpus > 0}
<pre>
$ module unload ml-toolkit-gpu/opencv/3.4.3
$ module unload ml-toolkit-gpu/pytorch/1.0.0
$ module load ml-toolkit-gpu/tensorflow/1.12.0
$ module load ml-toolkit-gpu/keras/2.2.4

</pre>
{::else}
<pre>
$ module unload ml-toolkit-cpu/opencv/3.4.1
$ module unload ml-toolkit-cpu/pytorch/0.4.0
$ module load ml-toolkit-cpu/tensorflow/1.4.0
$ module load ml-toolkit-cpu/keras/2.1.5

</pre>
{::/}

## Troubleshooting

ML applications depend on a wide range of Python packages and mixing multiple versions of these packages can lead to error. The following guidelines will assist you in identifying the cause of the problem.

- Check that you are using the correct version of Python with the command <kbd>python --version</kbd>. This should match the Python version in the loaded anaconda module.
- Make sure that your Python environment is clean. Follow the instructions in "Prerequisites" section above.
- Start from a clean environment. Either start a new terminal session or unload all the modules: <kbd>module purge</kbd>. Then load the desired modules following Steps 1-4.
- Verify that PYTHONPATH does not point to undesired packages. Run the following command to print PYTHONPATH: <kbd>echo $PYTHONPATH</kbd>
{::if resource.nodegpus > 0}
- If you don't see GPU devices in your code, make sure that you are using the <kbd>ml-toolkit-gpu/</kbd> modules and not using their cpu versions.
- ML applications often have dependency on specific versions of Cuda and CuDNN libraries. Make sure that you have loaded the required versions using the command: <kbd>module list</kbd>
{::/}
- Note that Caffe has a conflicting version of PyQt5. So, if you want to use Spyder (or any GUI application that uses PyQt), then you should unload the caffe module.
- Use Google search to your advantage. Copy the error message in Google and check probable causes.

More examples showing how to use <kbd>ml-toolkit</kbd> modules in a batch job are presented in <a href="tensor_batch" target="_top">this</a> guide.

# Installing ML applications

If the ML application you are trying to use is not in the list of supported applications or if you need a newer version of an installed application, you can install it in your home directory. We recommend using anaconda environments to <a href="../python/packages" target="_blank">install and manage Python packages</a>. Please follow the steps carefully, otherwise you may end up with a faulty installation. The example below shows how to install PyTorch.
{::if resource.nodegpus > 0}
  1.0.1
{::else}
  0.4.1
{::/}
(a newer version) in your home directory.

**Step 1:** Unload all modules and start with a clean environment.
<pre>
$ module purge

</pre>

**Step 2:** Load the anaconda module with desired Python version.
<pre>
$ module load anaconda/5.1.0-py36

</pre>

{::if resource.nodegpus > 0}
**Step 2A:** If the ML application requires Cuda and CuDNN, load the appropriate modules.
<pre>
$ module load cuda
$ module load cudnn

</pre>
{::/}

**Step 3:** Create a custom anaconda environment. Make sure the python version matches the Python version in the anaconda module.
<pre>
$ rcac-conda-env create -n env_name_here

</pre>

**Step 4:** Activate the anaconda environment by loading the modules displayed at the end of step 3.
<pre>
$ module load use.own
$ module load conda-env/env_name_here-py3.6.4

</pre>

**Step 5:** Now install the desired ML application. You can install multiple Python packages at this step using either <kbd>conda</kbd> or <kbd>pip</kbd>.
{::if resource.nodegpus > 0}
<pre>
$ conda install -c pytorch pytorch=1.0.1

</pre>
{::else}
<pre>
$ conda install -c pytorch pytorch-cpu=0.4.1

</pre>
{::/}
If the installation succeeded, you can now use the installed application.

Note that loading the modules generated by <kbd>rcac-conda-env</kbd> has different behavior than <kbd>conda create env_name_here</kbd> followed by <kbd>source activate env_name_here</kbd>. After running <kbd>source activate</kbd>, you may not be able to access any Python packages in anaconda or ml-toolkit modules. Therefore, using <kbd>rcac-conda-env</kbd> is the preferred way of using your custom installations.

## Troubleshooting

In most situations, dependencies among Python modules lead to error. If you cannot use a Python package after installing it, please follow the steps below to find a workaround.

- Unload all the modules.
<pre>
$ module purge
</pre>
- Clean up PYTHONPATH.
<pre>
$ unset PYTHONPATH
</pre>
- Next load the modules, e.g., anaconda and your custom environment.
<pre>
$ module load anaconda/5.1.0-py36
$ module load use.own
$ module load conda-env/env_name_here-py3.6.4
</pre>
{::if resource.nodegpus > 0}
- For GPU-enabled applications, you may also need to load the corresponding <kbd>cuda/</kbd> and <kbd>cudnn/</kbd> modules.
{::/}
- Now try running your code again.
- Few applications only run on specific versions of Python (e.g. Python 3.6). Please check the documentation of your application if that is the case.
- If you have installed a newer version of an <kbd>ml-toolkit</kbd> package (e.g., a newer version of PyTorch or Tensorflow), make sure that the <kbd>ml-toolkit</kbd> modules are NOT loaded. In general, we recommend that you don't mix <kbd>ml-toolkit</kbd> modules with your custom installations.
{::if resource.nodegpus > 0}
- GPU-enabled ML applictions often have dependencies on specific versions of Cuda and CuDNN. For example, Tensorflow version 1.5.0 and higher needs Cuda 9. Please check the application documentation about such dependencies.
{::/} 

{::if user.staff == 1}
# Staff Notes

{::/}

