---
title: Tensorflow
tags:
 - slurm
 - wholenode
 - sharednode
 - internal
---

# Tensorflow on ${resource.name}

## Tensorflow Modules

ITaP provides a set of stable tensorflow builds on ${resource.name}. At present, tensorflow is part of the <a href="mltoolkit">ML-Toolkit</a> packages. You must load one of the <kbd>learning</kbd> modules before you can load the tensorflow module. We recommend getting an <a href="/knowledge/${resource.hostname}/run/examples/pbs/interactive" target="_bank">interactive job</a> for running Tensorflow.

{::if resource.nodegpus > 0}
- First, load a desired <kbd>learning</kbd> module:
<pre>
$ module load learning/conda-5.1.0-py36-gpu
</pre>
- You can find all available <kbd>learning</kbd> modules using the <kbd>module spider</kbd> command:
<pre>
$ module spider learning
</pre>
- To list available tensorflow modules:
<pre>
$ module spider ml-toolkit-gpu/tensorflow
</pre>
- To show default tensorflow module:
<pre>
$ module show ml-toolkit-gpu/tensorflow
</pre>
- To load default tensorflow module:
<pre>
$ module load ml-toolkit-gpu/tensorflow
</pre>
- To test that tensorflow is available:
<pre>
$ python -c "import tensorflow as tf"
</pre>
- **Important: ** The tensorflow modules previosuly available on Research Computing systems, such as <kbd>tensorflow/1.2.0_py27-gpu</kbd> and <kbd>tensorflow/1.2.0_py35-gpu</kbd>, are deprecated and will not work with the <kbd>ml-toolkit-gpu</kbd> modules. Please update your job scripts to use the <kbd>ml-toolkit-gpu/tensorflow</kbd> module.
- To run tensorflow on GPUs, you must submit a job. Please see instructions on how to get an interactive job <a href="/knowledge/${resource.hostname}/run/examples/pbs/interactive" target="_blank">here</a>.
{::else}
- First, load a desired <kbd>learning</kbd> module:
<pre>
$ module load learning/conda-5.1.0-py36-cpu
</pre>
- To list available tensorflow modules:
<pre>
$ module spider ml-toolkit-cpu/tensorflow
</pre>
- To show default tensorflow module:
<pre>
$ module show ml-toolkit-cpu/tensorflow
</pre>
- To load default tensorflow module:
<pre>
$ module load ml-toolkit-cpu/tensorflow
</pre>
- To test that tensorflow is available:
<pre>
$ python -c "import tensorflow as tf"
</pre>
- **Important: ** The tensorflow modules previosuly available on Research Computing systems, such as <kbd>tensorflow/1.2.0_py27-cpu</kbd> and <kbd>tensorflow/1.2.0_py35-cpu</kbd>, are deprecated and will not work with the <kbd>ml-toolkit-cpu</kbd> modules. Please update your job scripts to use the <kbd>ml-toolkit-cpu/tensorflow</kbd> module.
{::/}

## Install

ITaP recommends downloading and installing Tensorflow in user's home directory using <a href="../python/packages" target="_blank">anaconda environments</a>. Installing Tensorflow in your home directory has the advantage that it can be upgraded to newer versions easily. Therefore, researchers will have access to the latest libraries when needed.

- We recommend getting an <a href="/knowledge/${resource.hostname}/run/examples/pbs/interactive" target="_bank">interactive job</a> for installing and running Tensorflow.
- First load the necessary modules and define which tensorflow version to install:
<pre>
$ module purge
$ module load anaconda/5.1.0-py36
{::if resource.nodegpus > 0}
$ module load cuda/8.0.61 cudnn/cuda-8.0_6.0
$ module list
$ export TF_BINARY_URL='https://storage.googleapis.com/tensorflow/linux/gpu/tensorflow_gpu-1.4.0-cp36-cp36m-linux_x86_64.whl'

{::else}
$ module list
$ export TF_BINARY_URL='https://storage.googleapis.com/tensorflow/linux/cpu/tensorflow-1.12.0-cp36-cp36m-linux_x86_64.whl'

{::/}
</pre>
- Create an anaconda environment using <kbd>rcac-conda-env</kbd>. The script also prints a list of modules that should be loaded to use the custom environment, please note down these module names.
<pre>
$ rcac-conda-env create -n my_tf_env
</pre>
- Activate the anaconda environment.
<pre>
$ module load use.own
$ module load conda-env/my_tf_env-py3.6.4
</pre>
- Now install Tensorflow binaries in your home directory:
<pre>
$ pip install --ignore-installed --upgrade $TF_BINARY_URL
</pre>
- Wait for installation to finish.
- If the installation finished successfully, you can now proceed with the examples below. If not, please look at <a href="https://www.tensorflow.org/install/install_linux#common_installation_problems" target="_blank">common installation problems</a> and how to resolve them.

