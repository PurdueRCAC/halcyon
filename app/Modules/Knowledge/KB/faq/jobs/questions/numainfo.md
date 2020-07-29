---
title: How do I know Non-uniform Memory Access (NUMA) layout on ${resource.name}?
tags:
 - faq
 - internal
---

### How do I know Non-uniform Memory Access (NUMA) layout on ${resource.name}?

- You can learn about processor layout on ${resource.name} nodes using the following command:
	<pre>${resource.hostname}-a000:~$ lstopo-no-graphics</pre>
- For detailed IO connectivity:
	<pre>${resource.hostname}-a000:~$ lstopo-no-graphics --physical --whole-io</pre>
- Please note that NUMA information is useful for advanced MPI/OpenMP/GPU optimizations. For most users, using default NUMA settings in MPI or OpenMP would give you the best performance.

{::if user.staff == 1}
### Staff Notes

{::/}

