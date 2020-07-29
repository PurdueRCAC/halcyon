---
title: SCP
tags:
 - linuxcluster
 - diskstorage
---

# SCP

<em>SCP</em> (Secure CoPy) is a simple way of transferring files between two machines that use the SSH protocol.  SCP is available as a protocol choice in some graphical file transfer programs and also as a command line program on most Linux, Unix, and Mac OS X systems.  SCP can copy single files, but will also recursively copy directory contents if given a directory name.

Command-line usage:

<pre>  (to a remote system from local)
$ scp sourcefilename ${user.username}@${resource.hostname}.rcac.purdue.edu:somedirectory/destinationfilename

  (from a remote system to local)
$ scp ${user.username}@${resource.hostname}.rcac.purdue.edu:somedirectory/sourcefilename destinationfilename

  (recursive directory copy to a remote system from local)
$ scp -r sourcedirectory/ ${user.username}@${resource.hostname}.rcac.purdue.edu:somedirectory/
</pre>

Linux / Solaris / AIX / HP-UX / Unix:

<ul>
	<li>The "scp" command-line program should already be installed.</li>
</ul>

Microsoft Windows:

<ul>
	<li>
		<a href="https://mobaxterm.mobatek.net/download.html" target="_blank" rel="noopener">MobaXterm</a><br />
		Free, full-featured, graphical Windows SSH, SCP, and SFTP client.
	</li>
</ul>

Mac OS X:

<ul>
	<li>You should have already installed the "scp" command-line program.  You may start a local terminal window from "Applications-&gt;Utilities".</li>
	<li><a href="https://cyberduck.io/" target="_blank" rel="noopener">Cyberduck</a> is a full-featured and free graphical SFTP and SCP client.</li>
</ul>
