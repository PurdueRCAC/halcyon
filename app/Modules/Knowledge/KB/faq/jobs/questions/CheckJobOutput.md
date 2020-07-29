---
title: "How do I check my job output while it is running?"
tags:
 - internal
 - wholenode
 - sharednode
---

### Problem

After submitting your job to the cluster, you want to see the output that it generates.

### Solution

There are two simple ways to do this:

<ul>
<li><strong><kbd>qpeek</kbd>:</strong> Use the tool <kbd>qpeek</kbd> to check the job's output. Syntax of the command is:
<pre>qpeek &lt;jobid&gt;</pre>
</li>
<li><strong>Redirect your output to a file:</strong> To do this you need to edit the main command in your jobscript as shown below. Please note the redirection command starting with the greater than (>) sign.
<pre>myapplication ...other arguments... > "${PBS_JOBID}.output"</pre>
On any front-end, go to the working directory of the job and scan the output file.
<pre>tail "&lt;jobid&gt;.output"</pre>
Make sure to replace <kbd>&lt;jobid&gt;</kbd> with an appropriate jobid.
</li>
</ul>

{::if user.staff == 1}
### Staff Notes

{::/}
