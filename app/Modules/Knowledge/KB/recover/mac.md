---
title: Mac OS X
tags:
 - depot
 - home
---

# Mac OS X

Mac OS X does not provide any way to access the ${resource.name} snapshots directly. To access the snapshots there are two options: browse the snapshots by hand through a network drive mount or use an automated command-line based tool.

To browse the snapshots by hand, follow the directions outlined in the [Manual Browsing](../manual) section.

To use the automated command-line tool, log into an ITaP Research Computing cluster or into the host <kbd>${resource.hostname}.rcac.purdue.edu</kbd> (which is available to all ${resource.name} users) and use the [flost](../flost) tool. On Mac OS X you can use the built-in SSH terminal application to connect.
<ul>
  <li>Open the Applications folder from Finder.</li>
  <li>Navigate to the Utilities folder.</li>
  <li>Double click the Terminal application to open it.</li>
  <li>Type the following command when the terminal opens.
    <pre>$ ssh ${user.username}@${resource.hostname}.rcac.purdue.edu</pre>
    Replace <kbd>${user.username}</kbd> with your Purdue career account username and provide your password when prompted.
  </li>
 </ul>
 Once logged in use the [flost](../flost) tool as described above. The tool will guide you through the process and show you the commands necessary to retrieve your lost file.

