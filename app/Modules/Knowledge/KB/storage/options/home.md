---
title: Home Directory
tags:
 - linuxcluster
 - linuxclusteritar
---

# Home Directory
{::if resource.name == Weber}
ITaP provides <em>home directories</em> for long-term file storage. Each user has one home directory. You should use your home directory for storing important program files, scripts, input data sets, critical results, and frequently used files.  Your home directory becomes your current working directory, by default, when you log in.

Your home directory physically resides on a ZFS storage system only accessible for ${resource.name}.  To find the path to your home directory, first log in then immediately enter the following:

{::else}
ITaP provides <em>home directories</em> for long-term file storage. Each user has one home directory. You should use your home directory for storing important program files, scripts, input data sets, critical results, and frequently used files.  You should store infrequently used files on Fortress. Your home directory becomes your current working directory, by default, when you log in.

ITaP provides daily snapshots of your home directory for a limited period of time in the event of accidental deletion. For additional security, you should store another copy of your files on more permanent storage, such as the [Fortress HPSS Archive](/storage/fortress/).

Your home directory physically resides on a GPFS storage system in the Research Computing data center.  To find the path to your home directory, first log in then immediately enter the following:
{::/}

    $ pwd
    /home/${user.username}

Or from any subdirectory:

    $ echo $HOME
    /home/${user.username}

{::if resource.name != Weber}
Your home directory and its contents are available on all ITaP research computing machines, including front-end hosts and compute nodes.
{::/}

<strong>Your home directory has a quota limiting the total size of files you may store within.</strong>  For more information, refer to the [Storage&#160;Quotas&#160;/&#160;Limits&#160;Section](../../quota).

### Lost File Recovery
ITaP maintains daily snapshots of your home directory for seven days in the event of accidental deletion. Cold storage backups of snapshots are kept for 90 days.  For additional security, you should store another copy of your files on more permanent storage, such as the [Fortress HPSS Archive](/storage/fortress/).

