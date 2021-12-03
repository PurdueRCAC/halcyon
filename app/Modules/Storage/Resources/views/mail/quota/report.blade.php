@component('mail::message')
Hello {{ $user->name }},

Current `{{ $notification->directory->storageResource->path . '/' . $notification->directory->path }}` usage:

@if ($latest->quota)
{{ $latest->formattedSpace }} / {{ $latest->formattedQuota }} ({{ round(($latest->space / $latest->quota) * 100, 1) }}%)
@endif
@if ($latest->filequota)
{{ $latest->files }} / {{ $latest->filequota }} files ({{ round(($latest->files / $latest->filequota) * 100, 1) }}%)
@endif

---

You are receiving this alert in response to quota reports you have defined.

You may disable or adjust these reports at the following URL:

[{{ route('site.users.account.section', ['section' => 'quota']) }}]({{ route('site.users.account.section', ['section' => 'quota']) }})

@endcomponent