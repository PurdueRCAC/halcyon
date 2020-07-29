---
title: Tensorflow Batch Job
tags:
 - wholenode
 - sharednode
 - internal
---

# Running Tensorflow code in a batch job

Batch jobs allow us to automate model training without human intervention. They are also useful when you need to run a large number of simulations on the clusters. In the example below, we shall run the <kbd>tensor_hello.py</kbd> script in a batch job (refer to <a href="tensorflow">Tensorflow</a> guide to see the code). We consider two situations: in the first example, we use the <a href="mltoolkit">ML-Toolkit</a> modules to run tensorflow, while in the second example, we use our custom installation of tensorflow.

## Using Ml-Toolkit modules
Save the following code as <kbd>tensor_hello.sub</kbd> in the same directory where <kbd>tensor_hello.py</kbd> is located.
<pre>
# filename: tensor_hello.sub
{::if resource.nodegpus > 0}
#PBS -l nodes=1:ppn=1:gpus=1
{::else}
#PBS -l nodes=1:ppn=${resource.nodecores}
{::/}
#PBS -l walltime=00:05:00
#PBS -q ${resource.queue}
#PBS -N hello_tensor

cd $PBS_O_WORKDIR

module purge
{::if resource.nodegpus > 0}
module load learning/conda-5.1.0-py36-gpu
module load ml-toolkit-gpu/tensorflow
{::else}
module load learning/conda-5.1.0-py36-cpu
module load ml-toolkit-cpu/tensorflow
{::/}
module list

python tensor_hello.py

</pre>

## Using custom tensorflow installation
Save the following code as <kbd>tensor_hello.sub</kbd> in the same directory where <kbd>tensor_hello.py</kbd> is located.
<pre>
# filename: tensor_hello.sub
{::if resource.nodegpus > 0}
#PBS -l nodes=1:ppn=1:gpus=1
{::else}
#PBS -l nodes=1:ppn=${resource.nodecores}
{::/}
#PBS -l walltime=00:05:00
#PBS -q ${resource.queue}
#PBS -N hello_tensor

cd $PBS_O_WORKDIR

module purge
{::if resource.nodegpus > 0}
module load anaconda/5.1.0-py36
module load cuda/8.0.61 
module load cudnn/cuda-8.0_6.0
module load use.own
module load conda-env/my_tf_env-py3.6.4
{::else}
module load anaconda/5.1.0-py36
module load use.own
module load conda-env/my_tf_env-py3.6.4
{::/}
module list

echo $PYTHONPATH

python tensor_hello.py

</pre>

Now you can submit the batch job using the <kbd>qsub</kbd> command.
<pre>$ qsub tensor_hello.sub</pre>

Once the job finishes, you will find an output (<kbd>hello_tensor.oxxxx</kbd>) and an error (<kbd>hello_tensor.exxxx</kbd>) file. If tensorflow ran successfully, then the output file will contain the message shown below.
<pre>
Hello, TensorFlow!
</pre>

{::if user.staff == 1}
# Staff Notes

{::/}

