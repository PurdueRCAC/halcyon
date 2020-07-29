---
title: Node Access Policies
tags:
 - wholenode
 - sharednode
---

# Node Access Policies

Node Access Policy determines how the scheduler allocates a job on a node. By default, job scheduling happens based on the default policy specified in the queue configurations, however, users can change it by specifying the <kbd>naccesspolicy</kbd> option in the qsub command. The syntax of this option is:

<pre>qsub -l naccesspolicy=policy ... other arguments ... </pre>



<table class="inrows-wide">
<caption>where <kbd>policy</kbd> can be one of the following:</caption>
<thead>
        <tr>
                <th scope="col">Policy</th>
                <th scope="col">Favorable Use Case</th>
                <th scope="col">Explanation</th>
                <th scope="col">Advantages</th>
                <th scope="col">Disadvantages</th>
        </tr>
</thead>
<tbody>
{::if resource.naccesspolicy == shared}
        <tr>
                <td><b>shared</b></td>
                <td>Lots of small jobs that need little memory</td>
                <td>Jobs from any user can run on a node</td>
                <td>Jobs start sooner, efficient use of community cluster</td>
                <td>Jobs may contend for resources, especially memory</td>
        </tr>
{::/}
        <tr>
                <td><b>singleuser</b></td>
                <td>Lots of small jobs that pack densely on one or more nodes</td>
                <td>All jobs running on a node must be owned by the same user</td>
                <td>Jobs start sooner, only contend with own jobs</td>
                <td>Takes up all ${resource.nodecores} cores on a node even if not using them</td>
        </tr>
        <tr>
                <td><b>singlejob</b></td>
                <td>Wide jobs or jobs that use large amounts of memory</td>
                <td>Only one job can run on a node</td>
                <td>No contention with other jobs</td>
                <td>Takes up all ${resource.nodecores} cores on a node even if not using them</td>
        </tr>
</tbody>
</table>

{::if resource.naccesspolicy == shared}
An example to submit a job in shared mode:

<pre>qsub -q myqueue -l nodes=1:ppn=1,walltime=00:30:00,naccesspolicy=shared myjobscript.sub</pre>
{::/}

An example to submit a job in singleuser mode:

<pre>qsub -q myqueue -l nodes=1:ppn=4,walltime=00:30:00,naccesspolicy=singleuser myjobscript.sub</pre>

Please note that, in <kbd>singleuser</kbd> and <kbd>singlejob</kbd> modes, your queue allocation would be deducted by a multiple of ${resource.nodecores} even if you are not using all the cores. For example, if you run 3 jobs with <kbd>nodes=1:ppn=${resource.nodecores/2}</kbd>, then in <kbd>singleuser</kbd> mode, you would be occupying 2 whole nodes (${resource.nodecores*2} cores) from your queue even though the jobs are only utilizing ${resource.nodecores*1.5} cores. Similarly, in <kbd>singlejob</kbd> mode, you would be occupying 3 whole nodes (${resource.nodecores*3} cores) from your queue.
<br/><br/>
The default node access policy on ${resource.name} is <b>${resource.naccesspolicy}</b>
