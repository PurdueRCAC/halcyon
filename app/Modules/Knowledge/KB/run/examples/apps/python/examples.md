---
title: Example Python Jobs
tags:
 - slurm
 - wholenode
 - sharednode
---
##Examples

This section illustrates how to submit a small Python job to a PBS queue.

##Example 1: Hello world

Prepare a Python input file with an appropriate filename, here named <kbd>myjob.in</kbd>:
<pre>
# FILENAME:  hello.py

import string, sys
print "Hello, world!"
</pre>

Prepare a job submission file with an appropriate filename, here named <kbd>myjob.sub</kbd>:
<pre>
#!/bin/bash
# FILENAME:  myjob.sub

module load anaconda
{::if resource.batchsystem == pbs}
cd $PBS_O_WORKDIR
{::/}
python hello.py
</pre>

[Submit the job](/knowledge/${resource.hostname}/run/${resource.batchsystem}/submit)

[View job status](/knowledge/${resource.hostname}/run/${resource.batchsystem}/status)

[View results of the job](/knowledge/${resource.hostname}/run/${resource.batchsystem}/output)
<pre>
Hello, world!
</pre> 


###Example 2: Matrix multiply

Save the following script as matrix.py:
<pre>
# Matrix multiplication program

x = [[3,1,4],[1,5,9],[2,6,5]]
y = [[3,5,8,9],[7,9,3,2],[3,8,4,6]]

result = [[sum(a*b for a,b in zip(x_row,y_col)) for y_col in zip(*y)] for x_row in x]

for r in result:
        print(r)
</pre>

Change the last line in the job submission file above to read:
<pre>
python matrix.py
</pre>

The standard output file from this job will result in the following matrix:
<pre>
[28, 56, 43, 53]
[65, 122, 59, 73]
[63, 104, 54, 60]
</pre>

###Example 3: Sine wave plot using numpy and matplotlib packages
Save the following script as sine.py:
<pre>
import numpy as np
import matplotlib
matplotlib.use('Agg')
import matplotlib.pylab as plt

x = np.linspace(-np.pi, np.pi, 201)
plt.plot(x, np.sin(x))
plt.xlabel('Angle [rad]')
plt.ylabel('sin(x)')
plt.axis('tight')
plt.savefig('sine.png')
</pre>

Change your job submission file to submit this script and the job will output a png file and blank standard output and error files.

For more information about Python:
<ul>
 <li><a href="http://www.python.org/" target="_blank" rel="noopener">The Python Programming Language - Official Website</a></li>
 <li><a href="https://store.continuum.io/cshop/anaconda/" target="_blank" rel="noopener">Anaconda Python Distribution - Official Website</a></li>
 <li><a href="https://conda.io/docs/user-guide/" target="_blank" rel="noopener">Conda User Guide</a></li>
<ul>
