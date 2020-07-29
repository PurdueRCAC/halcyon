---
title: HTAR
tags:
 - tapestorage
---

# HTAR

<em>HTAR</em> (short for &quot;HPSS TAR&quot;) is a utility program that writes TAR-compatible archive files directly onto ${resource.name}, without having to first create a local file.  Its command line was originally based on <kbd>tar</kbd>, with a number of extensions added to provide extra features.

HTAR is provided on all ITaP Research Computing systems as the command <kbd>htar</kbd>. HTAR is also available for <a href="/downloads/archive/#hsi">download</a> for many operating systems.


## Usage:

Create a tar archive on ${resource.name} named data.tar including all files with the extension ".fits":
<pre>
$ htar -cvf data.tar *.fits
HTAR: a   data1.fits
HTAR: a   data2.fits
HTAR: a   data3.fits
HTAR: a   data4.fits
HTAR: a   data5.fits
HTAR: a   /tmp/HTAR_CF_CHK_17953_1317760775
HTAR Create complete for data.tar. 5,120,006,144 bytes written for 5 member files, max threads: 3 Transfer time: 16.457 seconds (311.121 MB/s)
HTAR: HTAR SUCCESSFUL
</pre>

Unpack a tar archive on ${resource.name} named data.tar into a scratch directory for use in a batch job:
<pre>
$ cd $RCAC_SCRATCH/job_dir
$ htar -xvf data.tar
HTAR: x data1.fits, 1024000000 bytes, 2000001 media blocks
HTAR: x data2.fits, 1024000000 bytes, 2000001 media blocks
HTAR: x data3.fits, 1024000000 bytes, 2000001 media blocks
HTAR: x data4.fits, 1024000000 bytes, 2000001 media blocks
HTAR: x data5.fits, 1024000000 bytes, 2000001 media blocks
HTAR: Extract complete for data.tar, 5 files. total bytes read: 5,120,004,608 in 18.841 seconds (271.749 MB/s )
HTAR: HTAR SUCCESSFUL
</pre>

Look at the contents of the data.tar HTAR archive on ${resource.name}:
<pre>
$ htar -tvf data.tar
HTAR: -rw-r--r--  ${user.username}/pucc 1024000000 2011-10-04 16:30  data1.fits
HTAR: -rw-r--r--  ${user.username}/pucc 1024000000 2011-10-04 16:35  data2.fits
HTAR: -rw-r--r--  ${user.username}/pucc 1024000000 2011-10-04 16:35  data3.fits
HTAR: -rw-r--r--  ${user.username}/pucc 1024000000 2011-10-04 16:35  data4.fits
HTAR: -rw-r--r--  ${user.username}/pucc 1024000000 2011-10-04 16:35  data5.fits
HTAR: -rw-------  ${user.username}/pucc        256 2011-10-04 16:39  /tmp/HTAR_CF_CHK_17953_1317760775
HTAR: Listing complete for data.tar, 6 files 6 total objects
HTAR: HTAR SUCCESSFUL
</pre>

Unpack a single file, "data5.fits", from the tar archive on ${resource.name} named data.tar into a scratch directory.:
<pre>
$ htar -xvf data.tar data5.fits
HTAR: x data5.fits, 1024000000 bytes, 2000001 media blocks
HTAR: Extract complete for data.tar, 1 files. total bytes read: 1,024,000,512 in 3.642 seconds (281.166 MB/s )
HTAR: HTAR SUCCESSFUL
</pre>

### HTAR Archive Verification

HTAR allows different types of content verification while creating archives. Users can ask HTAR to verify the contents of an archive during (or after) creation using the '-Hverify' switch. The syntax of this option is:

<pre>
htar -Hverify=option[,option...] ... other arguments ... 
</pre>


<table class="inrows-wide">
	<caption>where <kbd>option</kbd> can be any of the following:</caption>
	<tr>
		<th scope = "col">Option</th>
		<th scope = "col">Explanation</th>
	</tr>
	<tr>
		<td><b>info</b></td>
		<td>Compares tar header info with the corresponding values in the index.</td>
	</tr>
	<tr>
		<td><b>crc</b></td> 
		<td>Enables CRC checking of archive files for which a CRC was generated when the file is added to the archive.</td>
	</tr>
	<tr>
		<td><b>compare</b></td> 
		<td>Enables a byte-by-byte comparison of archive member files and their local file counterparts.</td>
	</tr>
	<tr>
		<td><b>nocrc</b></td>
		<td>Disables CRC checking of archive files.</td>
	</tr>
	<tr>
		<td><b>nocompare</b></td>
		<td>Disables a byte-by-byte comparison of archive member files and their local file counterparts.</td>
	</tr>
</table>


Users can use a comma-separated list of <kbd>options</kbd> shown above, or a numeric value, or the wildcard <kbd>all</kbd> to specify the degree of verification. The numeric values for <kbd>Hverify</kbd> can be interpreted as follows:

<pre>
<b>0</b>: Enables "info" verification.
<b>1</b>: Enables level 0 + "crc" verification.
<b>2</b>: Enables level 1 + "compare" verification.
<b>all</b>: Enables all comparison options.
</pre>

An example to verify an archive during creation using checksums (crc):
<pre>
htar -Hverify=1 -cvf abc.tar ./abc
</pre>

An example to verify a previously created archive using checksums (crc):
<pre>
htar -Hverify=1 -Kvf abc.tar
</pre>

Please note that the time for verifying an archive increases as you increase the verification level. Carefully choose the option that suits your dataset best.

For details please see the <a target="_blank" rel="noopener" href="http://pal.mgleicher.us/index.html/htar/htar_man_page.html">HTAR Man Page</a>.

For more information about HTAR:
<ul>
	<li><a href="http://pal.mgleicher.us/index.html/htar/htar_user_guide.html">Gleicher Enterprises HTAR User Guide</a></li>
</ul>
