---
title: Ingress SFTP
tags:
 - linuxclusteritar
---

# Ingress FTP / SFTP

**ITaP does not support FTP on any ITaP research systems because it does not allow for secure transmission of data. Use SFTP instead, as described below.**

<em>SFTP</em> (Secure File Transfer Protocol) is a reliable way of transferring files between two machines.  SFTP is available as a protocol choice in some graphical file transfer programs and also as a command-line program on most Linux, Unix, and Mac OS X systems.  SFTP has more features than SCP and allows for other operations on remote files, remote directory listing, and resuming interrupted transfers.  Command-line SFTP cannot recursively copy directory contents; to do so, try using SCP or a graphical SFTP client.


{::if user.username != myusername}
General command-line usage:

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


##Accessing the ${resource.name} Inbound SFTP Server

<strong> The only data that may be uploaded to ${resource.name} are those allowed by your project's Technology Control Plan.  Normal, fundamental research data should not be uploaded to ${resource.name}.</strong>

{::if user.username != myusername}
- Using the CISCO VPN client, connect to the VPN at `reedvpn.itap.purdue.edu/cui`.

- Using an SFTP client, connect to `weber-sftp.rcac.purdue.edu`. 

- Your login credentials are your Purdue Career Account ID and password.

##Transferring Files into ${resource.name}

- Once connected to the server, you will see two directories: `/inbox-outside/${user.username}` and `/outbox-outside/${user.username}`.
- Start by uploading files that you want transferred into ${resource.name} into the `/inbox-outside/${user.username}` directory.  This will stage files for ingress.

- Next, you can begin the transfer by uploading a file named `begin_transfer.flag` into `/inbox-outside/${user.username}`.

- Every 5 minutes, the ingress system checks for the existence of the `begin_transfer.flag` file, and if it is found, it begins the transfer.  Once the transfer begins, a file named `transfer_in_progress.flag` will appear in the directory, and your input files will be scanned for viruses and malware.

- <strong>Do not</strong> transfer additional files into the `/inbox-outside/${user.username}` directory until the `transfer_in_progress.flag` file disappears. The files that you wanted to upload into ${resource.name} will also disappear once the transfer is completed.

- You may then retrieve your files from `/inbox-inside/${user.username}` within ${resource.name}.

{::else}

<strong>Additional log in instructions may be available to you after signing in to this website in the upper right corner.</strong>

{::/}
