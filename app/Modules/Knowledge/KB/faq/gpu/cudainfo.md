---
title: What is the cuda version and GPU architecture in ${resource.name}?
tags:
 - gpu
---

### What is the cuda version and GPU architecture in ${resource.name}?

- Each ${resource.name} node has 2 x ${resource.gpuname} GPUs.
- The cuda version installed in ${resource.name} is ${resource.cudaver}. You can find available cuda versions using the command:
	<pre>${resource.hostname}-fe00:~$ module spider cuda</pre>
- GPUs in ${resource.name} can support Nvidia compute capabilities upto ${resource.computecap}. This is typically specified with the <kbd>nvcc</kbd> compiler option:
	<pre>${resource.hostname}-fe00:~$ nvcc -arch=sm_${resource.nvccarch} ...</pre>
