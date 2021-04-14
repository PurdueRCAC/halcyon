@component('mail::message')
@if (count($article->updates))
@foreach ($article->updates()->orderBy('datetimecreated', 'desc')->get() as $update)
_**UPDATE: {{ $update->formatDate($update->datetimecreated) }}**_

{!! $update->formattedBody !!}
@endforeach

_**ORIGINAL: {{ $article->formatDate($article->datetimenews, $article->datetimenewsend) }}**_
@endif

**{{ $article->headline }}**

@if ($article->location)
{{ $article->location }}
@endif

@if ($article->isUpdated())
*_Update: {{ $article->formatDate($article->datetimeupdate) }}*
@endif

{!! $article->formattedBody !!}

---
[Article #{{ $article->id }}]({{ route('site.news.show', ['id' => $article->id]) }}) posted on {{ $report->datetimenews->format('F j, Y g:ia') }}.
@endcomponent