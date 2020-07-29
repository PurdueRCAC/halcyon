---
title: Storage Quota / Limits
tags:
 - linuxcluster
 - linuxclusteritar
---

# Storage Quota / Limits

ITaP imposes some limits on your disk usage on research systems. ITaP implements a quota on each filesystem. Each filesystem (home directory, scratch directory, etc.) may have a different limit. If you exceed the quota, you will not be able to save new files or new data to the filesystem until you delete or move data to long-term storage.

# Checking Quota

To check the current quotas of your home and scratch directories check the [My Quota](/account/myquota/) page or use the `myquota` command:

<pre>
$ myquota
Type        Filesystem          Size    Limit  Use         Files    Limit  Use
==============================================================================
home        ${user.username}         5.0GB   25.0GB  20%             -        -   -
{::if resource.hostname != workbench}
scratch     ${resource.scratch}/    8KB  476.8GB   0%             2  100,000   0%
{::/}
</pre>

The columns are as follows:

* Type:  indicates home or scratch directory.
* Filesystem:  name of storage option.
* Size:  sum of file sizes in bytes.
* Limit:  allowed maximum on sum of file sizes in bytes.
* Use:  percentage of file-size limit currently in use.
* Files:  number of files and directories (not the size).
* Limit:  allowed maximum on number of files and directories.  It is possible, though unlikely, to reach this limit and not the file-size limit if you create a large number of very small files.
* Use:  percentage of file-number limit currently in use.

If you find that you reached your quota in either your home directory or your scratch file directory, obtain estimates of your disk usage. Find the top-level directories which have a high disk usage, then study the subdirectories to discover where the heaviest usage lies.

To see in a human-readable format an estimate of the disk usage of your top-level directories in your home directory:

<pre>
$ du -h --max-depth=1 $HOME &gt;myfile
32K /home/${user.username}/mysubdirectory_1
529M    /home/${user.username}/mysubdirectory_2
608K    /home/${user.username}/mysubdirectory_3
</pre>

The second directory is the largest of the three, so apply command <kbd>du</kbd> to it.</p>

To see in a human-readable format an estimate of the disk usage of your top-level directories in your scratch file directory:

<pre>
$ du -h --max-depth=1 $RCAC_SCRATCH >myfile
{::if resource.letteredscratch == true}
160K    ${resource.scratch}/${user.usernameletter}/${user.username}
{::else}
160K    ${resource.scratch}/${user.username}
{::/}
</pre>

This strategy can be very helpful in figuring out the location of your largest usage. Move unneeded files and directories to long-term storage to free space in your home and scratch directories.

# Increasing Quota

### Home Directory
{::if resource.name != Weber}
If you find you need additional disk space in your home directory, please first consider archiving and compressing old files and moving them to long-term storage on the <a href="/storage/fortress/">Fortress HPSS Archive</a>. Unfortunately, it is not possible to increase your home directory quota beyond it's current level.
{::else}
If you find you need additional disk space in your home directory, please first consider archiving and compressing old files and moving them to long-term storage on ${resource.name}. Unfortunately, it is not possible to increase your home directory quota beyond it's current level.
{::/}

{::if resource.name == Weber}
### Scratch Space

If you find you need additional disk space in your scratch space, please first consider archiving and compressing old files and moving them to long-term storage on ${resource.name}.

{::elseif resource.hostname == workbench}

{::else}
### Scratch Space

If you find you need additional disk space in your scratch space, please first consider archiving and compressing old files and moving them to long-term storage on the <a href="/storage/fortress/">Fortress HPSS Archive</a>. If you are unable to do so, you may ask for a quota increase at <a href="mailto:rcac-help@purdue.edu">rcac-help@purdue.edu</a>. 
{::/}
