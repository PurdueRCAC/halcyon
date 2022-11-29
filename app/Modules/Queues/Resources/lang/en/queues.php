<?php
return [
	'module name' => 'Queue Manager',
	'module sections' => 'Module sections',
	'queues' => 'Queues',
	'queue' => 'Queue',
	'purchases and loans' => 'Purchases & Loans',
	'types' => 'Types',
	'type' => 'Type',
	'state' => 'State',
	//'edit' => 'Edit',
	//'create' => 'Create',
	'id' => 'ID',
	'name' => 'Name',
	'name error' => 'The field "Name" is required.',
	'all types' => '- All Types -',
	'enabled' => 'Enabled',
	'disabled' => 'Disabled',
	'trashed' => 'Trashed',
	'created' => 'Created',
	'removed' => 'Removed',
	'default walltime' => 'Default Walltime',
	'max walltime' => 'Max Walltime',
	'resource' => 'Resource',
	'subresource' => 'Node Type',
	'set state to' => 'Set this to :state',
	'node memory minimum' => 'Min memory',
	'node memory maximum' => 'Max memory',
	'last seen' => 'Last seen',
	'all queue classes' => '- All Classes -',
	'class' => 'Class',
	'system' => 'System',
	'system queues' => 'System queues',
	'owner' => 'Owner',
	'owner queues' => 'Owner queues',
	'all resources' => '- All Resources -',
	'scheduler' => 'Scheduler',
	'schedulers' => 'Schedulers',
	'scheduler policies' => 'Scheduler Policies',
	'qos' => 'Quality of Service',
	'all batch systems' => '- Select Batch system -',
	'batch system' => 'Batch system',
	'batch system warning' => 'Changing this value to anything other than PBS can have drastic effects for <code>qcontrol</code>.',
	'all scheduler policies' => '- Select Scheduler Policy -',
	'scheduler policy' => 'Scheduler Policy',
	'hostname' => 'Hostname',
	'default max walltime' => 'Max Walltime',
	'group' => 'Group',
	'search for group' => 'Search for group...',
	'jobs' => 'Jobs',
	'max jobs queued' => 'Max Jobs Queued',
	'max jobs queued per user' => 'Max Jobs Queued per User',
	'max jobs run' => 'Max Jobs Running',
	'max jobs run per user' => 'Max Jobs Running per User',
	'max job cores' => 'Max Cores per Job',
	'walltime' => 'Walltime',
	'node cores default' => 'Default Node Cores',
	'node cores min' => 'Minimum Node Cores',
	'node cores max' => 'Max Node Cores',
	'node mem min' => 'Minimum Node Memory',
	'node mem max' => 'Max Node Memory',
	'scheduling' => 'Scheduling',
	'stopped' => 'Stopped',
	'started' => 'Started',
	'select group' => '(select group)',
	'messages' => [
		'items enabled' => 'Queue(s) enabled.',
		'items disabled' => 'Queue(s) disabled.',
		'items stopped' => 'Scheduling stopped on selected queues.',
		'items started' => 'Scheduling started on selected queues.',
	],
	'reservation' => 'Dedicated Reservation',
	'reservation desc' => 'Allow dedicated reservations?',
	'queue has dedicated reservation' => 'Queue has dedicated reservation.',
	'queue is running' => 'Queue is running.',
	'queue is stopped' => 'Queue is stopped or disabled.',
	'queue has not active resources' => 'Queue has no active resources. Remove queue or sell/loan nodes or service units.',
	'max ijob factor' => 'Max Jobs per Iteration Factor',
	'max ijob user factor' => 'Max Jobs per User Iteration Factor',
	'cluster' => 'Subcluster(s)',
	'acl users enabled' => 'User ACL Enabled',
	'acl users enabled desc' => 'User ACL Enabled',
	'acl groups' => 'Group ACL',
	'acl groups desc' => 'Comma separated list of ACL groups',
	'priority' => 'Priority',
	'submission state' => 'Submission to the queue',
	'gpus' => 'GPUs',
	'nodes' => 'Nodes',
	'cores' => 'Cores',
	'service units' => 'Service Units',
	'sus' => 'SUs',
	'total' => 'Total',
	'active allocation' => 'Active Allocation',
	'amount' => 'Amount',
	'loans' => 'Loans',
	'access' => 'Access',
	'sell' => 'Sell',
	'loan' => 'Loan',
	'seller' => 'Seller',
	'lender' => 'Lender',
	'org owned' => '(ITaP-Owned)',
	'standby' => 'Standby',
	'owner' => 'Owner',
	'work' => 'Workq',
	'debug' => 'Debug',
	'end of life' => 'end of cluster life',
	'nodes' => 'Nodes',
	'cores' => 'Cores',
	'start' => 'Start',
	'stop' => 'Stop',
	'end' => 'End',
	'free' => 'Free',
	'free desc' => 'Can be reserved for free?',
	'comment' => 'Comment',
	'error' => [
		'invalid name' => 'Please provide a valid name.',
		'invalid hostname' => 'Please provide a valid hostname.',
		'invalid scheduler' => 'Please select a scheduler.',
		'invalid subresource' => 'Please select a node type.',
		'start cannot be after stop' => 'Field `start` cannot be after or equal to stop time',
		'corecount cannot be modified' => 'Core count cannot be modified on entries already in affect',
		'invalid corecount' => 'Invalid `corecount` value',
		'queue is empty' => 'Have not been sold anything and never will have anything',
		'queue has not started' => 'Have not been sold anything before this would start',
		'queue already exists' => 'A queue with the provided name and resource already exists',
		'start cannot be after end' => 'Field `start` cannot be after or equal to stop time',
		'failed to find counter' => 'Failed to retrieve counter entry',
		'failed to update counter' => 'Failed to update counter entry for #:id',
		'invalid queue' => 'Unknown or invalid queue',
		'entry already exists for hostname' => 'Entry already exists for `:hostname`',
	],
	'number cores' => ':num cores',
	'number memory' => ':num memory',
	'select queue' => '(Select Queue)',
	'loan to' => 'Loan to',
	'sell to' => 'Sell to',
	'action' => 'Action',
	'source' => 'Source',
	'start scheduling' => 'Start scheduling',
	'stop scheduling' => 'Stop scheduling',
	'start all scheduling' => 'Start all scheduling',
	'stop all scheduling' => 'Stop all scheduling',
	'options' => 'Options',
	'all states' => '- All States -',
	'edit loan' => 'Edit loan',
	'edit size' => 'Edit purchase',
	'new hardware' => 'New hardware',
	'cores per nodes' => ':cores per node',
	'cores per gpus' => ':cores per GPU',
	'saving' => 'Saving...',
	'entry marked as trashed' => 'This entry is marked as trashed.',
	'list of queues' => 'Below is a list of all queues',
	'confirm delete queue' => 'Are you sure you want to delete this queue?',
	'stats' => 'Stats',
	'member' => 'Member',
	'pending' => 'Pending',
	'status' => 'Status',
	'retired' => 'Retired',
	'export' => 'Export',
	// Qos
	'limits' => 'Limits',
	'description' => 'Description',
	'min_prio_thresh' => 'Minimum priority threshold',
	'max_jobs_pa' => 'Max jobs per account',
	'max_jobs_pa desc' => 'The maximum number of jobs an account (or subaccount) can have running at a given time.',
	'max_jobs_per_user' => 'Max jobs per user',
	'max_jobs_per_user desc' => 'The maximum number of jobs a user can have running at a given time.',
	'max_jobs_accrue_pa' => 'Max jobs accrueable per account',
	'max_jobs_accrue_pa desc' => 'The maximum number of pending jobs an account (or subacct) can have accruing age priority at any given time. This limit does not determine if the job can run, it only limits the age factor of the priority.',
	'max_jobs_accrue_pu' => 'Max jobs accrueable per user',
	'max_jobs_accrue_pu desc' => 'The maximum number of pending jobs a user can have accruing age priority at any given time. This limit does not determine if the job can run, it only limits the age factor of the priority.',
	'min_prio_thresh' => 'Minimum Priority Threshold',
	'min_prio_thresh desc' => 'Minimum priority required to reserve resources in the given association/QOS. Used to override bf_min_prio_reserve.',
	'max_submit_jobs_pa' => 'Max Submit Jobs Per Account',
	'max_submit_jobs_pa desc' => 'The maximum number of jobs an account (or subaccount) can have running and pending at a given time.',
	'max_submit_jobs_per_user' => 'Max Submit Jobs Per User',
	'max_submit_jobs_per_user desc' => 'The maximum number of jobs a user can have running and pending at a given time.',
	'max_tres_pa' => 'Max TRES Per Account',
	'max_tres_pa desc' => 'The maximum number of TRES an account can allocate at a given time.',
	'max_tres_pj' => 'Max TRES Per Job',
	'max_tres_pj desc' => 'The maximum size in TRES any given job can have from the association/QOS.',
	'max_tres_pn' => 'Max TRES Per Node',
	'max_tres_pn desc' => 'The maximum size in TRES each node in a job allocation can use.',
	'max_tres_pu' => 'Max TRES Per User',
	'max_tres_pu desc' => 'The maximum number of TRES a user can allocate at a given time.',
	'max_tres_mins_pj' => 'Max TRES Minutes Per Job',
	'max_tres_mins_pj desc' => 'A limit of TRES minutes to be used by a job. If this limit is reached the job will be killed if not running in Safe mode, otherwise the job will pend until enough time is given to complete the job.',
	'max_tres_run_mins_pa' => 'Max TRES Minutes Per Account',
	'max_tres_run_mins_pa desc' => '',
	'max_tres_run_mins_pu' => 'Max TRES Minutes Per User',
	'max_tres_run_mins_pu desc' => '',
	'min_tres_pj' => 'Min TRES Per Job',
	'min_tres_pj desc' => 'The minimum size in TRES any given job can have from the association/QOS.',
	'max_wall_duration_per_job' => 'Max wall duration per job',
	'max_wall_duration_per_job desc' => '',
	'grp_jobs' => 'Group jobs',
	'grp_jobs desc' => 'The total number of jobs able to run at any given time from an association and its children QOS. If this limit is reached new jobs will be queued but only allowed to run after previous jobs complete from this group.',
	'grp_jobs_accrue' => 'Group jobs accrue',
	'grp_jobs_accrue desc' => 'The total number of pending jobs able to accrue age priority at any given time from an association and its children QOS. If this limit is reached new jobs will be queued but not accrue age priority until after previous jobs are removed from pending in this group. This limit does not determine if the job can run or not, it only limits the age factor of the priority. When set on a QOS, this limit only applies to the job\'s QOS and not the partition\'s QOS.',
	'grp_submit_jobs' => 'Group Submit Jobs',
	'grp_submit_jobs desc' => 'The total number of jobs able to be submitted to the system at any given time from an association and its children or QOS. If this limit is reached new submission requests will be denied until previous jobs complete from this group.',
	'grp_tres' => 'Group TRES',
	'grp_tres desc' => 'The total count of TRES able to be used at any given time from jobs running from an association and its children or QOS. If this limit is reached new jobs will be queued but only allowed to run after resources have been relinquished from this group.',
	'grp_tres_mins' => 'Group TRES Minutes',
	'grp_tres_mins desc' => 'The total number of TRES minutes that can possibly be used by past, present and future jobs running from an association and its children or QOS. If any limit is reached, all running jobs with that TRES in this group will be killed, and no new jobs will be allowed to run.',
	'grp_tres_run_mins' => 'Group TRES Running Minutes',
	'grp_tres_run_mins desc' => 'Used to limit the combined total number of TRES minutes used by all jobs running with an association and its children or QOS. This takes into consideration time limit of running jobs and consumes it, if the limit is reached no new jobs are started until other jobs finish to allow time to free up.',
	'grp_wall' => 'Group walltime',
	'grp_wall desc' => 'The maximum wall clock time running jobs are able to be allocated in aggregate for a QOS or an association and its children. If this limit is reached, future jobs in this QOS or association will be queued until they are able to run inside the limit.',
	'preempt' => 'Preempt',
	'preempt desc' => 'A list of other QOSes that it can preempt.',
	'preempt_mode' => 'Preempt Mode',
	'preempt_mode desc' => 'Mechanism used to preempt jobs or enable gang scheduling.  It can be specified in addition to other Preempt Mode settings, with the two options comma separated (e.g. SUSPEND,GANG). OFF, CANCEL, GANG, REQUEUE, SUSPEND',
	'preempt_exempt_time' => 'Preempt Exempt Time',
	'preempt_exempt_time desc' => 'Specifies minimum run time of jobs before they are considered for preemption. This is only honored when the PreemptMode is set to REQUEUE or CANCEL.',
	'priority' => 'Priority',
	'priority desc' => 'The act of "stopping" one or more "low-priority" jobs to let a "high-priority" job run.',
	'usage_factor' => 'Usage Factor',
	'usage_factor desc' => 'A float that is factored into a job\'s TRES usage (e.g. RawUsage, TRESMins, TRESRunMins). For example, if the usagefactor was 2, for every TRESBillingUnit second a job ran it would count for 2. If the usagefactor was .5, every second would only count for half of the time. A setting of 0 would add no timed usage from the job.',
	'usage_thres' => 'Usage Threshold',
	'usage_thres desc' => 'A float representing the lowest fairshare of an association allowable to run a job. If an association falls below this threshold and has pending jobs or submits new jobs those jobs will be held until the usage goes back above the threshold. ',
	'limit_factor' => 'Limit Factor',
	'limit_factor desc' => '',
	'grace_time' => 'Grace Time',
	'grace_time desc' => 'Preemption grace time to be extended to a job which has been selected for preemption.',
];