---
title: Cold Booting Puppet
tags:
 - internal
---

# Cold Booting Puppet

These are the basic steps for doing a cold boot of Puppet, as told by Scott Hicks.

1. Bring up core (houses the Puppet database and the MySQL server for Foreman)
2. Bring up caput (runs Foreman)
3. Bring up manus (manus-00 is required because it is the CA for certs---other manus machines are optional)
4. Bring up digitus (digitus is what makes Puppet come alive for the clients)
