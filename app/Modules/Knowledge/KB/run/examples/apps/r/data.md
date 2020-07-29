---
title: Loading Data into R
tags:
 - slurm
 - wholenode
 - sharednode
 - workbench
---
# Loading Data into R

R is an environment for manipulating data. In order to manipulate data, it must be brought into the R environment. R has a function to read any file that data is stored in. Some of the most common file types like comma-separated variable(CSV) files have functions that come in the basic R packages. Other less common file types require additional packages to be installed. To read data from a CSV file into the R environment, enter the following command in the R prompt:
<pre>
> read.csv(file = "path/to/data.csv", header = TRUE)
</pre> 
When R reads the file it creates an object that can then become the target of other functions. By default the read.csv() function will give the object the name of the .csv file. To assign a different name to the object created by read.csv enter the following in the R prompt:
<pre>
> my_variable <- read.csv(file = "path/to/data.csv", header = FALSE)
</pre>
To display the properties (structure) of loaded data, enter the following:
<pre>
> str(my_variable)
</pre>

For more functions and tutorials:
<ul>
 <li><a href="http://cran.r-project.org/manuals.html" target="_blank" rel="noopener">The R Manuals</a></li>
 <li><a href="http://www.mayin.org/ajayshah/KB/R/index.html" target="_blank" rel="noopener">Other R Examples</a></li>
 <li><a href="https://swcarpentry.github.io/r-novice-inflammation/" target="_blank" rel="noopener">Software Carpentry - Programing with R</a></li>
 <li><a href="http://www.datacarpentry.org/lessons/" target="_blank" rel="noopener">Data Carpentry Lessons</a></li>
</ul>
