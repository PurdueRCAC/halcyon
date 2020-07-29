---
title: Globus
tags:
 - linuxcluster
 - diskstorage
 - tapestorage
---

# Globus

<em>Globus</em>, previously known as Globus Online, is a powerful and easy to use file transfer service for transferring files virtually anywhere. It works within ITaP's various research storage systems; it connects between ITaP and remote research sites running Globus; and it connects research systems to personal systems. You may use Globus to connect to your home, scratch, and Fortress storage directories. Since Globus is web-based, it works on any operating system that is connected to the internet. The Globus Personal client is available on Windows, Linux, and Mac OS X. It is primarily used as a graphical means of transfer but it can also be used over the command line.

### Globus Web:

<ul>
 <li>Navigate to <a href=http://transfer.rcac.purdue.edu target="_blank" rel="noopener">http://transfer.rcac.purdue.edu</a></li>
 <li>Click "Proceed" to log in with your Purdue Career Account.</li>
 <li>On your first login it will ask to make a connection to a Globus account. If you already have one - sign in to associate with your Career Account. Otherwise, click the link to create a new account.</li>
 <li>Now you're at the main screen. Click "File Transfer" which will bring you to a two-endpoint interface.</li>
 <li>You will need to select one endpoint on one side as the source, and a second endpoint on the other as the destination. This can be one of several Purdue endpoints or another University or your personal computer (see Personal Client section below).</li>
</ul>

The ITaP Research Computing endpoints are as follows. A search for "Purdue" will give you several suggested results you can choose from, or you can give a more specific search.

<ul>
{::if resource.type == compute}
 <li>Home Directory storage: "Purdue Research Computing - Home Directories",  however, you can start typing "Purdue" or "Home Directories" and it will suggest appropriate matches.</li>
 <li>${resource.name} scratch storage: "Purdue ${resource.name} Cluster",  however, you can start typing "Purdue" or "${resource.name} and it will suggest appropriate matches. From here you will need to navigate into the first letter of your username, and then into your username.</li>
{::/}
 <li>Research Data Depot: "Purdue Research Computing - Data Depot", a search for "Depot" should provide appropriate matches to choose from.</li>
 <li>Fortress: "Purdue Fortress HPPS Archive", a search for "Fortress" should provide appropriate matches to choose from.</a></li>
</ul>

From here, select a file or folder in either side of the two-pane window, and then use the arrows in the top-middle of the interface to instruct Globus to move files from one side to the other. You can transfer files in either direction. You will receive an email once the transfer is completed.

### Globus Personal Client setup:

<ul>
 <li>On the endpoint page from earlier, click "Get Globus Connect Personal" or download it from here: <a href="https://www.globus.org/globus-connect-personal">Globus Connect Personal</a></li>
 <li>Name this particular personal system and click "Generate Setup Key" on this page: <a href="https://www.globusonline.org/xfer/ManageEndpoints?globus_connect=true">Create Globus Personal endpoint</a></li>
 <li>Copy the key and paste it into the setup box when installing the client for your system.</li>
 <li>Your personal system is now available as an endpoint within the Globus transfer interface.</li>
</ul>

### Globus Command Line:

Globus supports command line interface, allowing advanced automation of your transfers.

To use the recommended standalone Globus CLI application (the <kbd>globus</kbd> command):
<ul>
 <li> First time use: issue the <kbd>globus login</kbd> command and follow instructions for initial login.</li>
 <li>Commands for interfacing with the CLI can be found via <a href="https://docs.globus.org/cli/">Using the Command Line Interface</a>, as well as the <a href="https://docs.globus.org/cli/examples/">Globus CLI Examples</a> pages.</li>
</ul>


### Sharing Data with Outside Collaborators

Globus allows convenient sharing of data with outside collaborators. Data can be shared with collaborators' personal computers or directly with many other computing resources at other intstitutions. See the Globus documentation on how to share data:

<ul>
 <li><a href="https://docs.globus.org/how-to/share-files/">https://docs.globus.org/how-to/share-files/</a>
</ul>

For links to more information, please see <a href="https://support.globus.org/home">Globus Support</a> page.
