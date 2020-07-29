---
title: How can I get detailed hardware information about the GPUs?
tags:
 - gpu
 - internal
---

### How can I get detailed hardware information about the GPUs?

- You can get detailed hardware information about the GPUs using the following command:
	<pre>${resource.hostname}-a000:~$ nvidia-smi</pre>
- Different suboptions can be used with <kbd>nvidia-smi</kbd> to control amount of information that is printed.
- Print all information about all GPUs:
	<pre>${resource.hostname}-a000:~$ nvidia-smi -a</pre>
- Print all information about GPU 0:
	<pre>${resource.hostname}-a000:~$ nvidia-smi -a -i 0</pre>

{::if user.staff == 1}
### Staff Notes

- Add info about <kbd>devicequery</kbd> when the binary is placed in /usr/bin
{::/}

