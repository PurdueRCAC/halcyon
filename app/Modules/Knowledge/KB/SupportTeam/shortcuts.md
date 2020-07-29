---
title: Creating Thinlinc Shortcuts
tags:
 - internal
---

# Creating Thinlinc Shortcuts

There are a few pieces to creating shortcuts in Thiblinc:

## 1) Desktop entry

The main piece you need is the desktop entry. This should look like this:

```
Type=Application
Name=<MyApp>
Comment=
Icon=/usr/share/desktop-cluster-directories/icons/<myapp>.png
Exec=<exec path here>
Path=
Terminal=false
StartupNotify=false
```

And be dropped into the directory `puppet/modules/frontend/files/usr/share/desktop-cluster-directories/<myapp>.desktop` for each cluster repo.

For front-end execution, the exec path should generally look like one of these:

* `/bin/bash -lc "module load ansys && logger -t modmenu ansys $USER && fluent"`
* `/bin/xfce4-terminal -e '/bin/bash -lc "module load vmd && logger -t modmenu vmd $USER && vmd"'`

You may need to experiment a bit to see what will work for the application. Some applications do not respond to being launched directly with bash. Some need to have the xfce4-terminal container to capture it. Be sure to include the 'modmenu' logger line.

For compute-node execution, the exec path should generally look like:

```
/bin/xfce4-terminal -e '/bin/bash -l /usr/share/desktop-cluster-directories/scripts/interactive.sh "nodes=1:ppn=24" "-x /apps/cent7/matlab/interactive.sh" "matlab-interactive"'
```

This executes the `scripts/interactive.sh` generic script which handles the dialog menus and the actual `qsub`. The first argument is a node request that is appropriate - generally should be one full node (you will need to adjust ppn for each cluster you copy to). 

The second argument is additional arguments to be passed to `qsub`. At a minimum this needs to have `-x /apps/cent7/<myapp>/interactive.sh`. The `-x` specifies a script we will create in a minute that qsub will execute instead of prompting for interactive input. 

The third argument is the logging string. This should be `<myapp>-interactive`. 

## 2) Icon

An appropriate icon should be found and dropped into `puppet/modules/frontend/files/usr/share/desktop-cluster-directories/icons/` and specified in the `Icon=` line in the desktop entry.

If possible, find the icon you see in the title bar of the application window. This may take some browsing and `find`ing of image files inside the /apps installation or `lsof`'ing. The file browser in Thinlinc is handy for looking for it.

## 3) Interactive script

If you are creating an Interactive node menu option you will need a simple script that is executed by qsub:

```
#!/bin/bash
# THIS FILE RUNS INTERACTIVE THINLINC SHORTCUT - DO NOT DELETE

module load matlab && matlab -desktop
```

and placed inside the root /apps/ installation for the software and specified as the `-x` argument above. Unfortunately, `qsub -x` does not seem to take arguments... or any shell-isms like `module` or `&&`, so this is necessary. Any other customizations to make the software work can also be added here. Logging is handled by the generic dialog interactive.sh script and the argument you feed it.
