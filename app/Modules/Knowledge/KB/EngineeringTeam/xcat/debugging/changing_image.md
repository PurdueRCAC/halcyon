---
title: Changing OS Image Files Without Rebuilding Image
tags:
 - internal
---

# Changing OS Image Files Without Rebuilding Image

Occassionally it is necessarry to rapidly test an xCAT OS image file changes
where taking the time to do a full OS image build is not possible. When this is
the case, it is possible to modify an existing xCAT OS image without having to
rebuild it.

### Overview
To provide a quick overview of the process, modifying an existing xCAT OS image
involves decompressing and extracting files from xCAT OS _rootimg_, modifying
files in the _rootimg_ directory, then packing and compressing the xCAT OS
_rootimg_ directory. This process avoids having to completely rebuild the xCAT
OS image by using an existing image to skip the **genimage** step, which is the
longest procedure in generating new xCAT OS images, and allowing a system
administrator to perform **postgenimage** actions or test new modifications.

# Procedure
* [Import xCAT OS image](#import-xcat-os-image)
* [Decompress xCAT OS _rootimg_](#decompress-xcat-os-rootimg)
* [Extract files from xCAT OS _rootimg_](#extract-files-from-xcat-os-rootimg)
* [Perform Modifications](#perform-modifications)
* [Pack xCAT OS _rootimg_](#pack-xcat-os-rootimg)
* [Deploy xCAT OS image](#deploy-xcat-os-image)

**BACKUP ANY FILES THAT WILL BE MODIFIED BEFORE CONTINUING**

**NOTE**: All actions will be performed on the server hosting the xCAT daemon.

### Import xCAT OS Image
**NOTE**: If the xCAT OS image already resides in the xCAT database, this step
can be skipped

An exported xCAT OS image should come as a `<filename>.gz` file. To import the
OS image, log into the host running the xCAT server and perform the following
command:

```bash
/opt/xcat/bin/imgimport <export os image>.gz
```

Verify that the xCAT OS image has been imported by listing all of the xCAT OS
images or listing only the new xCAT OS image if the name of the image is known.

```bash
/opt/xcat/bin/lsdef -t osimage # If the image name is not known
/opt/xcat/bin/lsdef -t osimage -o <xcat-image-name> # If the image name is known
```

### Decompress xCAT OS _rootimg_
To decompress the xCAT OS _rootimg_, locate where the xCAT OS image contents
are stored.

```bash
/opt/xcat/bin/lsdef -t osimage -i rootimgdir -o <xcat-image-name>
```

Navigate to the listed directory and investigate the contents. There should be
3 files at minimum:
* initrd-stateless.gz
* kernel
* rootimg.gz
The rootimg.gz is the xCAT _rootimg_ file which contains all of the files for
the root filesystem in the xCAT OS image. This is the file that modifications
will be made to.

The name of the _rootimg_ file can be incredibly misleading as it appears to be
a GZIP compressed file. The _rootimg_ file is in fact compressed in the GZIP
format, however to maintain consistency with utilities used by xCAT, the
_rootimg_ file should be decompressed using PIGZ.

```bash
cd <xcat-image-root-directory>
pigz -i rootimg.gz
```

The decompression process will leave an extensionless file named `rootimg`.
This file is the decompressed _rootimg_ file, which is the root filesystem
for the xCAT OS image packed into the CPIO archive format.

### Extract files from xCAT OS _rootimg_
After decompression, the _rootimg_ will be in the CPIO archive format. To
make changes to the xCAT OS image, the root file system will need to be
extracted using the CPIO utility. The CPIO utility will extract the archive
into the current working directory, so it will be necessarry to make a new
directory and move into it before executing the CPIO utility.

**NOTE**: The _rootimg_ is currently named `rootimg`, which is the default name
xCAT uses for the _rootimg_ directory. Either carefully name the new directory
that the extracted _rootimg_ will reside in, or follow the process xCAT uses by
renaming the _rootimg_ to `rootimg.cpio` and naming the directory for the
extracted _rootimg_ `rootimg`

```bash
cd <xcat-image-root-directory>
mv rootimg rootimg.cpio
mkdir rootimg
cd rootimg
/bin/cpio -i < ../rootimg.cpio
```

With the _rootimg_ extracted, the xCAT OS image's root file system is able to
be modified.

### Perform Modifications
After the xCAT OS _rootimg_ has been extracted, it is possible to make
modifications to the root filesystem of the xCAT OS image. Follow these
guidelines when modifying the xCAT OS _rootimg_:

* Anything outside of the _rootimg_ directory will not be placed in the
root filesystem of the xCAT OS image
* If a command needs to be executed in the _rootimg_, used `chroot` with the
command on the _rootimg_ directory. Example:  
```bash
chroot <xcat-image-root-directory>/rootimg chmod +x /bin/echo
```
* Special file permissions (ie. immutable, NETCAP, etc) will not copy with a
file into the final xCAT OS image

### Pack xCAT OS _rootimg_
Once the necessary modifications have been made on the xCAT OS image root
filesystem, the image will need to be packed and compressed once again. Luckliy
the xCAT utilities performs both the archiving and compressing of the
_rootimg_. To perform this action, only the xCAT OS image name is needed. The
end result will be a completed xCAT OS image that may be deployed to an xCAT
managed host.
```bash
/opt/xcat/sbin/packimage <xcat-image-name>
```

### Deploy xCAT OS image
With the xCAT OS image properly packed, it is safe and ready to be deployed
to any xCAT host. Assign the xCAT OS image to a host using `nodeset` and reboot
the host using `rpower` on the server hosting the xCAT daemon or log onto the
host to be rebooted and run `reboot`.
```bash
/opt/xcat/sbin/nodeset testhost-a000 osimage=<xcat-image-name>
/opt/xcat/bin/rpower testhost-a000 reset # OR
ssh testhost-a000 reboot
```
