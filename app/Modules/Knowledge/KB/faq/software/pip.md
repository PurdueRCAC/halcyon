---                                                                              
title: "Cannot use pip after loading ml-toolkit modules"                                                           
tags:                                                                               
 - faq                                                                              
---                                                                               
{::if resource.name != Weber}
### Question                                                                           
Pip throws an error after loading the machine learning modules. How can I fix it?

### Answer
Machine learning modules (tensorflow, pytorch, opencv etc.) include a version of <kbd>pip</kbd> that is newer than the one installed with Anaconda. As a result it will throw an error when you try to use it.
<pre><code>$ pip --version
Traceback (most recent call last):
  File "/apps/cent7/anaconda/5.1.0-py36/bin/pip", line 7, in &lt;module&gt;
    from pip import main
ImportError: cannot import name 'main'
</code></pre>

The preferred way to use <kbd>pip</kbd> with the machine learning modules is to invoke it via Python as shown below.
<pre><code>$ python -m pip --version</code></pre>
{::else}
Neither pip nor ml-toolkit are available on ${resource.name}, although they are available on the other community clusters.
{::/}
