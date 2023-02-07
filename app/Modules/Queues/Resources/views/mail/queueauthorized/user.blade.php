@component('mail::message')
Hello {{ $user->name }},

You have been granted access to {{ config('app.name') }} resources.

@php
$itar = 0;
$data = 0;
$need_data_info = 0;
@endphp
@if (count($roles))
---

You have been granted **accounts** on the following resources.

@foreach ($roles as $resource)
* {{ $resource->name }}
@php
if ($resource->name == 'RCACIUSR' || $resource->name == 'exrc')
{
	$itar = 1;
}
if ($resource->name == 'HPSSUSER')
{
	$data = 1;
}
@endphp
@endforeach

<?php if ($itar): ?>
**Access to ITAR systems is only possible by public SSH key authentication. You will need to send a public SSH key, in OpenSSH format, to {{ config('mail.from.address') }}. New public key requests are generally handled within one business day after account creation.**

SSH key-pairs must be generated on the machine you intend to connect to the ITAR cluster from. For Linux and Mac systems you can generate a SSH key-pair by typing the command `ssh-keygen -t rsa -b 2048` into the terminal. You will need to send the public key from the file `~/.ssh/id_rsa.pub` to {{ config('mail.from.address') }}. For Windows systems using PuTTY, a key-pair can be generated using puttygen. Select SSH-2 RSA with 2048 bits from the parameters and press Generate. 

**You must choose a strong, unique, passphrase while generating the key-pair. The private key must remain secure and private. Do not send this file to anyone else or allow anyone else to access the file. This file is used to authenicate against the public key on the cluster. Anyone with access to this file could be able to compromise your account.**

These accounts will be created during overnight processing. Accounts on non-ITAR systems are generally ready for use the morning of the next day ({{ Carbon\Carbon::now()->modify('+1 day')->format('F jS') }}) if requested by midnight. You will receive another notification with information about logging in and getting started once your account is ready for use.
<?php elseif ($data): ?>
These accounts will be created during overnight processing. Accounts are generally ready for use the morning of the next day ({{ Carbon\Carbon::now()->modify('+1 day')->format('F jS') }}) if requested by midnight.
<?php else: ?>
These accounts will be created during overnight processing. Accounts are generally ready for use the morning of the next day ({{ Carbon\Carbon::now()->modify('+1 day')->format('F jS') }}) if requested by midnight. You will receive another notification with information about logging in and getting started once your account is ready for use.
<?php endif; ?>
@endif

---

You have been granted **access** to the following job submission queues, Unix groups, and other resources.

@foreach ($queueusers as $queueuser)
@php
if ($queueuser->unixgroupid)
{
	if ($data)
	{
		// If this is a Fortress-only addition, user won't be able to use it till tomorrow
		$eta = 'tomorrow';
	}
	else 
	{
		$eta = 'within 4 hours';
	}
	$need_data_info = 1;
}
else
{
	$eta = 'now';

	foreach ($roles as $resource)
	{
		if ($queueuser->queue->resource->name == $resource->name)
		{
			$eta = 'tomorrow';
		}
	}
}
@endphp
@if (isset($queueuser->unixgroupid))
* Unix group: {{ $queueuser->unixgroup->longname }} (membership ready {{ $eta }})
@else
* {{ $queueuser->queue->resource->name }}: {{ $queueuser->queue->name }} (account ready {{ $eta }})
@endif
@endforeach

@if ($need_data_info)
You will be able to access the Data Depot (if applicable) within a few hours if you already have a {{ config('app.name') }} account, otherwise you will be able to access it tomorrow morning. You may transfer files to and from the Data Depot through [Globus](https://www.rcac.purdue.edu/storage/depot/guide/#storage_transfer_globus), by [mapping a network drive](https://www.rcac.purdue.edu/storage/depot/guide/#storage_transfer_cifs), or directly on other {{ config('app.name') }} resources. The [complete user guide](https://www.rcac.purdue.edu/storage/depot/guide/) also describes several other access methods, policies, and lost file recovery options.

You will be able to access Fortress (if applicable) tomorrow morning. You may transfer files to and from Fortress through [Globus](https://www.rcac.purdue.edu/storage/fortress/guide/#storage_transfer_globus), by [SFTP](https://www.rcac.purdue.edu/knowledge/fortress/all#storage_transfer_fortress-sftp), or directly through other {{ config('app.name') }} resources via [HSI](https://www.rcac.purdue.edu/storage/fortress/guide/#storage_transfer_hsi) and [HTAR](https://www.rcac.purdue.edu/storage/fortress/guide/#storage_transfer_htar). The [complete user guide](https://www.rcac.purdue.edu/storage/fortress/guide/) also describes several other access methods.
@endif

@endcomponent
