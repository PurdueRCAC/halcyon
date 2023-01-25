@component('mail::message')
@if ($report->groupid && $report->group)
**Group:** {{ $report->group->name }}<br />
@endif
@if ($people = $report->usersAsString())
**People:** {{ $people }}<br />
@endif
**Date:** {{ $report->datetimecontact->format('F j, Y') }}

{!! $report->toMarkdown() !!}

---
[Contact Report #{{ $report->id }}]({{ route('site.contactreports.show', ['id' => $report->id]) }}) posted by {{ $report->creator ? $report->creator->name : 'user ID #' . $report->userid }} on {{ $report->datetimecreated->format('F j, Y g:ia') }}.
@endcomponent