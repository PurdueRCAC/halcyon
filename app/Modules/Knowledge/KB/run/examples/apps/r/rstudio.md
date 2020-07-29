---
title: RStudio
tags:
 - slurm
 - wholenode
 - sharednode
 - workbench
---
{::if resource.name != Weber}
# RStudio

RStudio is a graphical integrated development environment(IDE) for R. RStudio is the most popular environment for developing both R scripts and packages. RStudio is provided on most Research systems and can be run by loading the following modules:
<pre>
$ module load gcc
$ module load r
$ module load rstudio
$ rstudio
</pre> 


Note that RStudio is a graphical program and in order to run it you must have a local X11 server running or use Thinlinc Remote Desktop environment. See the [ssh X11 forwarding section](../../../../../accounts/login/x11) for more details.


R and RStudio are free to download and run on your local machine. You can also use ${resource.name} to develop scripts but please remember to [submit](/knowledge/${resource.hostname}/run/${resource.batchsystem}/submit) any computationally intense tasks as jobs or [start an interactive job](../../../${resource.batchsystem}/interactive)

For more information about RStudio:
<ul>
 <li><a href="https://www.rstudio.com/">RStudio the Official Website</a></li>
 <li><a href="https://www.rstudio.com/resources/webinars/#rstudioessentials">RStudio Essentials: Tutorial</a></li>
 <li><a href="https://www.datacamp.com/courses/working-with-the-rstudio-ide-part-1">DataCamp: Working with the RStudio IDE</a></li>
</ul>
{::/}