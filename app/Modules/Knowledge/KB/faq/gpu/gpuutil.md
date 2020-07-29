---
title: How can I monitor GPU utilization of my application in ${resource.name}?
tags:
 - gpu
 - internal
---

### How can I monitor GPU utilization of my application in ${resource.name}?

- You can monitor your GPU utilization with any of the [Nvidia profiler tools](../nvprof).
- If you do not want detailed profiling, run the following command on the compute node(s) where your job is running. It will print GPU utilization every 5 seconds:
	<pre>${resource.hostname}-a000:~$ nvidia-smi --format=csv --query-gpu=index,utilization.gpu,memory.total,memory.used,memory.free,temperature.gpu -l 5</pre>

{::if user.staff == 1}
### Staff Notes

{::/}

