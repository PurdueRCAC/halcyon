---
title: Windows Network Drive / SMB
tags:
 - linuxcluster
 - diskstorage
---

# Windows Network Drive / SMB

<em>SMB</em> (Server Message Block), also known as CIFS, is an easy to use file transfer protocol that is useful for transferring files between ITaP research systems and a desktop or laptop. You may use SMB to connect to your home, scratch, and Fortress storage directories. The SMB protocol is available on Windows, Linux, and Mac OS X. It is primarily used as a graphical means of transfer but it can also be used over the command line.

<strong>Note:  to access ${resource.name} through SMB file sharing, you must be on a Purdue campus network or connected through <a href="http://www.itap.purdue.edu/connections/vpn/">VPN</a>.</strong>

Windows:

<ul>
 <li>Windows 7: Click Windows menu > Computer, then click Map Network Drive in the top bar</li>
 <li>Windows 8 & 10: Tap the Windows key, type <kbd>computer</kbd>, select This PC, click Computer > Map Network Drive in the top bar</li>
 <li>In the folder location enter the following information and click Finish:
 <p><ul>
{::if resource.dir == depot}
 <li>To access your Data Depot directory, enter <kbd>\\datadepot.rcac.purdue.edu\depot\mylab</kbd> where mylab is your research group name.</li>
{::elseif resource.name == Fortress} 
 <li>To access your Fortress home directory, enter <kbd>\\fortress-smb.rcac.purdue.edu\${user.username}</kbd>.</li>
  <li>To access your Fortress group directory, enter <kbd>\\fortress-smb.rcac.purdue.edu\group\mylab</kbd> where <kbd>mylab</kbd> is your research group name.</li>
{::else}
 <li>To access your home directory, enter <kbd>\\home.rcac.purdue.edu\${user.username}</kbd>.</li>
 <li>To access your scratch space on ${resource.name}, enter <kbd>\\scratch.${resource.frontend}.rcac.purdue.edu\${resource.frontend}</kbd>. Once mapped, you will be able to navigate to your scratch directory.</li>
 <li>See the <a href="/knowledge/fortress/storage/transfer/cifs">Fortress section</a> on mapping your Fortress space.</li>
 {::/}
 </ul></p></li>
{::if resource.name == Fortress} 
 <li>You may be prompted for login information. Enter your username as <kbd>onepurdue\${user.username}</kbd> and your account password. If you forget the <kbd>onepurdue</kbd> prefix it will prevent you from logging in.</li>
{::/}
  <li>Your {::if resource.dir == depot}${resource.name}{::elseif resource.name == Fortress}${resource.name}{::else}home, scratch, or Fortress{::/} directory should now be mounted as a drive in the Computer window.</li>
 </ul>

Mac OS X:
<ul>
 <li>In the Finder, click Go > Connect to Server</li>
 <li>In the Server Address enter the following information and click Connect:
 <p><ul>
{::if resource.dir == depot}
<li>To access your depot directory, enter <kbd>smb://datadepot.rcac.purdue.edu/depot/mylab</kbd> where mylab is your research group name.</li>
{::elseif resource.name == Fortress} 
 <li>To access your Fortress home directory, enter <kbd>smb://fortress-smb.rcac.purdue.edu/${user.username}</kbd>.</li>
  <li>To access your Fortress group directory, enter <kbd>smb://fortress-smb.rcac.purdue.edu/group/mylab</kbd> where <kbd>mylab</kbd> is your research group name.</li>
{::else}
 <li>To access your home directory, enter <kbd>smb://home.rcac.purdue.edu/${user.username}</kbd>.</li>
 <li>To access your scratch space on ${resource.name}, enter <kbd>smb://scratch.${resource.frontend}.rcac.purdue.edu/${resource.frontend}</kbd>. Once connected, you will be able to navigate to your scratch directory. </li>
 <li>See the <a href="/knowledge/fortress/storage/transfer/cifs">Fortress section</a> on mapping your Fortress space.</li>
{::/}
 </ul></p></li>
{::if resource.name == Fortress} 
 <li>You may be prompted for login information. Enter your username, password and for the domain enter <kbd>onepurdue</kbd> or it will prevent you from logging in.</li>
{::/}
</ul>

Linux:
 
<ul>
 <li>There are several graphical methods to connect in Linux depending on your desktop environment. Once you find out how to connect to a network server on your desktop environment, choose the Samba/SMB protocol and adapt the information from the Mac OS X section to connect.</li>
 <li>If you would like access via samba on the command line you may install smbclient which will give you ftp-like access and can be used as shown below. For all the possible ways to connect look at the Mac OS X instructions.
{::if resource.dir == depot}
 <pre>smbclient //datadepot.rcac.purdue.edu/depot/ -U ${user.username}
cd mylab</pre></li>
{::elseif resource.name == Fortress} 
 <pre>smbclient //fortress-smb.rcac.purdue.edu/${user.username} -U ${user.username}</pre></li>
{::else}
 <pre>smbclient //home.rcac.purdue.edu/${user.username} -U ${user.username}
smbclient //scratch.${resource.frontend}.rcac.purdue.edu/${resource.frontend} -U ${user.username}</pre></li>
{::/}
</ul>
