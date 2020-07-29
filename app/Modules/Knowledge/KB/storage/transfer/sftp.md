---
title: FTP / SFTP
tags:
 - linuxcluster
 - diskstorage
---

# FTP / SFTP

**ITaP does not support FTP on any ITaP research systems because it does not allow for secure transmission of data. Use SFTP instead, as described below.**

<em>SFTP</em> (Secure File Transfer Protocol) is a reliable way of transferring files between two machines.  SFTP is available as a protocol choice in some graphical file transfer programs and also as a command-line program on most Linux, Unix, and Mac OS X systems.  SFTP has more features than SCP and allows for other operations on remote files, remote directory listing, and resuming interrupted transfers.  Command-line SFTP cannot recursively copy directory contents; to do so, try using SCP or graphical SFTP client.

Command-line usage:

<pre>$ sftp -B buffersize ${user.username}@${resource.hostname}.rcac.purdue.edu

      (to a remote system from local)
sftp&gt; put sourcefile somedir/destinationfile
sftp&gt; put -P sourcefile somedir/

      (from a remote system to local)
sftp&gt; get sourcefile somedir/destinationfile
sftp&gt; get -P sourcefile somedir/

sftp&gt; exit
</pre>

<ul>
 <li><strong>-B</strong>:  optional, specify buffer size for transfer; larger may increase speed, but costs memory</li>
 <li><strong>-P</strong>:  optional, preserve file attributes and permissions</li>
</ul>
 <p>Linux / Solaris / AIX / HP-UX / Unix:</p>
<ul>
 <li>The "sftp" command-line program should already be installed.</li>
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
 <li>The "sftp" command-line program should already be installed.  You may start a local terminal window from "Applications-&gt;Utilities".</li>
 <li><a href="https://cyberduck.io/" target="_blank" rel="noopener">Cyberduck</a> is a full-featured and free graphical SFTP and SCP client.</li>
</ul>
