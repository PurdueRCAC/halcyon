@if ($page->params->get('show_title', 1))
	<h1>{{ $page->title }}</h1>
@endif

@if ($page->params->get('show_author') || $page->params->get('show_create_date') || $page->params->get('show_modify_date') || $page->params->get('show_hits'))
	<dl class="article-info">
		<dt class="article-info-term">{{ trans('pages::pages.article info') }}</dt>
	@if ($page->params->get('show_create_date'))
		<dd class="create">
			{{ trans('pages::pages.created on', ['date' => $page->created_at->toDateTimeString()]) }}
		</dd>
	@endif
	@if ($page->params->get('show_modify_date'))
		<dd class="updated">
			{{ trans('pages::pages.last updated', ['date' => $page->updated_at->toDateTimeString()]) }}
		</dd>
	@endif
	@if ($page->params->get('show_author') && $page->creator->id)
		<dd class="createdby">
			{{ trans('pages::pages.article author', ['author' => $page->creator->name]) }}
		</dd>
	@endif
	@if ($page->params->get('show_hits'))
		<dd class="hits">
			{{ trans('pages::pages.article hits', ['hits' => $page->hits]) }}
		</dd>
	@endif
	</dl>
@endif

{!! $page->body !!}
