---
title: flost
tags:
 - depot
 - home
---

If you know when you lost the file, the easiest way is to use the <kbd>flost</kbd> command. This tool is available from any ITaP Research Computing resource. If you do not have access to a compute cluster, any Data Depot user may use an SSH client to connect to <kbd>${resource.hostname}.rcac.purdue.edu</kbd> and run this command.

To run the tool you will need to specify the location where the lost file was with the <kbd>-w</kbd> argument:</p>

<pre>
$ flost -w /depot/mylab
</pre>

Replace <kbd>mylab</kbd> with the name of your lab's ${resource.name} directory. If you know more specifically where the lost file was you may provide the full path to that directory.

This tool will prompt you for the date on which you lost the file or would like to recover the file from. If the tool finds an appropriate snapshot it will provide instructions on how to search for and recover the file.

If you are not sure what date you lost the file you may try entering different dates into the <kbd>flost</kbd> to try to find the file or you may also [manually browse](../manual) the snapshots as described below.
