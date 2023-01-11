@component('mail::message')
Hello {{ $user->name }},

The following storage spaces for {{ $group->name }} have loans or purchases that will be expiring soon:

@foreach ($directories as $directory)
* `{{ $directory->storageResource->path . '/' . $directory->path }}`
@foreach ($group->loans()->withTrashed()->where('datetimestop', '>', Carbon\Carbon::now()->modify('+6 days')->toDateTimeString())->where('datetimestop', '<=', Carbon\Carbon::now()->modify('+7 days')->toDateTimeString())->orderBy('id', 'asc')->get() as $loan)
  * Loan of {{ $loan->formattedBytes }} expires {{ $loan->datetimestop->diffForHumans() }}
@endforeach
@foreach ($group->purchases()->withTrashed()->where('datetimestop', '>', Carbon\Carbon::now()->modify('+6 days')->toDateTimeString())->where('datetimestop', '<=', Carbon\Carbon::now()->modify('+7 days')->toDateTimeString())->orderBy('id', 'asc')->get() as $purchase)
  * Purchase of {{ $purchase->formattedBytes }} expires {{ $purchase->datetimestop->diffForHumans() }}
@endforeach
@endforeach

You may review these storage allocations at the following URL:

[{{ route('site.users.account.section.show.subsection', ['section' => 'groups', 'id' => $group->id, 'subsection' => 'storage']) }}]({{ route('site.users.account.section.show.subsection', ['section' => 'groups', 'id' => $group->id, 'subsection' => 'storage']) }})

@endcomponent