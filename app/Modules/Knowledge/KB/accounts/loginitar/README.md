---
title: Connecting to Weber
expandtoc: true
tags:
 - linuxclusteritar
---
## Windows:

### VPN
- Download and install the CISCO VPN client from [Purdue WebVPN](https://webvpn.purdue.edu). Your VPN client may periodically auto-patch itself with the latest security enhancements.

{::if user.username != myusername}

- For first time users, please request access to the CUI VPN by logging in to [manage your BoilerKey](https://www.purdue.edu/apps/account/BoilerKey/).

- Using the client, connect to the VPN at `reedvpn.itap.purdue.edu/cui`.
 
- Your login is your Purdue Career Account ID.

- Your password is generated at each login with the [BoilerKey](https://www.purdue.edu/apps/account/IAMO/BoilerKeyNew/Purdue_CareerAccount_BoilerKey.jsp).
{::/}

## Linux / Mac:

Login follows the same process as for Windows.  
- The ['OpenConnect' VPN client](http://www.infradead.org/openconnect/) can be used to connect to the VPN.


{::if user.username != myusername}
## Accessing the Weber Cluster

- To access the Weber cluster, open the standalone <a target = "_blank" href="https://www.cendio.com/thinlinc/what-is-thinlinc">ThinLinc</a> client and connect to `desktop.weber.rcac.purdue.edu`.

- Your user name and password will be the same combination that you use to access other cluster resources (and do not require two-factor authentication).
{::else}
<strong>Additional log in instructions may be available to you after signing in to this website in the upper right corner.</strong>
{::/}
