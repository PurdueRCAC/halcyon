---
title: /tmp Directory
tags:
 - linuxcluster
---

# /tmp Directory

ITaP provides <em>/tmp directories</em> for short-term file storage only. Each front-end and compute node has a /tmp directory. Your program may write temporary data to the /tmp directory of the compute node on which it is running. That data is available for as long as your program is active. Once your program terminates, that temporary data is no longer available.   When used properly, /tmp may provide faster local storage to an active process than any other storage option.  You should use your home directory and Fortress for longer-term storage or for holding critical results.

ITaP does not perform backups for the /tmp directory and removes files from /tmp whenever space is low or whenever the system needs a reboot.  In the event of a disk crash or file purge, <strong>files in /tmp are not recoverable</strong>. You should copy any important files to more permanent storage.
