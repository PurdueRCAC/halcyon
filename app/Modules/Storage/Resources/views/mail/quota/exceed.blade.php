@component('mail::message')
Hello {$user->name},

Total usage has exceeded {{ $notification->threshold }} on `{{ $notification->directory->path }}`.

Current usage:

@if ($latest->quota)
{{ $latest->space }} / {{ $latest->quota }} ({{ round(($latest->space / $latest->quota) * 100, 1) }}%)
@endif
@if ($latest->filequota)
{{ $latest->files }} / {{ $latest->filequota }} files ({{ round(($latest->files / $latest->filequota) * 100, 1) }}%)
@endif

**For Data Depot spaces this is _total_ usage by _all_ group members.**

Additional Data Depot space can be ordered from the following URL:

https://www.rcac.purdue.edu/purchase

---

You are receiving this alert in response to alerts that have been automatically or manually defined. For Data Depot spaces other group members are also receiving this message - if you have not used this space you may disregard this message.

You may disable or adjust these alerts at the following URL:

https://www.rcac.purdue.edu/account/myquota/
@endcomponent