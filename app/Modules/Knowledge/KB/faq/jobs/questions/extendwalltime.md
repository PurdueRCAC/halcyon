---
title: Can I extend the walltime on a PBS job?
tags:
  - wholenode
---

### Can I extend the walltime of a PBS job on ${resource.name}?

In some circumstances, yes. Walltime extensions must be requested of and completed by Research Computing staff. Walltime extension requests will be considered on named (your advisor or research lab) queues. **Standby or debug queue jobs cannot be extended**.

Extension requests are at the discretion of Research Computing staff based on factors such as any upcoming maintenance or resource availability. {::if resource.name == Scholar} Jobs in the the 'scholar' queue on Scholar cannot be extended. 'Long' queue jobs can be extended to the maximum for that queue. {::else} Extensions can be made past the normal maximum walltime on named queues but these jobs are subject to early termination should a conflicting maintenance downtime be scheduled.  {::/} 

Please be mindful of time remaining on your job when making requests and make requests at least 24 hours before the end of your job AND during business hours. We cannot guarantee jobs will be extended in time with less than 24 hours notice, after-hours, during weekends, or on a holiday.

We ask that you make accurate walltime requests during job submissions. Accurate walltimes will allow the job scheduler to efficiently and quickly schedule jobs on the cluster. Please consider that extensions can impact scheduling efficiency for all users of the cluster.

Requests can be made to <a href="mailto:rcac-help@purdue.edu">rcac-help@purdue.edu</a>. We ask that you:

* Provide numerical job IDs, cluster name, and your desired extension amount.
* Provide at least 24 hours notice before job will end (more if request is made on a weekend or holiday).
* Consider making requests during business hours. We may not be able to respond in time to requests made after-hours, on a weekend, or on a holiday.


{::if user.staff == 1}

### Staff Notes

Extension requests are granted at the discretion of the staff member who fields that ticket.  Requests should be dealt with as soon as practical, but no expectation exists that extensions will be granted within less than 24 hours.  If the request is made very close to the job’s termination, it may not be extended in time.

Some factors that should be taken into account for a request are: 

* Upcoming maintenance: We can extend up to maintenance but not into or past it.
* Amount of extension: Extensions up to the max for the queue is always approved, but beyond the maximum should look at the next factors as well.
* Resource availability: Very small or limited clusters/resources may warrant more scrutiny.  No extensions are given on Scholar beyond the max for the queue.
* Frequency of extension requests from this user: This should not become a habit.


If extending a walltime, add a statement in your response to the user to remind the user to please make accurate walltime requests in their job submissions and explain that accurate walltimes will allow the job scheduler to more efficiently schedule your jobs on the cluster. 

Walltime can be extended with the command:

`qalter -lwalltime=HHH:MM:SS <jobid>`
{::/}
