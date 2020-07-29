---
title: Scratch Space
tags:
 - slurm
 - wholenode
 - sharednode
 - linuxclusteritar
---

# Scratch Space
{::if resource.name != Weber}
ITaP provides <em>scratch directories</em> for short-term file storage only. The quota of your scratch directory is much greater than the quota of your home directory. You should use your scratch directory for storing temporary input files which your job reads or for writing temporary output files which you may examine after execution of your job. You should use your home directory and Fortress for longer-term storage or for holding critical results. The <kbd>hsi</kbd> and <kbd>htar</kbd> commands provide easy-to-use interfaces into the archive and can be used to copy files into the archive interactively or even automatically at the end of your regular job submission scripts.
 
<strong>Files in scratch directories are not recoverable.</strong>  ITaP does not back up files in scratch directories.  If you accidentally delete a file, a disk crashes, or old files are purged, they cannot be restored.

<strong>ITaP purges files from scratch directories not accessed or had content modified in 60 days.</strong>  Owners of these files receive a notice one week before removal via email. Be sure to regularly check your Purdue email account or <a href="https://www.purdue.edu/apps/account/ChangeMailbox">set up mail forwarding</a> to an email account you do regularly check. For more information, please refer to our <a href="/policies/scratchpurge/">Scratch File Purging Policy</a>.
{::else} 
ITaP provides <em>scratch directories</em> for short-term file storage only. The quota of your scratch directory is much greater than the quota of your home directory. You should use your scratch directory for storing temporary input files which your job reads or for writing temporary output files which you may examine after execution of your job. You should use your home directory and ${resource.name} long-term storage for holding critical results.
 
<strong>Files in scratch directories are not recoverable.</strong>  ITaP does not back up files in scratch directories.  If you accidentally delete a file, a disk crashes, or old files are purged, they cannot be restored.  Unique among our cluster resources, data are not purged from Weber scratch directories at this time.
{::/}

All users may access scratch directories on ${resource.name}.  To find the path to your scratch directory:

<pre>
$ findscratch
{::if resource.letteredscratch == true}
${resource.scratch}/${user.usernameletter}/${user.username}
{::else}
${resource.scratch}/${user.username}
{::/}
</pre>

The value of variable $RCAC_SCRATCH is your scratch directory path.  Use this variable in any scripts.  Your actual scratch directory path may change without warning, but this variable will remain current.

<pre>
$ echo $RCAC_SCRATCH
{::if resource.letteredscratch == true}
${resource.scratch}/${user.usernameletter}/${user.username}
{::else}
${resource.scratch}/${user.username}
{::/}
</pre>

{::if resource.name!= Weber}
<strong>All scratch directories are available on each front-end of all computational resources, however, only the ${resource.scratch} directory is available on ${resource.name} compute nodes. No other scratch directories are available on ${resource.name} compute nodes.</strong>
{::/}

<strong>Your scratch directory has a quota capping the total size and number of files you may store in it.</strong>  For more information, refer to the section [Storage&#160;Quotas&#160;/&#160;Limits&#160;](../../quota).
