@component('mail::message')
{$comment->comment}

---
Posted {{ $comment->datetimecreated->format('F j, Y g:ia') }} by {{ $comment->creator->name }} on [Contact Report #{{ $comment->contactreportid }}]({{ route('site.contactreports.show', ['id' => $comment->contactreportid])}}).
@endcomponent