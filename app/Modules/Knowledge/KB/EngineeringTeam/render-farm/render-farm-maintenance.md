---
title: Render Farm Maintenance
tags:
 - internal
---

# Render Farm Maintenance

## Setting Up A Workstation for Testing
Install on your workstation:
* [Houdini](https://www.sidefx.com/get/download-houdini)
* [Maya](https://www.autodesk.com/education/free-software/maya)
* [Qube](https://www.pipelinefx.com/downloadversions) (select the client
  install in the installer)

Move the following files from the Puppet tree to your workstation directories.
The ``.erb`` template files have a template value (``qube::params::supervisor_auth_url``)
that needs to be replaced with the Qube Supervisor Auth URL
(``https://render-adm.rcac.purdue.edu/cgi-bin/qube_auth.py``):
* ``modules/qube/templates/submit_maya.py.erb`` => ``/Applications/pfx/qube/api/python/qb/gui/simplecmds/submit_maya.py``
* ``modules/qube/templates/submit_maya.py.erb`` => ``/Applications/pfx/qube/qube.app/Contents/Resources/simplecmds/submit_maya.py``
* ``modules/qube/templates/submit_houdini.py.erb`` => ``/Applications/pfx/qube/qube.app/Contents/Resources/simplecmds/submit_houdini.py``
* ``modules/qube/templates/submit_houdini.py.erb`` => ``/Applications/pfx/qube/api/python/qb/gui/simplecmds/submit_houdini.py``
* ``modules/houdini/files/qbSubmitRop.otl`` => ``~/Library/Preferences/houdini/[VERSION]/otls/qbSubmitRop.otl``
  * On Linux, this goes in ``$HOME/houdini[VERSION]/asset_store/otls/``
* ``modules/houdini/files/qbSubmitRop.otl`` => ``/Applications/pfx/qube/api/python/qb/gui/AppUI/pyHoudini/qbSubmitRop.otl``
* ``modules/houdini/files/qbSubmitRop.otl`` => ``/Applications/pfx/qube/qube.app/Contents/Resources/AppUI/pyHoudini/qbSubmitRop.otl``

To submit jobs, please follow the documentation in the
[User Manual](https://github.rcac.purdue.edu/RCAC-Staff/www/blob/master/knowledge/KB/EngineeringTeam/render-farm/RenderFarmHowTo.pdf).

To download the RPMs without having to use the Qube installer,
go here: <http://repo.pipelinefx.com/downloads/pub/>

## Updating Qube License
To request a new license, contact Laura Theademan who will contact PipelineFX
representative Michelle Ray (michelle@pipelinefx.com) to purchase and retrieve
the new license.

Once a new license has been acquired, replace the license file in the Puppet
configuration environment (``modules/qube/files/qb.lic``) with the new license.
Puppet will automatically update the license file on all of the render nodes
(the ADM and the workers).

Once updated, restart the ``supervisor`` service and run ``/usr/local/pfx/qube/bin/qbping``

For additional information on Qube licensing, visit their
[license installation page](http://docs.pipelinefx.com/display/QUBE/License+Installation).

## Updating Qube
Download the Qube installer from [PipelineFX](https://www.pipelinefx.com/downloadversions).
Go through the installer, select a directory when it prompts you, select the
most recent version, then you are brought to the "Component Selection" screen.
Select "Download Only" at the very bottom, and from the drop-down menu, select
the appropriate operating system (for our RHEL6 systems, select
``CENTOS-6.6-x86_64``). Press continue, and it will download all of the RPMs
to the directory selected earlier. Once the download is complete, close the
downloader and move all of the RPMs into the RHEL6 software Yum repository
(directory: ``/usr/rmt_share/packages/rcac_software/rhel6-software``). The
mirror will automatically include them, then Puppet will automatically install
the latest version of the Qube Supervisor and Workers.

## Updating Maya
Message Autodesk Support and ask for a download link. Don't bother looking for
it yourself.
Unzip this application and store it in ``/depot/itap/maya[version]``.
The version number is then defined in Puppet Hiera for a host or resource under
the value ``maya_version``. In the Puppet repository, under the Maya module are
``rcac_install`` files. ``rcac_install.sh`` will be placed under the ``maya``
directory.

The required subfolder view is as follows:
* ``/depot/itap/maya[version]``
    * maya
        * miscellaneous files
        * ``rcac_install.sh``

Remove ``/usr/local/bin/maya`` to trigger the Maya install Puppet exec.

Once the Hiera value has been submitted and the Maya module applied, Puppet will
execute the installer. The new version of Maya will be installed alongside any
older version of Maya. If you wish to remove the older version of Maya, remove
``/usr/autodesk/maya[old-version]`` from the compute node.

Note that Mental Ray has been discontinued by Nvidia, and the CGT people only want
to use the renderer named Arnold.

## Updating Houdini (using Puppet)
Download the latest version of Houdini from
[SideFX](https://www.sidefx.com/download)
and untar its contents at ``/depot/itap/render-farm/houdini``. A directory will
be extracted. Make it look like ``houdini-<version>-linux_x86_64``. Copy the version
and place it into the Hiera key ``houdini_version`` in params.pp in Puppet for a host or
resource using the Houdini Puppet module. Puppet will automatically install the
new version of Houdini.

To remove old versions of Houdini, go to the directory
``/opt/hfs<old-houdini-version>`` on the host, and execute the program
``houdini.uninstall``. Then run ``rm -rf /opt/hfs<old-houdini-version>``

## Providing Access to Users
Contact ``accounts@purdue.edu`` to create an RCAC account for the user and add
the account to the group ``cgt-render`` and the host ``render-adm.rcac.purdue.edu``.

## Adding New Workers
TODO

## Removing Workers
TODO

## Starting/Stopping Qube Services
Service names are ``supervisor`` that runs on the ADM and ``worker`` for the
compute nodes. They have SysV init scripts that work just like everything else.

## Rebuilding the Render Farm
TODO
