---
title: Compiling GPU Programs
tags:
 - gpu
---

# Compiling GPU Programs

The ${resource.name} cluster nodes contain ${resource.nodegpus} {::if resource.nodegpus == 1}GPU{::else}GPUs{::/} that support <em>CUDA</em> and <em>OpenCL</em>. See the [detailed hardware overview](../../../../overview) for the specifics on the GPUs in ${resource.name}. This section focuses on using CUDA. 

A simple CUDA program has a basic workflow:

<ul>
 <li>Initialize an array on the host (CPU).</li>
 <li>Copy array from host memory to GPU memory.</li>
 <li>Apply an operation to array on GPU.</li>
 <li>Copy array from GPU memory to host memory.</li>
</ul>

Here is a sample CUDA program:

<ul>
 <li><a href="/knowledge/downloads/compile/src/gpu_hello.cu" target="_blank"><kbd>gpu_hello.cu</kbd></a></li>
</ul>

Both front-ends and GPU-enabled compute nodes have the CUDA tools and libraries available to compile CUDA programs. To compile a CUDA program, load CUDA, and use <kbd>nvcc</kbd> to compile the program:

<pre>
$ module load cuda
$ nvcc gpu_hello.cu -o gpu_hello
./gpu_hello
</pre>

The example illustrates only how to copy an array between a CPU and its GPU but does not perform a serious computation.

The following program times three square matrix multiplications on a CPU and on the global and shared memory of a GPU:

<ul>
 <li><a href="/knowledge/downloads/compile/src/mm.cu" target="_blank"><kbd>mm.cu</kbd></a></li>
</ul>


<pre>
$ module load cuda
$ nvcc mm.cu -o mm
$ ./mm 0
                                                            speedup
                                                            -------
Elapsed time in CPU:                    7810.1 milliseconds
Elapsed time in GPU (global memory):      19.8 milliseconds  393.9
Elapsed time in GPU (shared memory):       9.2 milliseconds  846.8
</pre> 

For best performance, the input array or matrix must be sufficiently large to overcome the overhead in copying the input and output data to and from the GPU.

For more information about NVIDIA, CUDA, and GPUs:

<ul>
 <li><a href="http://developer.download.nvidia.com/compute/DevZone/docs/html/C/doc/CUDA_C_Best_Practices_Guide.pdf" target="_blank" rel="noopener">NVIDIA CUDA C Best Practices Guide</a></li>
 <li><a href="http://developer.download.nvidia.com/compute/DevZone/docs/html/C/doc/CUDA_C_Programming_Guide.pdf" target="_blank" rel="noopener">NVIDIA CUDA C Programming Guide</a></li>
 <li><a href="http://www.nvidia.com/object/cuda_home_new.html" target="_blank" rel="noopener">Introducing CUDA</a></li>
 <li><a href="http://developer.nvidia.com/nvidia-gpu-computing-documentation" target="_blank" rel="noopener">NVIDIA GPU Computing Documentation</a></li>
 <li><a href="http://developer.download.nvidia.com/compute/DevZone/docs/html/C/doc/nvcc.pdf" target="_blank" rel="noopener">NVIDIA The CUDA Compiler Driver NVCC</a></li>
 <li><a href="http://developer.download.nvidia.com/compute/DevZone/docs/html/C/doc/cuda-gdb.pdf">NVIDIA CUDA-GDB Debugger</a></li>
 <li><a href="http://developer.nvidia.com/gpu-computing-webinars">NVIDIA GPU Computing Webinars</a></li>
 <li><a href="http://www.nvidia.com/page/home.html" target="_blank" rel="noopener">NVIDIA</a></li>
 <li><a href="http://gpgpu.org/" target="_blank" rel="noopener">General-Purpose Computation on Graphics Hardware</a></li>
</ul>
