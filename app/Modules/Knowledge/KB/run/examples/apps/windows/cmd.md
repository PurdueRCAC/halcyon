---
title: Command line
tags:
 - slurm
 - wholenode
 - sharednode
 - workbench
 - weber
---
# Command line

If you wish to work with Windows VMs on the command line or work into scripted workflows you can interact directly with the Windows system:

<ul>
{::if resource.qsub_needs_gpu == 1}
<li> Submit an interactive PBS job, with the appropriate walltime and queue:
<pre>
$ qsub -X -I -l walltime=8:00:00 -l nodes=1:ppn=1:gpus=1
</pre>
{::elseif resource.batchsystem == pbs}
<li> Submit an interactive PBS job, with the appropriate walltime and queue:
<pre>
$ qsub -X -I -l walltime=8:00:00 -l nodes=1:ppn=${resource.nodecores}
</pre>
{::/}
<li>Load the "qemu" module:
<pre>
$ module load qemu
</pre>
</ul>

{::if resource.name != Weber}
Copy a Windows 2016 Server VM image to your storage. Scratch or Research Data Depot are good locations to save a VM image. If you are using scratch, remember that <a href="/policies/scratchpurge">scratch spaces are temporary</a>, and be sure to safely back up your disk image somewhere permanent, such as Research Data Depot or Fortress.
To copy a basic image:
<pre>
$ cp /depot/itap/windows/base/2k16.qcow2 $RCAC_SCRATCH/windows.qcow2
</pre>
To copy a GIS image:
<pre>
$ cp /depot/itap/windows/gis/2k16.qcow2 $RCAC_SCRATCH/windows.qcow2
</pre>
{::else}

To launch a virtual machine in a batch job, use the "windows" script, specifying the path to your Windows virtual machine image. With no other command-line arguments, the <kbd>windows</kbd> script will autodetect a number cores and memory for the Windows VM. 
{::if resource.batchsystem == pbs}
A Windows network connection will be made to your cluster scratch directory. 
{::else}
A Windows network connection will be made to your home directory. 
{::/}
To launch:
<pre>
{::if resource.name != Weber}
$ windows  -i $RCAC_SCRATCH/windows.qcow2
{::else}
$ /depot/windows/weberwin.sh
{::/}
</pre>

{::if resource.name != Weber}
### Command line options:
<pre>
-i &lt;path to qcow image file&gt; (For example, $RCAC_SCRATCH/windows-2k16.qcow2)
-m &lt;RAM&gt;G (For example, 32G)
-c &lt;cores&gt; (For example, 20)
-s &lt;smbpath&gt; (UNIX Path to map as a drive, for example, $RCAC_SCRATCH)
-b  (If present, launches VM in background. Use VNC to connect to Windows.)
</pre>

To launch a virtual machine with 32GB of RAM, 20 cores, and a network mapping to your home directory:
<pre>
$ windows -i /path/to/image.qcow2  -m 32G -c 20 -s $HOME
</pre>
To launch a virtual machine with 16GB of RAM, 10 cores, and a network mapping to your Data Depot space:
<pre>
$ windows -i /path/to/image.qcow2  -m 16G -c 10 -s /depot/mylab
</pre>
{::/}

{::if resource.batchsystem == pbs}
To launch a background virtual machine with 16GB of RAM, 10 cores, and a network mapping to your cluster scratch, connecting via VNC:
<pre>
$ windows -i /path/to/image.qcow2  -m 16G -c 10 -s $RCAC_SCRATCH -b
$ vncviewer `hostname`:1
</pre>
You can use VNC to connect to this background virtual machine from the cluster node assigned by PBS,  or from the login nodes.
{::/}

{::if resource.name != Weber}
The Windows 2016 server desktop will open, and automatically log in as an administrator, so that you can install any software into the Windows virtual machine that your research requires. Changes to the image will be stored in the file specified with the <kbd>-i</kbd> option.
{::else}
The Windows desktop will open, and automatically log in as a temporary user.
No changes to the VM will be preserved.
{::/}
