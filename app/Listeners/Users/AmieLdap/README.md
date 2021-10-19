## Amie (LDAP) Listener

This connects to the RCAC Amie LDAP to retrieve user info.

### Listens for

* `App\Modules\Users\Events\UserSyncing`
* `App\Modules\Queues\Events\AllocationCreate`

### How It Works

1. A script on amie.anvil.rcac.purdue.edu reads packets from XSEDE's AMIE and imports the info into the Research Computing AMIE LDAP, which is the data source for this plugin.
2. The same script will POST either an XSEDE project ID (`x-xsede-pid: PEB215459`) or XSEDE project dn (`dn: x-xsede-pid=PEB215459,ou=Projects,dc=anvil,dc=rcac,dc=purdue,dc=edu`) to https://www.rcac.purdue.edu/api/allocations.
3. That endpoint can be found in the Queues module and triggers the `App\Modules\Queues\Events\AllocationCreate` event with the POSTed data.
4. This plugin listens for the event and performs the following actions:
    * Look up the XSEDE project info from the provided data.
    * Check if an associated group exists. If not, creates one.
    * Checks if the XSEDE project has a (unix) group. If so, creates a unix group in the portal. For every user in the group:
        * Checks if the user has an account in the portal. If not creates one.
        * Adds the user to the unix group if nto already a member.
        * Triggers the `App\Modules\Users\Events\UserSync` event that the Authprimary plugin responds to.
    * Checks if a queue exists. If not, creates one.
        * If the XSEDE project has any `serviceUnits`, it adds those to the portal as loans to the queue.
    * Checks if a storage space exists. If not, creates one.

#### Feeding AMIE LDAP

The script on amie.anvil.rcac.purdue.edu is at:

```
/opt/amieclient/local
```

Log file can be found at:

```
/var/log/amie_process
```

#### Sample AMIE LDAP Entries

Project:

```
# PEB215459, Projects, anvil.rcac.purdue.edu
dn: x-xsede-pid=PEB215459,ou=Projects,dc=anvil,dc=rcac,dc=purdue,dc=edu
objectClass: x-xsede-xsedeProject
objectClass: x-xsede-xsedePerson
objectClass: posixAccount
objectClass: inetOrgPerson
objectClass: top
x-xsede-recordId: 87665808
x-xsede-pid: PEB215459
uid: x-tannazr
x-xsede-resource: test-resource1.purdue.xsede
x-xsede-startTime: 20210415000000Z
x-xsede-endTime: 20220415000000Z
x-xsede-serviceUnits: 1
description: Lorem ipsum dolor est...
title: Lorem Ipsum
x-xsede-personId: x-tannazr
givenName:: VEFOTkFaIA==
sn: REZAEI DAMAVANDI
cn: TANNAZ  REZAEI DAMAVANDI
o: California State Polytechnic University, Pomona
departmentNumber: COMPUTER SCIENCE
mail: tannazr@cpp.edu
telephoneNumber: 9499297548
street: 2140 WATERMARKE PLACE
l: IRVINE
st: California
postalCode: 92612
co: United States
x-xsede-userDn: /C=US/O=Pittsburgh Supercomputing Center/CN=TANNAZ REZAEI DAMA
VANDI
x-xsede-userDn: /C=US/O=National Center for Supercomputing Applications/CN=TAN
NAZ REZAEI DAMAVANDI
x-xsede-gid: x-peb215459
gidNumber: 7000060
uidNumber: 7000006
homeDirectory: /home/x-tannazr
```

Project group:

```
# x-asc170016, Groups, anvil.rcac.purdue.edu
dn: cn=x-asc170016,ou=Groups,dc=anvil,dc=rcac,dc=purdue,dc=edu
cn: x-asc170016
gidNumber: 7000213
memberUid: x-tg457046
memberUid: x-zhao4
memberUid: x-psmith
memberUid: x-woojungha
memberUid: x-zhuxiao
memberUid: x-rkalyana
memberUid: x-colbykd
memberUid: x-rice1
memberUid: x-thompscs
memberUid: x-kelley
memberUid: x-ayounts
memberUid: x-yirugi
memberUid: x-gorenstein
memberUid: x-wu2
memberUid: x-lentner
memberUid: x-hong
memberUid: x-hillery
memberUid: x-maji
memberUid: x-schwarz
memberUid: x-adams
memberUid: x-mertes
memberUid: x-zhang
memberUid: x-yoder
objectClass: posixGroup
objectClass: top
```
