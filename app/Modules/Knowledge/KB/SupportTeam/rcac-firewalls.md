---
title: RCAC Firewalls Explained
tags:
 - internal
---

# RCAC Firewalls

Please note that this information is for our internal use. It should not be forwarded or conveyed verbatim.

## Compute Nodes

Compute nodes allow access from any RCAC IPv4 or IPv6 subnet to connect.

Those subnets are:

* 128.211.128.0/19 (Research Public)
* 128.210.189.0/24 (Staff Workstations)
* 128.210.9.0/24 (Datacenter Public)
* 172.18.0.0/16 (Research Private)
* 128.210.251.140/24 (Fortress)
* 172.30.120.0/24 (Fortress?)
* 172.30.122.0/24 (Fortress?)
* 172.30.121.0/24 (Fortress?)
* 2607:ac80:100::/40 (Research IPv6 Public - new)
* 2001:18e8:804::/47 (Research IPv6 Public - old)

The firewall rules are generally:

```
*filter
:INPUT ACCEPT [0:0]
:FORWARD ACCEPT [0:0]
:OUTPUT ACCEPT [0:0]
-A INPUT -i lo -j ACCEPT 
-A INPUT -m state --state RELATED,ESTABLISHED -j ACCEPT 
-A INPUT -p icmp -j ACCEPT
-A INPUT -m set --set rcac_ipv4 src -j ACCEPT
-A INPUT -j REJECT --reject-with icmp-host-prohibited
-A FORWARD -j REJECT --reject-with icmp-host-prohibited
COMMIT
```

## Frontends

Frontends allow Purdue West Lafayette IP addresses to connect to any service. They offer up a select number of services globally.

Purdue West Lafayette subnets are:

* 172.16.0.0/12 (Private)
* 10.0.0.0/8 (Private)
* 192.168.0.0/16 (Private)
* 128.10.0.0/16 (CS)
* 128.46.0.0/16 (ECN)
* 128.210.0.0/15 (Campus)
* 204.52.32.0/19 (AgIT)
* 2607:ac80::/32 (IPv6 - new)
* 2001:18e8:800::/44 (IPv6 - old)

The firewall rules are generally:

```
*filter
:INPUT ACCEPT [0:0]
:FORWARD ACCEPT [0:0]
:OUTPUT ACCEPT [0:0]
-A INPUT -i lo -j ACCEPT 
-A INPUT -m state --state RELATED,ESTABLISHED -j ACCEPT 
-A INPUT -p icmp -j ACCEPT
-A INPUT -m set --set campus_ipv4 src -j ACCEPT
-A INPUT -p tcp -m tcp --dport 22 -j ACCEPT (SSH)
-A INPUT -p tcp -m multiport --dports 80,300,443,2811,7512,50000:51000 -j ACCEPT (HTTP(S), Globus)
-A INPUT -p udp -m udp --dport 60000:61000 -j ACCEPT (Mosh)
-A INPUT -j REJECT --reject-with icmp-host-prohibited
-A FORWARD -j REJECT --reject-with icmp-host-prohibited
COMMIT
```

## ADMs

ADMs are like nodes but more restrictive given that Torque trusts requests from our networks more than outside of our networks.

The firewall rules:

```
*filter
:INPUT ACCEPT [0:0]
:FORWARD ACCEPT [0:0]
:OUTPUT ACCEPT [0:0]
-A INPUT -i lo -j ACCEPT 
-A INPUT -m state --state RELATED,ESTABLISHED -j ACCEPT 
-A INPUT -p icmp -j ACCEPT
-A INPUT -m set --set rcac_customer_ipv4 src -j REJECT --reject-with icmp-host-prohibited
-A INPUT -m set --set rcac_ipv4 src -j ACCEPT
-A INPUT -p tcp -m multiport --dports 9618,9700:9900 -j ACCEPT (Condor)
-A INPUT -j REJECT --reject-with icmp-host-prohibited
-A FORWARD -j REJECT --reject-with icmp-host-prohibited
COMMIT
```

Note the line: `-A INPUT -m set --set rcac_customer_ipv4 src -j REJECT --reject-with icmp-host-prohibited`

There are certain subnets set aside for us to host customer infrastructure in and they are blocked from talking with -adm and -sys hosts. Those subnets are:

* 172.18.128.0/19
* 172.18.11.0/24
* 128.211.150.0/23

