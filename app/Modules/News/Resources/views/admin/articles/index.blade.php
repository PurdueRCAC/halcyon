@extends('layouts.master')

@push('styles')
<link rel="stylesheet" type="text/css" media="all" href="{{ timestamped_asset('modules/core/vendor/tom-select/css/tom-select.bootstrap4.min.css') }}" />
@endpush

@push('scripts')
<script src="{{ timestamped_asset('modules/core/vendor/tom-select/js/tom-select.complete.min.js') }}"></script>
<script src="{{ timestamped_asset('modules/core/vendor/handlebars/handlebars.min-v4.7.7.js') }}"></script>
<script src="{{ timestamped_asset('modules/news/js/admin.js') }}"></script>
@endpush

@php
app('pathway')
	->append(
		trans('news::news.module name'),
		route('admin.news.index')
	);
if ($template)
{
	app('pathway')->append(trans('news::news.templates'));
}
else
{
	app('pathway')->append(trans('news::news.articles'));
}
@endphp

@section('toolbar')
	@if (auth()->user()->can('delete news'))
		@if ($filters['state'] == 'trashed')
			{!! Toolbar::custom(route('admin.news.restore'), 'refresh', 'refresh', trans('global.button.restore'), true) !!}
		@else
			{!! Toolbar::deleteList(trans('global.confirm delete'), route('admin.news.delete')) !!}
		@endif
	@endif

	@if (auth()->user()->can('create news'))
		{!! Toolbar::spacer() !!}
		{!! Toolbar::custom(route('admin.news.copy'), 'copy', 'copy', trans('news::news.copy'), true) !!}
		{!! Toolbar::addNew($template ? route('admin.news.create', ['template' => 1]) : route('admin.news.create')) !!}
	@endif

	@if (auth()->user()->can('admin news'))
		{!!
			Toolbar::spacer();
			Toolbar::preferences('news')
		!!}
	@endif

	{!! Toolbar::render() !!}
@stop

@section('title')
{{ trans('news::news.module name') }}{{ $template ? ': ' . trans('news::news.templates') : '' }}
@stop

@section('content')

@component('news::admin.submenu')
	@if (request()->segment(3) == 'templates')
		templates
	@else
		articles
	@endif
@endcomponent

