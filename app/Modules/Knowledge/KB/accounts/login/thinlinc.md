---
title: ThinLinc
tags:
 - linuxcluster
---
# ThinLinc

ITaP Research Computing provides <a target = "_blank" href="https://www.cendio.com/thinlinc/what-is-thinlinc"><em>ThinLinc</em></a> as an alternative to running an X11 server directly on your computer. It allows you to run graphical applications or graphical interactive jobs directly on ${resource.name} through a persistent remote graphical desktop session.

ThinLinc is a service that allows you to connect to a persistent remote graphical desktop session. This service works very well over a high latency, low bandwidth, or off-campus connection compared to running an X11 server locally. It is also very helpful for Windows users who do not have an easy to use local X11 server, as little to no set up is required on your computer.

There are two ways in which to use ThinLinc: preferably through the native client or through a web browser.

### Installing the ThinLinc native client

The native ThinLinc client will offer the best experience especially over off-campus connections and is the recommended method for using ThinLinc. It is compatible with Windows, Mac OS X, and Linux.

<ul>
 <li>Download the ThinLinc client from the <a target = "_blank" href="https://www.cendio.com/thinlinc/download">ThinLinc website</a>.</li>
 <li>Start the ThinLinc client on your computer.</li>
 <li>In the client's login window, use <kbd>desktop.${resource.frontend}.rcac.purdue.edu</kbd> as the Server. Use your Purdue Career Account username and password.</li>
 <li>Click the Connect button.</li>
 <li>Continue to following section on connecting to ${resource.name} from ThinLinc.</li>
</ul>

### Using ThinLinc through your web browser

The ThinLinc service can be accessed from your web browser as a convenience to installing the native client. This option works with no set up and is a good option for those on computers where you do not have privileges to install software. All that is required is an up-to-date web browser. Older versions of Internet Explorer may not work.

<ul>
 <li>Open a web browser and navigate to <a href="https://desktop.${resource.frontend}.rcac.purdue.edu"> <kbd>desktop.${resource.frontend}.rcac.purdue.edu</kbd>.</a></li>
 <li>Log in with your Purdue Career Account username and password.</li>
 <li>You may safely proceed past any warning messages from your browser.</li>
 <li>Continue to the following section on connecting to ${resource.name} from ThinLinc.</li>
</ul>

### Connecting to ${resource.name} from ThinLinc

<ul>
 <li>Once logged in, you will be presented with a remote Linux desktop running directly on a cluster front-end.</li>
 <li>Open the terminal application on the remote desktop.
 <li>Once logged in to the ${resource.name} head node, you may use graphical editors, debuggers, software like Matlab, or run graphical interactive jobs. For example, to test the X forwarding connection issue the following command to launch the graphical editor <kbd>gedit</kbd>:
    <pre>$ gedit</pre></li>
 <li>This session will remain persistent even if you disconnect from the session. Any interactive jobs or applications you left running will continue running even if you are not connected to the session.</li>
</ul>

### Tips for using ThinLinc native client
<ul>
 <li>To exit a full screen ThinLinc session press the <kbd>F8</kbd> key on your keyboard (<kbd>fn + F8 key</kbd> for Mac users) and click to disconnect or exit full screen.</li>
 <li>Full screen mode can be disabled when connecting to a session by clicking the Options button and disabling full screen mode from the Screen tab.</li>
</ul>
