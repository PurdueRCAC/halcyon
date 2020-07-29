---
title: Scratch Space
tags:
 - hadoop
---

# Scratch Space

ITaP provides <em>scratch directories</em> for short-term file storage only. The quota of your scratch directory is much greater than the quota of your home directory. You should use your scratch directory for storing temporary input files which your job reads or for writing temporary output files which you may examine after execution of your job. You should use your home directory and Fortress for longer-term storage or for holding critical results. The <kbd>hsi</kbd> and <kbd>htar</kbd> commands provide easy-to-use interfaces into the archive and can be used to copy files into the archive interactively or even automatically at the end of your regular job submission scripts.
 
<strong>Files in scratch directories are not recoverable.</strong>  ITaP does not back up files in scratch directories.  If you accidentally delete a file, a disk crashes, or old files are purged, they cannot be restored.

<strong>ITaP purges files from scratch directories not accessed or had content modified in 60 days.</strong>  Owners of these files receive a notice one week before removal via email. Be sure to regularly check your Purdue email account or <a href="https://www.purdue.edu/apps/account/ChangeMailbox">set up mail forwarding</a> to an email account you do regularly check. For more information, please refer to our <a href="/policies/scratchpurge/">Scratch File Purging Policy</a>.
 
All users may access scratch directories on ${resource.name}.  To find the path to your scratch directory:

<pre>
$ findscratch
${resource.scratch}/${user.username}
</pre>

The value of variable $RCAC_SCRATCH is your scratch directory path.  Use this variable in any scripts.  Your actual scratch directory path may change without warning, but this variable will remain current.

<pre>
$ echo $RCAC_SCRATCH
${resource.scratch}/${user.username}
</pre>

You can use the <em>hdfs</em> command to list the contents of a directory in HDFS scratch.
<pre>
$ hdfs dfs -ls $RCAC_SCRATCH
Found 1 items
drwxrwxr-t   - ${user.username} ${user.username}          0 2014-08-26 21:38 ${resource.scratch}/${user.username}
</pre>

The <em>hdfs dfs</em> command supports many options similar to standard UNIX commands. To see available commands:
<pre>
$ hdfs dfs
Usage: hadoop fs [generic options]
...
</pre>

You may also operate on files in your HDFS scratch using standard Unix utilities such as 'ls', 'cd', 'cp', 'mkdir', 'find', 'grep', or use standard Posix libraries like open, write, read, close from C, C++, Python, Ruby, Perl, Java, bash, etc. Please note, however, that the filesystem interface does not have all of the capabilities of a real UNIX filesystem.

With the filesystem interface, users may:
<ul>
 <li>Browse the HDFS file system through their local file system on NFSv3 client compatible operating systems.
 <li>Download files from the the HDFS file system on to a UNIX file system.
 <li>Upload files from a UNIX file system directly to the HDFS file system.
 <li>Stream data directly to HDFS through the mount point. File append is supported but random write is not supported. Text editors like 'vi' or 'emacs' cannot edit a file through the HDFS filesystem interface.
</ul>

To access HDFS scratch through the filesystem interface, simply 'cd' to /hadoop/mnt