<form action="{{ $template ? route('admin.news.templates') : route('admin.news.index') }}" method="get" name="adminForm" id="adminForm">

	<fieldset id="filter-bar" class="container-fluid mb-3">
		<div class="row">
			<div class="col-md-3 mb-2 filter-search">
				<label class="form-label sr-only visually-hidden" for="filter_search">{{ trans('search.label') }}</label>
				<span class="input-group">
					<input type="text" name="search" id="filter_search" class="form-control filter" placeholder="{{ trans('search.placeholder') }}" value="{{ $filters['search'] }}" />
					<span class="input-group-append"><button type="submit" class="input-group-text"><span class="fa fa-search" aria-hidden="true"></span><span class="sr-only visually-hidden">{{ trans('search.submit') }}</span></button></span>
				</span>
			</div>
			<div class="col-md-3">
			</div>
			<div class="col-md-3 mb-2">
				<label class="form-label sr-only visually-hidden" for="filter_state">{{ trans('news::news.state') }}</label>
				<select name="state" class="form-control filter filter-submit">
					<option value="*"<?php if ($filters['state'] == '*'): echo ' selected="selected"'; endif;?>>{{ trans('news::news.state_all') }}</option>
					<option value="published"<?php if ($filters['state'] == 'published'): echo ' selected="selected"'; endif;?>>{{ trans('news::news.published') }}</option>
					<option value="unpublished"<?php if ($filters['state'] == 'unpublished'): echo ' selected="selected"'; endif;?>>{{ trans('news::news.unpublished') }}</option>
					<option value="trashed"<?php if ($filters['state'] == 'trashed'): echo ' selected="selected"'; endif;?>>{{ trans('global.trashed') }}</option>
				</select>
			</div>
			<div class="col-md-3 mb-2">
				<label class="form-label sr-only visually-hidden" for="filter-type">{{ trans('news::news.type') }}</label>
				<select name="type" id="filter-type" class="form-control filter filter-submit">
					<option value="0">{{ trans('news::news.select type') }}</option>
					<?php foreach ($types as $type): ?>
						<option value="<?php echo $type->id; ?>"<?php if ($filters['type'] == $type->id) { echo ' selected="selected"'; } ?>>{{ ($type->level ? str_repeat('|_', $type->level) . ' ' : '') . $type->name }}</option>
					<?php endforeach; ?>
				</select>
			</div>
		</div>

		<input type="hidden" name="order" value="{{ $filters['order'] }}" />
		<input type="hidden" name="order_dir" value="{{ $filters['order_dir'] }}" />
	</fieldset>

	<!-- Experimental Vue rendering
	<div id="app">
		<table-component></table-component>
	</div>
	<script src="{{ timestamped_asset('modules/news/js/app-admin.js') }}"></script>
	-->

	@if (count($rows))
		<div class="card mb-4">
			<div class="table-responsive">
				<table class="table table-hover adminlist">
					<caption class="sr-only visually-hidden">{{ $template ? trans('news::news.articles') : trans('news::news.templates') }}</caption>
					<thead>
						<tr>
							@if (auth()->user()->can('delete news'))
								<th>
									{!! Html::grid('checkall') !!}
								</th>
							@endif
							<th scope="col">
								{!! Html::grid('sort', trans('news::news.id'), 'id', $filters['order_dir'], $filters['order']) !!}
							</th>
							<th scope="col">
								{!! Html::grid('sort', trans('news::news.headline'), 'headline', $filters['order_dir'], $filters['order']) !!}
							</th>
							<th scope="col">
								{!! Html::grid('sort', trans('news::news.state'), 'state', $filters['order_dir'], $filters['order']) !!}
							</th>
							<th scope="col">
								{!! Html::grid('sort', trans('news::news.type'), 'newstypeid', $filters['order_dir'], $filters['order']) !!}
							</th>
							@if (!$template)
								<th scope="col" colspan="3" class="text-center">
									{!! Html::grid('sort', trans('news::news.publish window'), 'datetimenews', $filters['order_dir'], $filters['order']) !!}
								</th>
								<th scope="col" class="text-right text-end">{{ trans('news::news.updates') }}</th>
								<th scope="col" class="text-right text-end">{{ trans('news::news.email') }}</th>
							@endif
						</tr>
					</thead>
					<tbody>
					@foreach ($rows as $i => $row)
						<tr>
							@if (auth()->user()->can('delete news'))
								<td>
									{!! Html::grid('id', $i, $row->id) !!}
								</td>
							@endif
							<td>
								{{ $row->id }}
							</td>
							<td>
								@php
								$headline = Illuminate\Support\Str::limit($row->headline, 70);
								$headline = App\Halcyon\Utility\Str::highlight(e($headline), $filters['search']);
								@endphp
								@if (auth()->user()->can('edit news'))
									<a href="{{ route('admin.news.edit', ['id' => $row->id]) }}">
										@if ($row->headline)
											{!! $headline !!}
										@else
											<span class="none">{{ trans('global.none') }}</span>
										@endif
									</a>
								@else
									@if ($row->headline)
										{!! $headline !!}
									@else
										<span class="none">{{ trans('global.none') }}</span>
									@endif
								@endif

								@if ($row->isMailed())
									<div class="text-muted">
										Last emailed:
										<time datetime="{{ $row->datetimemailed->toDateTimeLocalString() }}">
											{{ $row->datetimemailed->format('M j, Y g:ia T') }}
										</time>
										by {{ $row->mailer ? $row->mailer->name : trans('global.unknown') }}
									</div>
								@endif
							</td>
							<td>
								@if (auth()->user()->can('edit.state news'))
									@if ($row->trashed())
										<a class="badge badge-danger" href="{{ route('admin.news.restore', ['id' => $row->id]) }}" data-tip="{{ trans('news::news.click to restore') }}">
											{{ trans('global.trashed') }}
										</a>
									@elseif ($row->published)
										<a class="badge badge-success" href="{{ route('admin.news.unpublish', ['id' => $row->id]) }}" data-tip="{{ trans('news::news.click to unpublish') }}">
											{{ trans('global.published') }}
										</a>
									@else
										<a class="badge badge-secondary" href="{{ route('admin.news.publish', ['id' => $row->id]) }}" data-tip="{{ trans('news::news.click to publish') }}">
											{{ trans('global.unpublished') }}
										</a>
									@endif
								@else
									@if ($row->trashed())
										<span class="badge badge-danger">
											{{ trans('global.trashed') }}
										</span>
									@elseif ($row->published)
										<span class="badge badge-success">
											{{ trans('global.published') }}
										</span>
									@else
										<span class="badge badge-secondary">
											{{ trans('global.unpublished') }}
										</span>
									@endif
								@endif
							</td>
							<td>
								{{ $row->type->name }}
							</td>
							@if (!$template)
								<td class="text-right text-end text-nowrap">
									@if ($row->hasStart())
										<time datetime="{{ $row->datetimenews->toDateTimeLocalString() }}">
											{{ $row->datetimenews->format('M j, Y g:ia T') }}
										</time>
									@else
										<span class="none">{{ trans('global.none') }}</span>
									@endif
								</td>
								<td>
									@if ($row->hasStart())
										&rarr;
									@endif
								</td>
								<td class="text-nowrap">
									@if ($row->hasStart())
										@if ($row->hasEnd())
											<time datetime="{{ $row->datetimenewsend->toDateTimeLocalString() }}">
												{{ $row->isSameDay() ? $row->datetimenewsend->format('g:ia T') : $row->datetimenewsend->format('M j, Y g:ia T') }}
											</time>
										@else
											<span class="never">{{ trans('global.never') }}</span>
										@endif
									@else
										<span class="none">{{ trans('global.none') }}</span>
									@endif
								</td>
								<td class="text-right text-end">
									<a href="{{ route('admin.news.updates', ['article' => $row->id]) }}">
										{{ number_format($row->updates_count) }}
									</a>
								</td>
								<td class="text-right text-end">
									<button class="btn news-mail" data-success="Email sent!" data-toggle="modal" data-bs-toggle="modal" data-target="#mailpreview-modal" data-bs-target="#mailpreview-modal" data-article="{{ route('api.news.read', ['id' => $row->id]) }}" data-api="{{ route('api.news.email', ['id' => $row->id]) }}" data-tip="{{ trans('news::news.send email') }}">
										<span class="fa fa-envelope" ari-ahidden="true"></span><span class="sr-only visually-hidden">Email</span>
									</button>
								</td>
							@endif
						</tr>
					@endforeach
					</tbody>
				</table>
			</div>
		</div>

		{{ $rows->render() }}
	@else
		<div class="card mb-4">
			<div class="card-body text-muted text-center">{{ trans('global.no results') }}</div>
		</div>
	@endif

	<input type="hidden" name="boxchecked" value="0" />
