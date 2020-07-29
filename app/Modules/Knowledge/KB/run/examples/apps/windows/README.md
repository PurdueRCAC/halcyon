---
title: Windows
tags:
 - wholenode
 - sharednode
 - weber
 - workbench
order:
 - cmd
 - launcher
---

# Windows

Windows virtual machines (VMs) are supported as batch jobs on HPC systems. This section illustrates how to submit a job and run a Windows instance in order to run Windows applications on the high-performance computing systems.

{::if resource.name != Weber}
The following images are pre-configured and made available by ITaP staff:
<ul>
   <li>Windows 2016 Server Basic (minimal software pre-loaded)
   <li>Windows 2016 Server GIS (GIS Software Stack pre-loaded)
</ul>
{::else}
${resource.name} provides a basic Windows 10 image to execute Microsoft Office applications within the cluster's boundaries.
<ul>
  <li>The Windows image is not persistent, and will default to a baseline state each time Windows is launched.
  <li>Only the provided Windows image is to be launched on ${resource.name}.
</ul>

{::/}

The Windows VMs can be launched in two fashions:


* [Menu Launcher](launcher) - Point and click to start
* [Command Line](cmd) - Advanced and customized usage

Click each of the above links for detailed instructions on using them.


{::if resource.name != Weber}
### Software Provided in Pre-configured Virtual Machines
<p>The Windows 2016 Base server image available on ${resource.name} has the following software packages preloaded:
<ul>
<li>Anaconda Python 2 and Python 3
<li>JMP 13
<li>Matlab R2017b
<li>Microsoft Office 2016
<li>Notepad++
<li>NVivo 12
<li>Rstudio
<li>Stata SE 15
<li>VLC Media Player
</ul>

<p>The Windows 2016 GIS server image available on ${resource.name} has the following software packages preloaded:

<ul>
<li>ArcGIS Desktop 10.5
<li>ArcGIS Pro
<li>ArcGIS Server 10.5
<li>Anaconda Python 2 and Python 3
<li>ENVI5.3/IDL 8.5
<li>ERDAS Imagine
<li>GRASS GIS 7.4.0
<li>JMP 13
<li>Matlab R2017b
<li>Microsoft Office 2016
<li>Notepad++
<li>Pix4d Mapper
<li>QGIS Desktop
<li>Rstudio
<li>VLC Media Player
</ul>

{::/}
