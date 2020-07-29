---
title: BoilerKey
tags:
 - internal
---

# BoilerKey 2-Facter Authentication
[IAM's BoilerKey](https://www.purdue.edu/apps/account/flows/BoilerKey) system
is a 2-Facter (2FA) system connected to Purdue's Career Accounts. This 2FA
ensures that user authentication to any BoilerKey protected service uses 2
passwords: a secure pin and a one time password (OTP) that changes every
minute. Our intent is to implement the BoilerKey 2FA system on our systems to
further secure our resources.

As a note, RCAC is currently implementing BoilerKey 2FA for sudo level access.
Puppet is configured to enable either password file sudo authentication (old
system) or BoilerKey 2FA authentication. Eventually this configuration may
extend to more than sudo level authentication.

For more information on BoilerKey, please visit
[IAM's BoilerKey FAQ](https://www.purdue.edu/apps/account/IAMO/BoilerKeyNew/Purdue_CareerAccount_BoilerKey_FAQ.jsp)


# BoilerKey 2-Facter Authentication
Whenever an authentication request enters a Linux system, the request is
processed by the Linux Pluggable Authentication Modules. BoilerKey utilizes
a service known as [FreeRADIUS](freeradius.org) maintained by IAM's ACMAINT
and processes Linux requests starting at a Linux client through a PAM library
called 'pam_radius'. IAM does not want all of our resources to query their
FreeRADIUS directly, so we have setup Proxy FreeRADIUS servers on the Manus
servers to handle incoming requests from our resources. This results in sudo
level requests from a system administrator being processed by the PAM RADIUS
module, which forwards the authentication request to the FreeRADIUS Proxy
server on the Manus servers, which upon authorization of the server the request
originated from forwards the request to IAM's FreeRadius Servers where a push
notification is sent or the OTP is verified. The result of the request is then
sent back up this server chain to the originating server where, depending on
whether the request was approved or not, the sudo command is executed or
halted.

![BoilerKey Authentication Chain](/knowledge/internal/EngineeringTeam/images/BoilerKeyAuthenticationChain.png)


# Configuring Sudoers on a Puppet Resources to Use BoilerKey 2FA
The central RCAC Puppet tree consists of a module called 'BoilerKey' that
provides the configuration necessary for enabling BoilerKey on sudoers
commands. During the transition, this module is included through the
'base_rcac' class if a hiera key 'sudo_authentication' is set to 'boilerkey'
(`sudo_authentication: boilerkey`), however if 'sudo_authentication' is not
set through Hiera, the password file based authentication is enabled for
sudoers. Once the transition is complete for all of our central RCAC Puppet
managed resources, this Hiera based mechanism for BoilerKey/password file
authentication should be removed and set to use BoilerKey only. Once the module
is included, packages will be installed and configuration file templates will
be finalized and set on the defined resources.

To authorize resources to authenticate with BoilerKey through our FreeRADIUS
Proxies, create a secret key and collect the IPv4 CIDR's of the resources. It
is expected that there will be multiple CIDR definitions for various resources
(eg. clusters). This will only result in multiple definitions with the same
secret on the FreeRADIUS Proxies. With the CIDR and secret in hand, access the
Corpus Backend resource Hiera file 
(`<puppet>/hieradata/resource/corpus/ipvs-be.yaml`) and add the following to
the `boilerkey_proxy_clients` dictionary:

```yaml
    host_<resource>_<cidr-summary>:
        iprange: <cidr>
        secret: <eyaml-encrypted-secret>
```

For example:

```yaml
    host_halstead_servers:
        iprange: 128.211.148.0/27
        secret: ENC[PKCS7,abc123]
    host_halstead_nodes:
        iprange: 172.18.48.0/22
        secret: ENC[PKCS7,abc123]
```

Next add the following to the Hiera file for the desired resources needing
BoilerKey 2FA. Note this is assuming the FreeRADIUS servers are behind an IPvS
set of servers.

```yaml
boilerkey_server: <ipvs-fe>
boilerkey_secret: <eyaml-encrypted-secret>
sudo_authentication: boilerkey
```
**NOTE**: IPvS is currently changing; interim server is manus-03.rcac.purdue.edu.

For example:

```yaml
# <puppet>/hiera/resources/halstead.yaml
boilerkey_server: manus-03.rcac.purdue.edu
boilerkey_secret: ENC[PKCS7,abc123]
sudo_authentication: boilerkey
```

Let Puppet run on the FreeRADIUS Proxy servers and on the client to apply the
new changes. Test with `sudo -k echo 'It works!'` and using your BoilerKey 2FA
authentication and with your old sudoers password. If the BoilerKey 2FA fails,
double check the Puppet configurations are in place.


# Manually Configuring Sudoers on a Resource to Use BoilerKey 2FA
Follow the steps for configuring a Puppet configured resources through adding
the resources secret and CIDR to the `corpus/ipvs-be.yaml` Hiera file. Run
Puppet on the FreeRADIUS Proxy servers.

With another configuration framework (eg. xCAT) or by hand, install the package
`pam_radius` on the resources of focus. Next add the file
`/etc/pam_radius.conf` to each resource with the following contents:

```
# server[:port]    shared_secret       timeout(s)
<ipvs-fe>:1812     <unecrypted-secret> 25
```

For example:
```
# server[:port]    shared_secret       timeout(s)
manus-03.rcac.purdue.edu:1812    abc123    25
```

Next modify the sudo PAM file to authenticate with the PAM RADIUS module.
Modify the file `/etc/pam.d/sudo` to the following:
```
#PAM-1.0
auth      required    pam_radius_auth.so
account   include     system-auth
```

The manually configured resources should now be able to authenticate using
BoilerKey 2FA. Test with `sudo -k echo 'It works!'` and using your BoilerKey 
2FA authentication and with your old sudoers password. If the BoilerKey 2FA
fails, double check the Puppet configurations are in place.


# Enabling BoilerKey 2FA for Another Level of Authentication

To enable any other service to authenticate through BoilerKey 2FA, follow the
steps above to configure the resources. The primary difference between setting
up the sudo service and any other service to authenticate through BoilerKey 2FA
are the PAM.D configuration files. The magic line that needs to be added to any
relevant PAM.D configuration file is the `auth` statement. In the PAM.D
configuration file for the service that will use BoilerKey, replace the
existing `auth  required` lines with the following:

```
auth    required   pam_radius_auth.so
```

For example, modifying SSHD on our systems to use BoilerKey, change the
following line in ``/etc/pam.d/sshd`` from:

```
auth     required  pam_sepermit.so
```

to

```
auth     required  pam_radius_auth.so
```

This instructs PAM.D to forward SSHD authentication requests to the configured
FreeRADIUS server to authenticate using BoilerKey credentials. In Puppet this
will have to be modified in the modules that configure the SSHD PAM.D file.

**NOTE**: This change is un-tested and does not fully ensure proper
configuration. It is meant as an example only. RCAC environments may require
additional changes to the `/etc/pam.d/password-auth` file.
