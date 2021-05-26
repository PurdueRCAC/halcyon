@component('mail::message')
Hello {{ $user->name }},

Total usage has dropped below {{ $notification->threshold }} on `{{ $notification->directory->storageResource->path . '/' . $notification->directory->path }}`.

Current usage:

@if ($latest->quota)
{{ $latest->space }} / {{ $latest->quota }} ({{ round(($latest->space / $latest->quota) * 100, 1) }}%)
@endif
@if ($latest->filequota)
{{ $latest->files }} / {{ $latest->filequota }} files ({{ round(($latest->files / $latest->filequota) * 100, 1) }}%)
@endif

For group storage spaces this is a total of usage by all group members.

---

You are receiving this alert in response to quota alerts you have defined or that have been defined automatically.

You may disable or adjust these alerts at the following URL:

[{{ route('site.users.account.section', ['section' => 'quota']) }}]({{ route('site.users.account.section', ['section' => 'quota']) }})
@endcomponent