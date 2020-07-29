---
title: SSH Client Software
tags:
 - linuxcluster
---

*Secure Shell* or *SSH* is a way of establishing a secure connection between two computers.  It uses public-key cryptography to authenticate the user with the remote computer and to establish a secure connection.  Its usual function involves logging in to a remote machine and executing commands.  There are many SSH clients available for all operating systems:

Linux / Solaris / AIX / HP-UX / Unix:
* The `ssh` command is pre-installed.  Log in using `ssh ${user.username}@${resource.frontend}.rcac.purdue.edu` from a terminal.

Microsoft Windows:

* [MobaXterm](http://mobaxterm.mobatek.net/download.html) is a small, easy to use, full-featured SSH client. It includes X11 support for remote displays, SFTP capabilities, and limited SSH authentication forwarding for keys.

Mac OS X:

* The `ssh` command is pre-installed.  You may start a local terminal window from "Applications-&gt;Utilities".  Log in by typing the command `ssh ${user.username}@${resource.frontend}.rcac.purdue.edu`.

