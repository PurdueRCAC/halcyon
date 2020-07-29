@component('mail::message')
{{ $article->body }}

---
[Article #{{ $article->id }}]({{ route('site.news.show', ['id' => $article->id]) }}) posted on {{ $report->datetimenews->format('F j, Y g:ia') }}.
@endcomponent