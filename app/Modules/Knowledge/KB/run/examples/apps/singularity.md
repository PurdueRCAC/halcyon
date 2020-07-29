---
title: Singularity
tags:
 - slurm
 - wholenode
 - sharednode
 - workbench
---

# Singularity

**Note:** Singularity was originally a project out of [Lawrence Berkeley National Laboratory](https://lbl.gov). It has now been spun off into a distinct offering under a new corporate entity under the name [Sylabs Inc](https://sylabs.io). This guide pertains to the open source community edition, _SingularityCE_.


## What is Singularity?
Singularity is a new feature of the Community Clusters allowing the portability and reproducibility of operating system and application environments through the use of Linux containers. It gives users complete control over their environment.

Singularity is like Docker but tuned explicitly for HPC clusters. More information is available from the [project’s website](https://sylabs.io/singularity).

### Features
* Run the latest applications on an Ubuntu or Centos userland
* Gain access to the latest developer tools
* Launch MPI programs easily
* Much more

Singularity’s user guide is available at: [sylabs.io/guides/2.6/user-guide](https://sylabs.io/guides/2.6/user-guide/)

### Example
Here is an example using an Ubuntu 16.04 image on ${resource.name}:
```
$ singularity exec /depot/itap/singularity/ubuntu1604.img cat /etc/lsb-release
DISTRIB_ID=Ubuntu
DISTRIB_RELEASE=16.04
DISTRIB_CODENAME=xenial
DISTRIB_DESCRIPTION="Ubuntu 16.04 LTS"
```

Here is another example using a Centos 7 image:
```
$ singularity exec /depot/itap/singularity/centos7.img cat /etc/redhat-release
CentOS Linux release 7.2.1511 (Core) 
```

## Purdue Cluster Specific Notes
All service providers will integrate Singularity slightly differently depending on site. The largest customization will be which default files are inserted into your images so that routine services will work.

Services we configure for your images include DNS settings and account information.
File systems we overlay into your images are your home directory, scratch, Data Depot, and application file systems.

Here is a list of paths:
* /etc/resolv.conf
* /etc/hosts
* /home/$USER
* /apps
* /scratch
* /depot

This means that within the container environment these paths will be present and the same as outside the container. The `/apps`, `/scratch`, and `/depot` directories will need to exist _inside_ your container to work properly.

### Creating Singularity Images
Due to how singularity containers work, you must have root privileges to _build_ an image. Once you have a singularity container image built on your own system, you can copy the image file up to the cluster (you do not need root privileges to _run_ the container).

You can find information and documentation for how to install and use singularity on your system:

* [Install Singularity on Windows](https://sylabs.io/guides/2.6/user-guide/installation.html#install-on-windows)
* [Install Singularity on macOS](https://sylabs.io/guides/2.6/user-guide/installation.html#install-on-mac)
* [Install Singularity on Linux](https://sylabs.io/guides/2.6/user-guide/installation.html#install-on-linux)

We have version `2.6.1-dist` on the cluster. You will most likely not be able to run any container built with any singularity past that version (i.e., version 3). So be sure to follow the installation guide for version 2.6 on your system.

```sh
$ singularity --version
2.6.1-dist
```

Everything you need on how to [build a container](https://sylabs.io/guides/2.6/user-guide/build_a_container.html) is available from their user-guide. Below are merely some quick tips for getting your own containers built for ${resource.name}.

You can use a [Container Recipe](https://sylabs.io/guides/2.6/user-guide/container_recipes.html#container-recipes) to both build your container and share its specification with collaborators (for the sake of reproducibility). Here is a simplistic example of such a file:
```
# FILENAME: Buildfile

Bootstrap: docker
From: ubuntu:18.04

%post
    apt-get update && apt-get upgrade -y
    mkdir /apps /depot /scratch
```

To build the image itself:
```
$ sudo singularity build ubuntu-18.04.simg Buildfile
```

The challenge with this approach however is that it must start from scratch if you decide to change something. In order to create a container image iteratively and interactively, you can use the `--sandbox` option.
```
$ sudo singularity build --sandbox ubuntu-18.04 docker://ubuntu:18.04
```

This will not create a flat image file but a directory tree (i.e., a folder), the contents of which are the container's filesystem. In order to get a shell inside the container that allows you to modify it, user the `--writable` option.
```
$ sudo singularity shell --writable ubuntu-18.04
Singularity: Invoking an interactive shell within container...

Singularity ubuntu-18.04.sandbox:~>
```

You can then proceed to install any libraries, software, etc. within the container. Then to create the final image file, `exit` the shell and call the `build` command once more on the _sandbox_.
```
$ sudo singularity build ubuntu-18.04.simg ubuntu-18.04
```

Finally, copy the new image to ${resource.name} and run it.
