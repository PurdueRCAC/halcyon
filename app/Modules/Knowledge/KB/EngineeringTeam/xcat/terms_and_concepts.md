---
title: xCAT Concepts and Terminology
tags:
 - internal
---

# xCAT Concepts and Terminology
This will provide information about what xCAT is, some of the terminology and
concepts around it, and what various features xCAT provides.

# xCAT Terminology

* xCAT definitions
* node definition
* osimage
* node
* provmethod

# xCAT Cheat Sheet
Here are a handful of useful commands and tips to use when working with xCAT.

```
# === xCAT General Command Tips

# Listing all entries under an xCAT definition (defaults to node)
# -- Most common xCAT definitions we use are osimage and node
/opt/xcat/bin/lsdef -t <definition>

# Listing a single entry under an xCAT definition
# -- The -o <object-name> flag provides the name of an object under the definition
# -- The -o in the -o <object-name> flag is automatically assumed if there is a
#       parameter without a flag (eg. lsdef -t osimage centos-7-test-1)
# -- Multiple objects can be listed using commas (eg. <object-1-name>,<object-2-name>)
/opt/xcat/bin/lsdef -t <definition> -o <object-name>

# Listing an attribute
# -- Just like the -o flag, multiple attributes can be requested with a comma separated list
# -- If multiple objects and attributes are given (-o obj1,obj2 -i atr1,atr2),
#       the listed attributes will be listed for each of the list objects
/opt/xcat/bin/lsdef -t <definition> -o <object-name> -i <attribute-name>

# Listing attributes for multiple objects formatted for dshbak
# -- -c tells lsdef to list each attribute on a new line with the object name
#       as the prefix (eg. clustername-a256: status=booted)
/opt/xcat/bin/lsdef -t <definition> -o <object-name> -i <attribute-name> -c | dshbak -c


# === xCAT OS Images

# Listing xCAT OS Images
/opt/xcat/bin/lsdef -t osimage

# List a specific xCAT OS Image
/opt/xcat/bin/lsdef -t osimage -o <os-tmage>


# === xCAT Nodes

# List all defined xCAT nodes
/opt/xcat/bin/lsdef -t node

# List a single node
/opt/xcat/bin/lsdef -t node -o <node-name>

# List a group of nodes
/opt/xcat/bin/lsdef -t node -o <pdsh-node-definition>

# List the current OS xCAT assigned to a node
/opt/xcat/bin/lsdef -t node -o <node-name> -i provmethod
```
