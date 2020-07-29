---
title: Installing R packages
tags:
 - slurm
 - wholenode
 - sharednode
 - workbench
---
### CRAN

The <a href="https://cran.r-project.org/mirrors.html" target="_blank">Comprehensive R Archive Network (CRAN)</a> is network of websites that host the collection of R packages.  The collection is mirrored on sites hosted all over the world to ensure access for anyone who wishes to use it.  Anyone can use packages or contribute to CRAN so long as they follow the CRAN repository policies.

### Challenges of Managing R Packages in the Cluster Environment

Users can install their own R packages by using the standard `install.packages()` command from an R session.  R provides sensible defaults for the resulting installation directories, making an effort to differentiate between possible multiple versions of R.  While suitable for a typical personal workstation, this level of differentiation is often insufficient in an HPC environment with multiple versions _and_ multiple clusters.  Not only R versions may change through the lifetime of a cluster, or differ between clusters, but different clusters often have different hardware architectures (so packages installed on one cluster may work suboptimally, or not work at all on another one).

_We strongly recommend installing user packages into separate directories on a per-cluster, per-R version basis to ensure optimal performance and compatibility._
This can be achieved by specifying custom values of `R_LIBS_USER` environment variable manually, or by using a customized personal `~/.Rprofile` initialization file that defines proper cluster- and version-specific destination locations automatically.

A minor drawback of this approach is that you may have to recreate the environment (i.e. repeat package installation) for each new cluster or R version. But it is by far outweighed by major benefits of stability, consistency and isolation of each environment (e.g. changes to packages that you made on one cluster would not accidentally break your work on another one).

### Installing Packages

For your convenience, ITaP provides a sample [~/.Rprofile example file](/knowledge/downloads/run/examples/apps/r/src/Rprofile_example) that can be downloaded to your cluster account and renamed into `~/.Rprofile` (or appended to one).  Follow these steps to download our recommended `~/.Rprofile` example and copy it into place:

```
$ curl -#LO https://www.rcac.purdue.edu/knowledge/downloads/run/examples/apps/r/src/Rprofile_example
$ mv -ib Rprofile_example ~/.Rprofile
```

The above installation step needs to be done only once.  Now load the R module and run R:

```
$ module load r
$ R
> .libPaths()
[1] "/home/${user.username}/R/${resource.hostname}/3.6.1_gcc-6.3.0_${resource.hostname}"
[2] "/apps/cent7/R/3.6.1_gcc-6.3.0_${resource.hostname}/lib64/R/library"
```

`.libPaths()` should output something similar to above if it is set up correctly.  Now let's try installing a package:

```
> install.packages('packagename', repos="http://ftp.ussg.iu.edu/CRAN/")
```

The above commands should download and install the requested R package, which upon completion can then be loaded.

###  Loading Libraries

Once you have packages installed you can load them with the library() function as shown below:

```
> library('packagename')
```

The package is now installed and loaded and ready to be used in R. 
 
### Installing Package Example
The following demonstrates installing the dplyr package assuming the above-mentioned custom `~/.Rprofile` is in place (note its effect in the "Installing package into" information message):

```
$ module load r
$ R
> install.packages('dplyr', repos="http://ftp.ussg.iu.edu/CRAN/")
Installing package into ‘/home/${user.username}/R/${resource.hostname}/3.6.1_gcc-6.3.0_${resource.hostname}’
(as ‘lib’ is unspecified)
 ...
also installing the dependencies 'crayon', 'utf8', 'bindr', 'cli', 'pillar', 'assertthat', 'bindrcpp', 'glue', 'pkgconfig', 'rlang', 'Rcpp', 'tibble', 'BH', 'plogr'
 ...
 ...
 ...
The downloaded source packages are in 
	'/tmp/RtmpHMzm9z/downloaded_packages'

>library(dplyr)

Attaching package: 'dplyr'
>
```

Many R packages are dependent on other R packages but install.packages() will install all dependencies and their dependencies by default.  Repeat install.packages(...) for any packages that you need. Your R packages should now be installed.

<p>
For more information about installing R packages:
</p>

<ul>
 <li><a href="http://cran.r-project.org/doc/manuals/r-release/R-admin.html#Installing-packages" target="_blank" rel="noopener">Installing additional R packages on Linux</a></li>
 <li><a href="https://cran.r-project.org/web/packages/" target="_blank" rel="noopener">List of Packages</a></li>
</ul>
