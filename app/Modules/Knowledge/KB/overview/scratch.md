---
title: ${resource.name} Overview
tags:
 - scratch
---
# ${resource.name} Overview

For  ${resource.name}, each cluster is assigned a default <a href="https://en.wikipedia.org/wiki/Lustre_(file_system)">Lustre</a> or <a href="https://en.wikipedia.org/wiki/IBM_General_Parallel_File_System">GPFS</a> parallel filesystem. The parallel filesystems provide work-area storage optimized for a wide variety of job types, and are designed to perform well with data-intensive computations, while scaling well to large numbers of simultaneous connections.

${resource.name} currently consists of several redundant, high-availability disk spaces and is a central component of ITaP's research systems infrastructure.  All scratch tier resources are high-performance, large capacity, and subject to scheduled purging of old files.

## Gilbreth:

* Scratch filesystem for Gilbreth.
* Gilbreth scratch consists of 2.3PB of redundant, high-availability disk space.
* The quota on Gilbreth scratch is 200TB and 2,000,000 files.

## Brown:

* Scratch filesystem for Brown.
* Brown scratch consists of 3.4PB of redundant, high-availability disk space.
* The quota on Brown scratch is 200TB and 2,000,000 files.

## Halstead:

* Scratch filesystem for Halstead.
* Halstead scratch consists of 2.3PB of redundant, high-availability disk space.
* The quota on Halstead scratch is 100TB and 1,000,000 files.

## Rice:

* Scratch filesystem for Rice.
* Rice scratch consists of 1.7PB of redundant, high-availability disk space.
* The quota on Rice scratch is 100TB and 2,000,000 files.

## Snyder:

* Scratch filesystem for Snyder.
* Snyder scratch consists of 1.0PB of redundant, high-availability disk space.
* The quota on Snyder scratch is 100TB and 1,000,000 files.

*Files in scratch directories are not backed up or recoverable.* ITaP does not back up files in scratch directories. If you accidentally delete a file, old files are purged, or the filesystem crashes, they cannot be restored. All important files should be backed up to the [Fortress HPSS Archive](/storage/fortress/) on a regular basis.

If you need more space in your scratch directories, please contact us at <a href="mailto:rcac-help@purdue.edu">rcac-help@purdue.edu</a>.
