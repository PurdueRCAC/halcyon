---
title: Interactive Jobs
tags:
 - slurm
---
# Interactive Jobs

Interactive jobs are run on compute nodes, while giving you a shell to interact with. They give you the ability to type commands or use a graphical interface as if you were on a front-end.

To submit an interactive job, use <kbd>sinteractive</kbd> to run a login shell on allocated resources.  

<kbd>sinteractive</kbd> accepts most of the same resource requests as <kbd>sbatch</kbd>, so to request a login shell on the <kbd>${resource.queue}</kbd> Account while allocating 2 nodes and ${resource.nodecores} total cores, you might do:

<pre>sinteractive -A ${resource.queue} -N2 -n${resource.nodecores}</pre>

To quit your interactive job:

<pre>exit</pre> or <pre> Ctrl-D </pre>
