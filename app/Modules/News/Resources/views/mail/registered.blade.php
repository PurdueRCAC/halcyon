@component('mail::message')
Hello {{ $association->associated ? $association->associated->name : '' }},

You **<span style="color:green">successfully</span>** registered for an event. Event details are listed below. To cancel registration, visit the event page.

@if ($association->comment)
> {{ $association->comment }}
@endif

---

**Event:** [{{  $article->headline }}]({{ route('site.news.show', ['id' => $article->id]) }}) <br />
**Date/Time:** {{ $article->formatDate($article->datetimenews, $article->datetimenewsend) }} <br />
@if ($article->type)
**Category:** {{ $article->type->name }} <br />
@endif
@if ($article->location)
**Location:** {{ $article->location }} <br />
@endif
@if ($article->url)
@php
$url = parse_url($article->url);
@endphp
**URL:** [{{ Illuminate\Support\Str::limit($url['host'], 70) . ($url['path'] || $url['query'] ? ' ...' : '') }}]({{ $article->visitableUrl }})
@endif

@if (count($article->updates))
@foreach ($article->updates()->orderBy('datetimecreated', 'desc')->get() as $update)
_**UPDATE: {{ $update->formatDate($update->datetimecreated) }}**_

{!! $update->toMarkdown() !!}

@endforeach

_**ORIGINAL: {{ $article->formatDate($article->datetimenews, $article->originalDatetimenewsend) }}**_
@endif

@if ($article->isUpdated())
_**Update: {{ $article->formatDate($article->datetimeupdate) }}**_
@endif

{!! $article->toMarkdown() !!}

---
[Article #{{ $article->id }}]({{ route('site.news.show', ['id' => $article->id]) }}) posted on {{ $article->datetimenews->format('F j, Y g:ia') }}.
@endcomponent