---
title: How do I profile my GPU-enabled applications on ${resource.name}?
tags:
 - gpu
 - internal
---

### How do I profile my GPU-enabled applications on ${resource.name}?

- You can profile your GPU-enabled applications using Nvidia profiler tools <kbd>nvprof</kbd> and <kbd>nvvp</kbd>.
- For profiling an application without GUI run:
	<pre>${resource.hostname}-a000:~$ nvprof application_name</pre>
- For profiling an application with GUI run:
	<pre>${resource.hostname}-a000:~$ nvvp</pre>
- For more details, please refer to Nvidia <a href="http://docs.nvidia.com/cuda/profiler-users-guide/index.html" target="_blank" rel="noopener">Profiling Guide</a>.

{::if user.staff == 1}
### Staff Notes

{::/}

