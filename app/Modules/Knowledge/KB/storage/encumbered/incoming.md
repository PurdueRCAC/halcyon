---
title: Incoming Files
tags:
 - reed
---

### Overview

There is a two-step process for bringing external files into REED.  Files are first uploaded to an internal staging area of your project's incoming server using file transfer client software, then the uploaded files are relocated to your working space from within the Windows environment.

Your project's server will be named as your project's name, followed by '-in.reed.rcac.purdue.edu'.  For instance, if your project's name is 'myproject-1', your incoming server will be 'myproject-1-in.reed.rcac.purdue.edu'.

### Transfer

* Log into the REED VPN [reedvpn.itap.purdue.edu](https://reedvpn.itap.purdue.edu/)
* Use WinSCP to connect to `myproject-in.reed.rcac.purdue.edu`. (use your project's name for `myproject`)
*   Login with your BoilerKey credentials
* Put your files into the `in` directory

Any client that supports the SFTP or SCP protocol can be used.  WinSCP is a commonly used client for Windows workstations.  

There is a limit of 90 Gigabytes in the `in` directory; if you have more data than that you will need to transfer it in smaller sets.

### Relocation
* With your files in the `in` directory, quit your SCP client.  Do not disconnect from the VPN.
* Login with Remote Desktop to your Project's Remote Desktop server.
* Your new files should be on your `I:` drive.  Move them from there to your working space, either home, group shared, or scratch drive (`H:`, `F:`, or `G:`)

You should move your files immediately when you transfer them.  Files will be automatically deleted from `in` after seven days.