## Testing the installation

- Check that you have the anaconda module and your custom environment loaded using the command <kbd>module list</kbd>. Otherwise, load the necessary modules:
<pre>
$ module load anaconda/5.1.0-py36
{::if resource.nodegpus > 0}
$ module load cuda/8.0.61
$ module load cudnn/cuda-8.0_6.0
$ module load use.own
$ module load conda-env/my_tf_env-py3.6.4
{::else}
$ module load use.own
$ module load conda-env/my_tf_env-py3.6.4
{::/}
</pre>
- Save the following code as <kbd>tensor_hello.py</kbd>
<pre>
# filename: tensor_hello.py
import tensorflow as tf
hello = tf.constant('Hello, TensorFlow!')
sess = tf.Session()
print(sess.run(hello))
</pre>
- Run the example
	<pre>$ python tensor_hello.py</pre>
- This will produce an output like the following:
<pre>
< ... tensorflow build related information ... >
< ... hardware information ... >
Hello, TensorFlow!
</pre>

{::if resource.nodegpus > 0}
## Test GPU run

- For this we shall use the matrix multiplication example from <a href="https://www.tensorflow.org/tutorials/using_gpu" target="_blank" rel="noopener">Tensorflow documentation</a>.
<pre>
# filename: matrixmult.py
import tensorflow as tf
# Creates a graph.
a = tf.constant([1.0, 2.0, 3.0, 4.0, 5.0, 6.0], shape=[2, 3], name='a')
b = tf.constant([1.0, 2.0, 3.0, 4.0, 5.0, 6.0], shape=[3, 2], name='b')
c = tf.matmul(a, b)
# Creates a session with log_device_placement set to True.
sess = tf.Session(config=tf.ConfigProto(log_device_placement=True))
# Runs the op.
print(sess.run(c))
</pre>

- Run the example
	<pre>$ python matrixmult.py</pre>
- This will produce an output like:
<pre>
< ... tensorflow build related information ... >
< ... hardware information ... >
MatMul: (MatMul): /job:localhost/replica:0/task:0/gpu:0
2017-06-22 18:14:57.630336: I tensorflow/core/common_runtime/simple_placer.cc:847] MatMul: (MatMul)/job:localhost/replica:0/task:0/gpu:0
b: (Const): /job:localhost/replica:0/task:0/gpu:0
2017-06-22 18:14:57.630365: I tensorflow/core/common_runtime/simple_placer.cc:847] b: (Const)/job:localhost/replica:0/task:0/gpu:0
a: (Const): /job:localhost/replica:0/task:0/gpu:0
2017-06-22 18:14:57.630400: I tensorflow/core/common_runtime/simple_placer.cc:847] a: (Const)/job:localhost/replica:0/task:0/gpu:0
[[ 22.  28.]
 [ 49.  64.]]
</pre>
- For more details, please refer to Tensorflow <a href="https://www.tensorflow.org/get_started/" target="_blank" rel="noopener">User Guide</a>.

{::/}

## Tensorboard

- You can visualize data from a Tensorflow session using Tensorboard. For this, you need to save your session summary as described <a href="https://www.tensorflow.org/get_started/summaries_and_tensorboard" target="_blank" rel="noopener">here</a>.
- Launch Tensorboard:
<pre>$ python -m tensorboard.main --logdir=/path/to/session/logs</pre>
- When Tensorboard is launched successfully, it will give you the URL for accessing Tensorboard.
<pre>
<... build related warnings ...> 
TensorBoard 0.4.0 at http://${resource.hostname}-a000.rcac.purdue.edu:6006
</pre>
- Follow the printed URL to visualize your model.
- Please note that due to firewall rules, the Tensorboard URL may only be accessible from ${resource.name} nodes. If you cannot access the URL directly, you can use Firefox browser in <a href='https://desktop.${resource.hostname}.rcac.purdue.edu' target="_blank" rel="noopener">Thinlinc</a>. 
- For more details, please refer to Tensorboard <a href="https://www.tensorflow.org/get_started/summaries_and_tensorboard" target="_blank" rel="noopener">User Guide</a>.

{::if user.staff == 1}
# Staff Notes

{::/}

