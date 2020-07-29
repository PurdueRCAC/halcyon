---
title: Egress SFTP
tags:
 - linuxclusteritar
---

# Egress FTP / SFTP

**ITaP does not support FTP on any ITaP research systems because it does not allow for secure transmission of data. Use SFTP instead, as described below.**

<em>SFTP</em> (Secure File Transfer Protocol) is a reliable way of transferring files between two machines.  SFTP is available as a protocol choice in some graphical file transfer programs and also as a command-line program on most Linux, Unix, and Mac OS X systems.  SFTP has more features than SCP and allows for other operations on remote files, remote directory listing, and resuming interrupted transfers.  Command-line SFTP cannot recursively copy directory contents; to do so, try using SCP or graphical SFTP client.

{::if user.username != myusername}
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
{::/}

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

##Transferring Files out of ${resource.name}

<strong> The only data that may be downloaded from ${resource.name} are those allowed by your project's Technology Control Plan.</strong>

It is the responsibility of the Principal Investigator and their project team members to ensure that their data is A) unrestricted or B) uncontrolled research prior to removing that data from Weber.  Controlled Research could be subject to publication restrictions, dissemination controls, or may involve proprietary or controlled inputs that make it subject to regulations. Controlled research must be properly marked and protected prior to distribution if specified by the Technology Control Plan.

{::if user.username != myusername}
###Instructions

- From inside ${resource.name}, place the files that you want to transfer out into `/outbox-inside/${user.username}`.  This stages the files for egress.

- Next, you can begin the transfer by copying a file named `begin_transfer.flag` into `/outbox-inside/${user.username}`.

- Every 5 minutes, the egress system checks for the existence of the `begin_transfer.flag` file, and if it is found, it begins the transfer.  Once the transfer begins, a file named `transfer_in_progress.flag` will appear in the directory, and your outbound files will be scanned for viruses and malware.

- <strong>Do not</strong> transfer additional files into the `/outbox-inside/${user.username}` directory until the `transfer_in_progress.flag` file disappears. The files that you wanted to download from ${resource.name} will also disappear once the transfer is completed.

- You may then retrieve your files from the SFTP Server.

##Accessing the ${resource.name} Outbound SFTP Server

- Using the CISCO VPN client, connect to the VPN at `reedvpn.itap.purdue.edu/cui`.

- Using an SFTP client, connect to `weber-sftp.rcac.purdue.edu`. 

- Your login credentials are your Purdue Career Account ID and password.

- Your outbound files may be downloaded from `/outbox-outside/${user.username}` using your SFTP client.

{::else}

<strong>Additional log in instructions may be available to you after signing in to this website in the upper right corner.</strong>

{::/}