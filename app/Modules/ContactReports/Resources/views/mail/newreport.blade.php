@component('mail::message')
{{ $report->usersAsString() }}
{{ $report->datetimecontact->format('F j, Y') }}

{{ $report->report }}

---
[Contact Report #{{ $report->id }}]({{ route('site.contactreports.show', ['id' => $report->id]) }}) posted by {{ $report->creator ? $report->creator->name : 'user ID #' . $report->userid }} on {{ $report->datetimecreated->format('F j, Y g:ia') }}.
@endcomponent