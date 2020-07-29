---
title: Thinlinc on xCAT Managed Hosts
tags:
 - internal
---

# Thinlinc on xCAT Managed Hosts

Adding Thinlinc to the xCAT managed hosts requires installing the appropriate
Thinlinc packages to the OS image hosting Thinlinc and configuring the Thinlinc
service. Adding the packages is fairly striaght forward, on Halstead it
requires packages to be defined in the frontend packagelist. Here is the patch
for the Halstead frontend that references the necessary files from the base
directory of the xCAT Halstead configuration repository.

```bash
cd <halstead-xcat-repo>
patch -p1 <<EOF
diff --git a/xcat/netboot/rh/frontend.rhels6.x86_64.pkglist b/xcat/netboot/rh/frontend.rhels6.x86_64.pkglist
index 099c4f6..3d3641c 100644
--- a/xcat/netboot/rh/frontend.rhels6.x86_64.pkglist
+++ b/xcat/netboot/rh/frontend.rhels6.x86_64.pkglist
@@ -460,6 +460,9 @@ hpssa
 hpssacli
 hsi_htar
 htop
+httpd
+httpd-tools
+httpd-devel
 hunspell
 hunspell-en
 hwdata
@@ -850,6 +853,9 @@ mlnx-ofa_kernel-modules
 mlnx-ofed-all
 mlnxofed-docs
 mlocate
+mod_auth_pam
+mod_perl
+mod_ssl
 module-init-tools
 mosh
 mozilla-filesystem
@@ -1305,6 +1311,14 @@ teckit
 telnet
 texlive-texmf-context
 theora-tools
+thinlinc-rdesktop
+thinlinc-tladm
+thinlinc-tlmisc
+thinlinc-tlmisc-libs
+thinlinc-tlprinter
+thinlinc-vnc-server
+thinlinc-vsm
+thinlinc-webaccess
 time
 tk
 tk-devel
EOF
```

Once the packages are defined to install, Thinlinc needs to be configured. In
the xCAT configuration, configure the Thinlinc service to start on boot using
`chkconfig` as is done with several services. Next specify the files that need
to be placed to configure the master and slave Thinlinc servers. If all of the
frontend machines are the same type of Thinlinc server (master or agent), then
place the configuration file in the os profile's root directory where it will
be synchronised the OS image's root directory. If all of the machines using the
same OS image are not running the same Thinlinc service, then alternatives are
needed. One option is to place both Thinlinc configuration files in the xCAT
root configuration for frontend images and modify the startup service to
inspect the FQDN to determine which configuration file to use. The second
option include putting both Thinlinc configuration files on Halstead-sys and
using xCAT's Syncfiles mechanism to pull them in. Syncfiles has the ability to
specify what nodes or groups a file is synchronised to. In this case, the
syncfiles entry in
`<halstead-xcat-repo>/xcat/netboot/rh/frontend.rhels6.x86_64.synclist` would be
the following:

```
<halstead-sys-thinlinc-config-location>/master.conf -> (halstead-fe00) /etc/thinlinclocation/thinlinc.conf
<halstead-sys-thinlinc-config-location>/agent.conf -> (halstead-fe0[1-3]) /etc/thinlinclocation/thinlinc.conf
```

Syncfiles will then synchronise the appropriate Thinlinc configuration file to
the correct frontend.