</form>

<form action="{{ $template ? route('admin.news.templates') : route('admin.news.index') }}" method="post" class="form-inlin">
	<script id="mailpreview-template" type="text/x-handlebars-template">
		<div id="mail-recipients"><strong>To:</strong> <?php echo '{{resourcelist}}'; ?> Users</div>
		<div id="mail-from"><strong>From:</strong> YOU via <?php echo config('app.name'); ?></div>
		<div id="mail-subject"><strong>Subject:</strong> <?php echo '{{subject}} - {{formatteddate}}'; ?></div>
		<hr />
		<div id="mail-meta">
			<strong><?php echo '{{subject}}'; ?></strong><br/>
			<?php echo '{{formatteddate}}'; ?><br/>
			<?php echo '{{locale}}'; ?><br/>
		</div>

		<?php echo '{{#if updates}}'; ?>
			<?php echo '{{#each updates}}'; ?>
				<span class="newsupdate" style="font-style: italic"><strong>UPDATE: <?php echo '{{this.formattedcreateddate}}'; ?></strong></span>
				<?php echo '{{{this.formattedbody}}}'; ?><br/>
			<?php echo '{{/each}}'; ?>
			<span class="newsupdate" style="font-style: italic"><strong>ORIGINAL: <?php echo '{{formattedcreateddate}}'; ?></strong></span>
		<?php echo '{{/if}}'; ?>
		<?php echo '{{{formattedbody}}}'; ?>

		<hr/>

		<div class="modal-pane">
			<?php echo '{{#if resources}}'; ?>
				<fieldset class="option-group">
					<legend>Send to resource mailing lists:</legend>
					<div class="row">
						<?php echo '{{#each resources}}'; ?>
							<div class="col-md-3">
								<label for="resource<?php echo '{{this.id}}'; ?>">
									<input type="checkbox" checked="checked" id="resource<?php echo '{{this.id}}'; ?>" value="<?php echo '{{this.id}}'; ?>" class="preview-resource" />
									<?php echo '{{this.name}}'; ?>
								</label>
							</div>
						<?php echo '{{/each}}'; ?>
					</div>
				</fieldset>
			<?php echo '{{/if}}'; ?>

			<div class="form-group row">
				<label for="newsuser" class="col-sm-2 col-form-label">Send to:</label>
				<div class="col-sm-10">
					<input type="text" name="to" id="mail-to" class="form-control form-users" data-api="{{ route('api.users.index') }}" value="" />
				</div>
			</div>
		</div>
	</script>

	<div class="modal" id="mailpreview-modal" tabindex="-1" aria-labelledby="mailpreview-title" aria-hidden="true">
		<div class="modal-dialog modal-lg modal-dialog-centered">
			<div class="modal-content shadow-sm">
				<div class="modal-header">
					<div class="modal-title" id="mailpreview-title">Mail Preview</div>
					<button type="button" class="btn-close close" data-bs-dismiss="modal" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true" class="visually-hidden">&times;</span>
					</button>
				</div>
				<div class="modal-body" id="mailpreview">
					<div class="spinner-border" role="status">
						<span class="sr-only visually-hidden">Loading...</span>
					</div>
				</div>
				<div class="modal-footer text-right text-end">
					<button id="mailsend" data-bs-dismiss="modal" data-dismiss="modal" class="btn btn-success" data-confirm="You have unsaved changes that need to be saved before mailing news item. Would you like to save the changes?">Send mail</button>
				</div>
			</div>
		</div>
	</div>

	@csrf
