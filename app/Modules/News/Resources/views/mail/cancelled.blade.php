@component('mail::message')
Hello {{ $association->associated ? $association->associated->name : '' }},

Your registration for an event has been **<span style="color:red">cancelled</span>**. Event details are listed below.

---

**Event:** [{{  $article->headline }}]({{ route('site.news.show', ['id' => $article->id]) }}) <br />
**Date/Time:** {{ $article->formatDate($article->datetimenews, $article->datetimenewsend) }}<br />
@if ($article->type)
**Category:** {{ $article->type->name }} <br />
@endif
@if ($article->location)
**Location:** {{ $article->location }} <br />
@endif
@if ($article->url)
**URL:** [{{ \Illuminate\Support\Str::limit($article->url, 50) }}]({{ $article->url }})
@endif

@if (count($article->updates))
@foreach ($article->updates()->orderBy('datetimecreated', 'desc')->get() as $update)
_**UPDATE: {{ $update->formatDate($update->datetimecreated) }}**_

{!! $update->formattedBody !!}

@endforeach

_**ORIGINAL: {{ $article->formatDate($article->datetimenews, $article->originalDatetimenewsend) }}**_
@endif

@if ($article->isUpdated())
_**Update: {{ $article->formatDate($article->datetimeupdate) }}**_
@endif

{!! $article->formattedBody !!}

---
[Article #{{ $article->id }}]({{ route('site.news.show', ['id' => $article->id]) }}) posted on {{ $article->datetimenews->format('F j, Y g:ia') }}.
@endcomponent