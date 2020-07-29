---
title: Using Jupyter Hub
tags:
 - jupyter
---
###What is Jupyter Hub
JupyterHub, a multi-user Hub, spawns, manages, and proxies multiple instances of the single-user Jupyter notebook server. JupyterHub can be used to serve notebooks to a class of students, a corporate data science group, or a scientific research group.

Jupyter is an acronym meaning Julia, Python and R.  The application was originally developed for use with these languages but now supports many more.  Jupyter stores your project in a notebook.  It is called a notebook because it is not just a block of code but rather a collection of information that relate to a project. The way you organize your notebook can explain processes and steps taken as well as highlight results. Notebooks provide a variety of formatting options while downloading so you can share the project appropriately for the situation. In addition, Jupyter can compile and run code, as well as save its output, making it an ideal workspace for many types of projects.

Jupyter Hub is currently available [here](https://notebook.${resource.frontend}.rcac.purdue.edu/hub/login) or under the url https://notebook.${resource.frontend}.rcac.purdue.edu.

###Getting Started
When you are logging to Jupyter Hub on one of the clusters you need to use your career account credentials. After, you will see the contents of your home directory in a file explorer. To start a new notebook click the "New" dropdown menu at the right-top and select one of the kernels available. Bash, R or Python.

![New dropdown menu on Jupyter GUI](/knowledge/downloads/jupyter/images/jupyter-kernels.png)

###Create your own environment
You can create your own environment in a kernel using a conda environment. Whatever environment you have created using conda can become in a Kernel ready to use in Jupyter Hub, just following some steps in the terminal or from the conda tab in the Jupyter Hub dashboard.

Below are listed the steps needed to create the environment for Jupyter from the terminal.

1. Load the anaconda module or use your own local installation.

   `$ module load anaconda/5.0.0-py36`

2. Create your own Conda env with the following packages.

   `$ conda create -n <your-env-name> python=x.x ipython ipykernel <more-needed-packages>`

3. Activate your environment.

   `$ source activate <your-env-name>`

4. Install the new Kernel.

   `$ ipython kernel install --user --name <env-name> --display-name "Python (My Own Kernel)"`

   The --name value is used by Jupyter internally. These commands will overwrite any existing kernel with the same name. --display-name is what you see in the notebook menus.

5. Go to your Jupyter dashboard and reload the page, you will see your own Kernel when you create a new Notebook. If you want to change the Kernel in the current Notebook, just go to the Kernel tab and select it from the "Change Kernel" option.

If you want to create the environment from the Dashboard, just go to the conda tab and create a new one with one of the available kernels, it will take some minutes while all base packages are being installed, after the new environment shows up in the list you can just select the libraries you want from the box under the list.

<img src="/knowledge/downloads/jupyter/images/jupyter-conda_tab.png" alt="Conda tab on Jupyter GUI" style="width: 100%;"/>

![Create new environment from Jupyter GUI](/knowledge/downloads/jupyter/images/jupyter-create_env.png)

Additionally, You can change the environment you are using at any time by clicking the "Kernel" dropdown menu and selecting "Change kernel".

![Change kernel button on Jupyter GUI](/knowledge/downloads/jupyter/images/jupyter-change_kernel.png)

**If you want to install a new kernel different from Python (e.g. R or Bash), please refer to the links at the end.**

To run code in a cell, select the cell and click the "run cell" icon on the toolbar.

![Run cell button on Jupyter GUI](/knowledge/downloads/jupyter/images/run_cell.png)

To add descriptions or other plain text change the cell to markdown format.  Any standard markdown tags will apply after you click the "run cell" tool.

![Format cell button on Jupyter GUI](/knowledge/downloads/jupyter/images/format_cell.png)

Below is a simple example of a notebook created following the steps outlined above.

![Example Jupyter Notebook](/knowledge/downloads/jupyter/images/sine.png)

For more information about Jupyter Hub, kernels and example notebooks:
<ul>
 <li><a href="http://jupyter.org/" target="_blank">Project Jupyter Home</a></li>
 <li><a href="http://github.com/jupyter/jupyter/wiki/A-gallery-of-interesting-Jupyter-Notebooks" target="_blank" rel="noopener">A gallery of interesting Jupyter Notebooks</a></li>
 <li><a href="https://zonca.github.io/2015/10/use-own-python-in-jupyterhub.html" target="_blank" rel="noopener">Use your own Python installation (kernel) in Jupyterhub</a></li>
 <li><a href="http://slhogle.github.io/2017/bash_jupyter_notebook/" target="_blank" rel="noopener">Installing the bash kernel for Jupyter notebook</a></li>
 <li><a href="https://irkernel.github.io/installation/" target="_blank" rel="noopener">Installing R kernel</a></li>
<ul>
