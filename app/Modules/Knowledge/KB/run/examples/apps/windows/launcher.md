---
title: Menu Launcher
tags:
 - slurm
 - wholenode
 - sharednode
 - workbench
 - weber
---

# Menu Launcher

Windows VMs can be easily launched through the <a href="knowledge/${resource.dir}/accounts/login/thinlinc">Thinlinc</a> remote desktop environment.

* Log in via <a href="knowledge/${resource.dir}/accounts/login/thinlinc">Thinlinc</a>.
* Click on Applications menu in the upper left corner.
* Look under the Cluster Software menu.
{::if resource.batchsystem == pbs}
* Use the "Windows 10 interactive job" launcher to launch a VM on a compute node.
{::/}
* The "Windows 10" launcher will launch a VM directly on the front-end.
* Follow the dialogs to set up your VM.

<img src="/knowledge/downloads/run/examples/apps/windows/images/menu.png" alt="Screenshot of menu" />

The dialog menus will walk you through setting up and loading your VM.

{::if resource.name != Weber}
* You can choose to create a new image or load a saved image.
* New VMs should be saved on Scratch or Research Data Depot as they are too large for Home Directories.
* If you are using scratch, remember that <a href="/policies/scratchpurge">scratch spaces are temporary</a>, and be sure to safely back up your disk image somewhere permanent, such as Research Data Depot or Fortress.

You will also be prompted to select a storage space to mount on your image (Home, Scratch, or Data Depot). You can only choose one to be mounted. It will appear on a shortcut on the desktop once the VM loads.
{::/}

### Notes

Using the menu launcher will launch automatically select reasonable CPU and memory values. If you wish to choose other options or work Windows VMs into scripted workflows see the section on [using the command line](../cmd).

{::if resource.batchsystem == pbs}
VMs ran on the front-ends should only be used for light computation work as the front-ends are shared resources amongst many people. Any compute or memory intensive work should be ran through interactive jobs.
{::/}
