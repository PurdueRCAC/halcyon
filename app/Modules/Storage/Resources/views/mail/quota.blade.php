@component('mail::message')
{$comment->comment}

---
Posted {{ $comment->datetimecreated->format('F j, Y g:ia') }} by {{ $comment->creator ? $comment->creator->name : 'user ID #' . $comment->userid }} on [Contact Report #{{ $comment->contactreportid }}]({{ route('site.contactreports.show', ['id' => $comment->contactreportid])}}).
@endcomponent