---
title: xCAT GoCD Overview
tags:
 - internal
---

# Halstead Cluster Configuration
This repository contains the necessary files and scripts to build, configure,
and deliver the Operating System for the Halstead Cluster. Two types of
Halstead OS images are built based on this repository, the Halstead Compute Node
image and the Halstead Frontend image.

Changes are made by teammates and submitted through GitHub. The changes in
GitHub are then detected by Go CD and trigger a build for the Frontend and
Compute OS images. The OS image is then tested for OS specific requirements,
followed by testing for cluster level requirements (ie. testpbs). If the
changes do not cause tests to fail, a sign that changes broke an OS image's or
cluster's functionality, Go CD delivers the images to xCAT. Finally OS images
are deployed to Halstead Frontend and Compute Nodes from xCAT. More details
about this process are provided in this document.

![Halstead Workflow Overview](/knowledge/internal/EngineeringTeam/images/Halstead-Workflow-Overview.png)

*An overview of how changes become OS images for xCAT.*

### Technology Overview
* [GitHub Enterprise](https://enterprise.github.com/home)
([docs](https://guides.github.com/))
([RCAC](https://github.rcac.purdue.edu/)) -
stores, records, and reviews changes submitted by team members.
* [Thoughtworks Go CD](https://www.thoughtworks.com/go/)
([docs](https://docs.go.cd/current/))
([RCAC](https://navis-b001.rcac.purdue.edu/)) -
builds, tests, and deplivers OS images.
* [xCAT](https://xcat.org/)
([docs](http://xcat-docs.readthedocs.io/en/stable/)) -
manages and deploys OS images and machines.

Currently the GoCD containers are executed as Docker containers on the navis
cluster (navis-b[000-005]). The agents are running under navis-b003 and
navis-b004. The GoCD website and master is running on navis-b001. All of these
are under the rcac.purdue.edu domain zone.

After a Pull Request is made to the RCAC-Staff/xCAT-Halstead-Configuration
repository, GoCD's polling check detects the new PR and initiates the pipeline
process to build and test an image. The first stage builds an xCAT image in
GoCD agents built inside Docker containers running CentOS6 bases. After the
OS images have been built, the second stage moves the images to Halstead-sys
and provisions the halstead-t nodes (xcat group cicd) to the test image. Once
provisioned the same stage executes several tests on the node itself to ensure
processes are running and the node is in a healthy state. The third stage
assumes the nodes are still booted into the image provisioned from stage 2 and
begins running our `pbstestsuite` tests on the test nodes. Upon successful
completion, stage 4 merges the GitHub Pull Request.

At the completion of the build pipeline, 2 additional pipelines begin the
process of importing the xCAT test images that were just built and renames them
to the production OS names from `test-<cluster>-<profile>...` to
`<cluster>-<profile>...` so that they are available for nodes to provision to.

# Contributing Changes to the Halstead Cluster
This section provides documentation on how to submit changes to the Halstead
Cluster and how to monitor them throughout the CI/CD process.

## The Goal and Philosophy
Our goal and philosophy for using GitHub, Go CD, and xCAT is to test every
change made to Halstead before they are applied to the Halstead Frontends and
Compute Nodes. This system is known as CI/CD
([Continuous Integration](http://martinfowler.com/articles/continuousIntegration.html),
[Continuous Delivery](http://martinfowler.com/bliki/ContinuousDelivery.html)),
and the idea is that every product delivered to the deployment framework (xCAT)
is automatically verified as a healthy OS image. This reduces the overall number
of unhealthy changes being deployed to the production cluster which in turn
reduces the overall time required to maintain the cluster.

## How to Contribute Changes to Halstead

### Overview

### GitHub Forking & Pull Requests

### Monitor Your Changes

### What to do When Changes Fail

### Did My Change Succeed?

### Best Practices When Contributing


## Halstead Repository Layout

### OS Profiles Overview

### OS Files

### OS Packages

### OS Configuration Files

### OS Tests

### CI/CD Scripts
