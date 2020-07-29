---
title: 	Do I need to do anything to my firewall to access ${resource.name}?
tags:
 - diskstorage
 - linuxcluster
 - fortress
---

### Do I need to do anything to my firewall to access ${resource.name}?

{::if resource.name == Fortress}
Yes, any machines using HSI or HTAR must have all firewalls (local and departmental) configured to allow open access from the following IP addresses: 

<pre>128.211.138.40
128.211.138.41
128.211.138.42
128.211.138.43
128.211.138.44
128.211.138.45
128.211.138.46
128.211.138.47
128.211.138.48
</pre>

Firewall issues may manifest with error messages like "[put: Error -50 on transfer](../../data/puterror)." If you are unsure of how to modify your firewall settings, please consult with your department's IT support or the documentation for your operating system.  Access to Fortress is restricted to on-campus networks.  If you need to directly access Fortress from off-campus, please use the Purdue VPN service before connecting.
{::else}
No firewall changes are needed to access ${resource.name}. However, to access data through Network Drives (i.e., CIFS, "Z: Drive"), you must be on a Purdue campus network or connected through <a href="http://www.itap.purdue.edu/connections/vpn/">VPN</a>.
{::/}