</form>

<div class="modal" id="copy-article" tabindex="-1" aria-labelledby="copy-article-title" aria-hidden="true">
	<div class="modal-dialog modal-lg modal-dialog-centered">
		<div class="modal-content shadow-sm">
			<div class="modal-header">
				<div class="modal-title" id="copy-article-title">{{ trans('news::news.copy article') }}</div>
				<button type="button" class="btn-close close" data-bs-dismiss="modal" data-dismiss="modal" aria-label="Close">
					<span class="visually-hidden" aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<form method="post" action="{{ route('admin.news.copy') }}">
					<h2 class="modal-title sr-only visually-hidden">{{ trans('news::news.copy article') }}</h2>

					<div class="px-3">
						<?php /*<div class="row">
							<div class="col-md-6">*/ ?>
						<div class="form-group">
							<label class="form-label" for="copy-start">{{ trans('news::news.copy to') }}:</label>
							<span class="input-group input-datetime">
								<input type="text" class="form-control date" name="start" id="copy-start" value="{{ Carbon\Carbon::now()->modify('+1 day')->format('Y-m-d') }}" />
								<span class="input-group-append"><span class="input-group-text fa fa-calendar"></span></span>
							</span>
						</div>

						<?php /*<div class="form-group">
							<label class="form-label" for="copy-days">{{ trans('news::news.days to copy') }}:</label>
							<input type="number" class="form-control" name="days" id="copy-days" value="1" />
						</div>

							</div>
							<div class="col-md-6">å
								<div class="form-group">
									<label class="form-label" for="copy-days">{{ trans('news::news.times to copy') }}:</label>
									<select class="form-control datetime" name="times" id="copy-times" multiple size="7">
										<?php
										$now   = Carbon\Carbon::now();
										$start = '07:00:00';
										$end   = '19:00:00';
										$date = Carbon\Carbon::parse($now->format('Y-m-d') . ' ' . $start);
										$date_end = Carbon\Carbon::parse($now->format('Y-m-d') . ' ' . $end);

										for ($date; $date <= $date_end; $date->modify('+30 Minutes'))
										{
											?>
											<option value="<?php echo $date->format('h:i a'); ?>"><?php echo $date->format('h:i a'); ?></option>
											<?php
										}
										?>
									</select>
								</div>
							</div>
						</div>*/ ?>

						<p class="text-center">
							<input type="submit" class="btn btn-primary" value="{{ trans('news::news.copy') }}" />
						</p>
					</div>

					@csrf
				</form>
			</div>
		</div>
	</div>
</div>

@stop