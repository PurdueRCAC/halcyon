---
title: Archive and Compression
tags:
 - linuxcluster
 - linuxclusteritar
 - diskstorage
---

# Archive and Compression
{::if resource.name == Weber}
Archived files and directories must remain on ${resource.name} and cannot be removed from the cluster without prior authorization.  Even after when a project ends, project materials must be placed within ???.  There are several options for archiving and compressing groups of files or directories on ITaP research systems.  The mostly commonly used options are:
{::else}
There are several options for archiving and compressing groups of files or directories on ITaP research systems.  The mostly commonly used options are:
{::/}



### <strong>tar</strong>
&#160;
<a href="http://www.gnu.org/software/tar/tar.html" target="_blank" rel="noopener">(more information)</a><br />

Saves many files together into a single archive file, and restores individual files from the archive.  Includes automatic archive compression/decompression options and special features for incremental and full backups.

Examples:

<pre>  (list contents of archive somefile.tar)
$ tar tvf somefile.tar

  (extract contents of somefile.tar)
$ tar xvf somefile.tar

  (extract contents of gzipped archive somefile.tar.gz)
$ tar xzvf somefile.tar.gz

  (extract contents of bzip2 archive somefile.tar.bz2)
$ tar xjvf somefile.tar.bz2

  (archive all ".c" files in current directory into one archive file)
$ tar cvf somefile.tar *.c

  (archive and gzip-compress all files in a directory into one archive file)
$ tar czvf somefile.tar.gz somedirectory/

  (archive and bzip2-compress all files in a directory into one archive file)
$ tar cjvf somefile.tar.bz2 somedirectory/
</pre>

Other arguments for <kbd>tar</kbd> can be explored by using the <kbd>man tar</kbd> command.

### <strong>gzip</strong>
&#160;
<a href="http://www.gnu.org/software/gzip/gzip.html" target="_blank" rel="noopener">(more information)</a><br />

The standard compression system for all GNU software.

Examples:

<pre>  (compress file somefile - also removes uncompressed file)
$ gzip somefile

  (uncompress file somefile.gz - also removes compressed file)
$ gunzip somefile.gz
</pre> 

### <strong>bzip2</strong>
&#160;
<a href="http://www.bzip.org/" target="_blank" rel="noopener">(more information)</a><br />

Strong, lossless data compressor based on the Burrows-Wheeler transform. Stronger compression than gzip.

Examples:

<pre>  (compress file somefile - also removes uncompressed file)
$ bzip2 somefile

  (uncompress file somefile.bz2 - also removes compressed file)
$ bunzip2 somefile.bz2
</pre>

There are several other, less commonly used, options available as well:

* zip
* 7zip
* xz
