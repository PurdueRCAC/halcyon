---
title: Profile Manager
tags:
 - slurm
 - wholenode
 - sharednode
---
# Profile Manager

MATLAB offers two kinds of profiles for parallel execution: the 'local' profile and user-defined cluster profiles. The 'local' profile runs a MATLAB job on the processor core(s) of the same compute node, or front-end, that is running the client. To run a MATLAB job on compute node(s) different from the node running the client, you must define a Cluster Profile using the <kbd>Cluster Profile Manager</kbd>.

To prepare a user-defined cluster profile, use the <kbd>Cluster Profile Manager</kbd> in the <kbd>Parallel</kbd> menu. This profile contains the scheduler details (queue, nodes, processors, walltime, etc.) of your job submission.  Ultimately, your cluster profile will be an argument to MATLAB functions like <kbd>batch()</kbd>.

For your convenience, ITaP provides a generic cluster profile that can be downloaded: [my${resource.batchsystem}profile.settings](/knowledge/downloads/run/examples/apps/matlab/src/my${resource.batchsystem}profile.settings)

Please note that modifications are very likely to  be required to make <kbd>my${resource.batchsystem}profile.settings</kbd> work. You may need to change values for number of nodes, number of workers, walltime, and submission queue specified in the file. As well, the generic profile itself depends on the particular job scheduler on the cluster, so you may need to download or create two or more generic profiles under different names.  Each time you run a job using a Cluster Profile, make sure the specific profile you are using is appropriate for the job and the cluster.

To import the profile, start a MATLAB session and select <kbd>Manage Cluster Profiles...</kbd> from the Parallel menu. In the Cluster Profile Manager, select <kbd>Import</kbd>, navigate to the folder containing the profile, select <kbd>my${resource.batchsystem}profile.settings</kbd> and click <kbd>OK</kbd>. Remember that the profile will need to be customized for your specific needs. If you have any questions, please <a href="mailto:rcac-help@purdue.edu">contact us</a>.

For detailed information about MATLAB's Parallel Computing Toolbox, examples, demos, and tutorials:
<ul>
	<li><a href="http://www.mathworks.com/help/distcomp/index.html" target="_blank" rel="noopener">MATLAB - Parallel Computing Toolbox</a></li>
	<li><a href="http://www.mathworks.com/help/distcomp/introduction-to-parallel-solutions.html" target="_blank" rel="noopener">MATLAB Parallel Computing Toolbox: Introduction to Parallel Solutions</a></li>
	<li><a href="http://www.mathworks.com/help/distcomp/clusters-and-cluster-profiles.html" target="_blank" rel="noopener">MATLAB Parallel Computing Toolbox: Clusters and Cluster Profiles</a></li>
</ul>
