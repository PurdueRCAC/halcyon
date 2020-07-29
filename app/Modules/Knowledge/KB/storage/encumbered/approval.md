---
title: File Transfer Approval
tags:
 - reed
---

### Overview

All file transfer operations out of the REED environment must be approved by two separate project team members.  One member moves the file into a staging area or airlock, and another must authorize the transfer.

### File server names
From within the REED environment, your file server will be named with your Project's name in lower case, followed by `-out.purduereed.lcl`.  From an external workstation connected through the REED VPN, the Project name will be followed by `out.reed.rcac.purdue.edu`

So, if your Project's name is 'Proj-1', your outgoing server will be named `proj-1-out.purduereed.lcl` from within REED, and will be named `proj-1-out.reed.rcac.purdue.edu` from an external workstation.  Remember that an external workstation must be connected through the VPN to authorize transfers.

### To authorize a transfer:
* Connect to your Project's outgoing file server using an SSH client.  PuTTY is available within REED.
* Login using your REED credentials.
* Run the `approve_files` script.  Read all prompts thoroughly.

You will need to know the REED user ID of the person requesting approval

Once the script finishes, the files will be accessible for three days from outside REED, in the `out_approved` directory.

