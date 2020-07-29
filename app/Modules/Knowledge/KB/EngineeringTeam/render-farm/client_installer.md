---
title: Client Installation
tags:
 - internal
---

# Render Farm Client Installation
Enabling a client machine to have access to the Renderfarm, we created a
Windows installer that handles installting Qube!. The reason for creating this
installer rather than instructing Windows users to install Qube! through
PipelineFX's installer is so that we can pre-configure the Qube! installation.

The custom installer installs Qube! 6.5 for Windows along with the following
files:
* Qube! Client Configuration - directs the client to the Renderfarm Supervisor,
render-adm
* Custom Maya submission forms - enables Purdue custom authentication for Maya
* Custom Houdini submission forms - enables Purdue custom authentication for
Houdini

If the Qube! client is installed using the standard PipelineFX installer, our
custom configuration and submissions scripts will need to be installed manually
using documentation from the Qube! Puppet module in our master RCAC Puppet
environment.

### Custom Configuration
This file is configured to direct the Qube! client to submit jobs through
the RCAC Renderfarm Supervisor, render-adm.

### Submission Scripts
These custom scripts were written to authenticate a user against the Purdue
ACMaint Career Accounts. All of our submission scripts follow the process of
requesting a user's career account credentials, sending them over SSL to a
CGI-Bin script, guarded by the Apache PAM module, that creates a one time
password (OTP), which is stored in a MySQL database on render-adm and sent back
to the Qube! client, and stores the OTP that it receives as metadata in the
submitted job. Any job submitted from a normal Qube! installation or Qube!
client without these custom submission scripts will be removed from the Qube!
Supervisor on render-adm.

### Building a New Qube! Client Installer
For creating the Windows installer, you must use Windows. These instructions
were taken from <https://superuser.com/a/923281>
1. Create the directory ``C:\Install`` --- This is where the installer will be
   created.
2. Extract the existing installer to another directory
3. Create a 7-Zip archive (``.7z``) containing the files extracted from step 2.
4. Copy this ``.7z`` file to your ``C:\Install`` directory created in step 1.
5. Download the LZMA SDK from the [7-Zip web
   site](http://www.7-zip.org/download.html)
6. Extract the LZMA SDK, and copy the file ``7zS2.sfx`` to ``C:\Install``
7. Using Notepad++, create a new text file named ``config.txt`` in
   ``C:\Install``, and make sure via the "Encoding Menu" that the file is
   encoded in UTF-8.
8. Copy the following code block in the ``config.txt`` file:
```
;!@Install@!UTF-8!
Title="RCAC Qube Client v6.7-1"
BeginPrompt="Do you want to install RCAC Qube Client v6.7-1?"
RunProgram="install-qube.bat"
;!@InstallEnd@!
```
9. Open a cmd window, and enter the following (replace ``installer-archive``
   with the name of the archive created in step 3):
```
cd \
cd Install
copy /b 7zS2.sfx + config.txt + <installer-archive>.7z RCAC-Qube-client.exe
```
10. Look in ``C:\Install`` to find the created executable, named
    ``RCAC-Qube-client.exe``.

For Linux and Mac, install based on the Puppet file locations for the custom
Maya and Houdini submission scripts.

### Qube! Client Installer Distribution
A copy of the Windows Qube! Client installer is located at
``/depot/itap/render-farm/RCAC-Qube-client.exe``. When distributing, upload the
installer to [FileLocker](https://filelocker.purdue.edu/) and share the file
with whomever requested it.
