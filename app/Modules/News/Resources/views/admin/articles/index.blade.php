@extends('layouts.master')

@push('styles')
<link rel="stylesheet" type="text/css" media="all" href="{{ asset('modules/core/vendor/tagsinput/jquery.tagsinput.css?v=' . filemtime(public_path() . '/modules/core/vendor/tagsinput/jquery.tagsinput.css')) }}" />
<link rel="stylesheet" type="text/css" media="all" href="{{ asset('modules/core/vendor/select2/css/select2.css?v=' . filemtime(public_path() . '/modules/core/vendor/select2/css/select2.css')) }}" />
@endpush

@push('scripts')
<script>
$(document).ready(function() {
	var cdialog = $("#copy-article").dialog({
		autoOpen: false,
		height: 250,
		width: 500,
		modal: true
	});

	$('#toolbar-copy>.btn-copy').removeClass('toolbar-submit').off('click').on('click', function(e){
		e.preventDefault();

		//$('#adminForm').addClass('frozen');

		cdialog.dialog("open");
	});
	$("#copy-article").find('.btn').on('click', function(e){
		e.preventDefault();
		console.log('foo');
		console.log($(this).closest('form'));
		console.log($('#adminForm').find('input:checked'));
		$(this).closest('form').append($('#adminForm').find('input:checked')).submit();
	})
});
</script>
<script src="{{ asset('modules/core/vendor/handlebars/handlebars.min-v4.7.6.js?v=' . filemtime(public_path() . '/modules/core/vendor/handlebars/handlebars.min-v4.7.6.js')) }}"></script>
<script src="{{ asset('modules/core/vendor/tagsinput/jquery.tagsinput.js?v=' . filemtime(public_path() . '/modules/core/vendor/tagsinput/jquery.tagsinput.js')) }}"></script>
<script src="{{ asset('modules/core/vendor/select2/js/select2.min.js?v=' . filemtime(public_path() . '/modules/core/vendor/select2/js/select2.min.js')) }}"></script>
<script src="{{ asset('modules/news/js/admin.js?v=' . filemtime(public_path() . '/modules/news/js/admin.js')) }}"></script>
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
		{!! Toolbar::deleteList(trans('global.confirm delete'), route('admin.news.delete')) !!}
	@endif

	@if (auth()->user()->can('create news'))
		{!! Toolbar::spacer() !!}
		{!! Toolbar::custom(route('admin.news.copy'), 'copy', 'copy', trans('news::news.copy'), true) !!}
		{!! Toolbar::addNew(route('admin.news.create')) !!}
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
{!! config('news.name') !!}{{ $template ? ': ' . trans('news::news.templates') : '' }}
@stop

@section('content')

@component('news::admin.submenu')
	@if (request()->segment(3) == 'templates')
		templates
	@else
		articles
	@endif
@endcomponent

<form action="{{ $template ? route('admin.news.templates') : route('admin.news.index') }}" method="post" name="adminForm" id="adminForm" class="form-inline">

	<fieldset id="filter-bar" class="container-fluid">
		<div class="row">
			<div class="col col-md-4 filter-search">
				<div class="form-group">
					<label class="sr-only" for="filter_search">{{ trans('search.label') }}</label>
					<span class="input-group">
						<input type="text" name="search" id="filter_search" class="form-control filter" placeholder="{{ trans('search.placeholder') }}" value="{{ $filters['search'] }}" />
						<span class="input-group-append"><span class="input-group-text"><span class="icon-search" aria-hidden="true"></span></span></span>
					</span>
				</div>
			</div>
			<div class="col col-md-8 filter-select text-right">
				<label class="sr-only" for="filter_state">{{ trans('news::news.state') }}</label>
				<select name="state" class="form-control filter filter-submit">
					<option value="*"<?php if ($filters['state'] == '*'): echo ' selected="selected"'; endif;?>>{{ trans('news::news.state_all') }}</option>
					<option value="published"<?php if ($filters['state'] == 'published'): echo ' selected="selected"'; endif;?>>{{ trans('news::news.published') }}</option>
					<option value="unpublished"<?php if ($filters['state'] == 'unpublished'): echo ' selected="selected"'; endif;?>>{{ trans('news::news.unpublished') }}</option>
				</select>

				<?php /*@if (!$template)
				<label class="sr-only" for="filter-access">{{ trans('news::news.access level') }}</label>
				<select name="access" id="filter-access" class="form-control filter filter-submit">
					<option value="*">{{ trans('news::news.select access') }}</option>
					<?php foreach (App\Halcyon\Access\Viewlevel::all() as $access): ?>
						<option value="<?php echo $access->id; ?>"<?php if ($filters['access'] == $access->id) { echo ' selected="selected"'; } ?>>{{ $access->title }}</option>
					<?php endforeach; ?>
				</select>
				@endif*/ ?>

				<label class="sr-only" for="filter-type">{{ trans('news::news.type') }}</label>
				<select name="type" id="filter-type" class="form-control filter filter-submit">
					<option value="0">{{ trans('news::news.select type') }}</option>
					<?php foreach ($types as $type): ?>
						<option value="<?php echo $type->id; ?>"<?php if ($filters['type'] == $type->id) { echo ' selected="selected"'; } ?>>{{ $type->name }}</option>
					<?php endforeach; ?>
				</select>
			</div>
		</div>

		<input type="hidden" name="order" value="{{ $filters['order'] }}" />
		<input type="hidden" name="order_dir" value="{{ $filters['order_dir'] }}" />

		<button class="btn btn-secondary sr-only" type="submit">{{ trans('search.submit') }}</button>
	</fieldset>

	<!-- Experimental Vue rendering
	<div id="app">
		<table-component></table-component>
	</div>
	<script src="{{ asset('modules/news/js/app-admin.js?v=3') }}"></script>
	-->

	<div class="card mb-4">
	<table class="table table-hover adminlist">
		<caption class="sr-only">{{ $template ? trans('news::news.articles') : trans('news::news.templates') }}</caption>
		<thead>
			<tr>
				@if (auth()->user()->can('delete news'))
					<th>
						{!! Html::grid('checkall') !!}
					</th>
				@endif
				<th scope="col" class="priority-5">
					{!! Html::grid('sort', trans('news::news.id'), 'id', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col">
					{!! Html::grid('sort', trans('news::news.headline'), 'headline', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col" class="priority-2">
					{!! Html::grid('sort', trans('news::news.state'), 'state', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col" class="priority-4">
					{!! Html::grid('sort', trans('news::news.type'), 'newstypeid', $filters['order_dir'], $filters['order']) !!}
				</th>
				@if (!$template)
					<th scope="col" colspan="3" class="text-center priority-4">
						{!! Html::grid('sort', trans('news::news.publish window'), 'datetimenews', $filters['order_dir'], $filters['order']) !!}
					</th>
					<th scope="col" class="priority-5 text-right">{{ trans('news::news.updates') }}</th>
					<th scope="col" class="priority-6 text-right">{{ trans('news::news.email') }}</th>
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
				<td class="priority-5">
					{{ $row->id }}
				</td>
				<td>
					@if (auth()->user()->can('edit news'))
						<a href="{{ route('admin.news.edit', ['id' => $row->id]) }}">
							@if ($row->headline)
								{{ Illuminate\Support\Str::limit($row->headline, 70) }}
							@else
								<span class="none">{{ trans('global.none') }}</span>
							@endif
						</a>
					@else
						@if ($row->headline)
							{{ Illuminate\Support\Str::limit($row->headline, 70) }}
						@else
							<span class="none">{{ trans('global.none') }}</span>
						@endif
					@endif

					@if ($row->isMailed())
						<div class="text-muted">
							Last emailed:
							<time datetime="{{ $row->datetimemailed->format('Y-m-d\TH:i:s\Z') }}">
								{{ $row->datetimemailed->format('M j, Y g:ia') }}
							</time>
							by {{ $row->mailer ? $row->mailer->name : trans('global.unknown') }}
						</div>
					@endif
				</td>
				<td class="priority-2">
					@if (auth()->user()->can('edit.state news'))
						@if ($row->published)
							<a class="badge badge-success" href="{{ route('admin.news.unpublish', ['id' => $row->id]) }}" data-tip="{{ trans('news::news.click to unpublish') }}">
								{{ trans('global.published') }}
							</a>
						@else
							<a class="badge badge-secondary" href="{{ route('admin.news.publish', ['id' => $row->id]) }}" data-tip="{{ trans('news::news.click to publish') }}">
								{{ trans('global.unpublished') }}
							</a>
						@endif
					@else
						@if ($row->published)
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
				<td class="priority-4">
					{{ $row->type->name }}
				</td>
				@if (!$template)
					<td class="priority-4 text-right text-nowrap">
						@if ($row->hasStart())
							<time datetime="{{ $row->datetimenews->format('Y-m-d\TH:i:s\Z') }}">
								{{ $row->datetimenews->format('M j, Y g:ia') }}
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
					<td class="priority-4 text-nowrap">
						@if ($row->hasStart())
							@if ($row->hasEnd())
								<time datetime="{{ $row->datetimenewsend->format('Y-m-d\TH:i:s\Z') }}">
									{{ $row->isSameDay() ? $row->datetimenewsend->format('g:ia') : $row->datetimenewsend->format('M j, Y g:ia') }}
								</time>
							@else
								<span class="never">{{ trans('global.never') }}</span>
							@endif
						@else
							<span class="none">{{ trans('global.none') }}</span>
						@endif
					</td>
					<td class="priority-5 text-right">
						<a href="{{ route('admin.news.updates', ['article' => $row->id]) }}">
							{{ $row->updates_count }}
						</a>
					</td>
					<td class="priority-6 text-right">
						<button class="btn news-mail" data-success="Email sent!" data-article="{{ route('api.news.read', ['id' => $row->id]) }}" data-api="{{ route('api.news.email', ['id' => $row->id]) }}" data-tip="{{ trans('news::news.send email') }}">
							<span class="icon-mail glyph">Email</span>
						</button>
					</td>
				@endif
			</tr>
		@endforeach
		</tbody>
	</table>
	</div>

	{{ $rows->render() }}

	<script id="mailpreview-template" type="text/x-handlebars-template">
		<div id="mail-recipients">To: <?php echo '{{resourcelist}}'; ?> Users</div>
		<div id="mail-from">From: YOU via <?php echo config('app.name'); ?></div>
		<div id="mail-subject">Subject: <?php echo '{{subject}} - {{formatteddate}}'; ?></div>
		<hr />
		<div id="mail-meta">
			<strong><?php echo '{{subject}}'; ?></strong><br/>
			<?php echo '{{formatteddate}}'; ?><br/>
			<?php echo '{{locale}}'; ?><br/>
		</div>

		<?php echo '{{#if updates}}'; ?>
			<?php echo '{{#each updates}}'; ?>
				<span class="newsupdate" style="font-style: italic"><strong>UPDATE: <?php echo '{{formattedcreateddate}}'; ?></strong></span>
				<?php echo '{{{formattedbody}}}'; ?><br/>
			<?php echo '{{/each}}'; ?>
			<span class="newsupdate" style="font-style: italic"><strong>ORIGINAL: <?php echo '{{formattedcreateddate}}'; ?></strong></span>
		<?php echo '{{/if}}'; ?>
		<?php echo '{{{formattedbody}}}'; ?>

		<hr/>
		<a href="<?php echo '{{uri}}'; ?>">ITaP Research Computing News</a> from YOU<br/>
		<br/>
		Please reply to <a href="mailto:<?php echo config('mail.from.address'); ?>"><?php echo config('mail.from.address'); ?></a> with any questions or concerns.<br/>
		<a href="<?php echo '{{uri}}'; ?>">View this article on the web.</a>

		<div class="ui-dialog-pane-highlight">
			<?php echo '{{#if resources}}'; ?>
				<fieldset class="option-group">
					<legend>Send to resource mailing lists:</legend>
					<div class="row">
						<?php echo '{{#each resources}}'; ?>
							<div class="col-md-3">
								<label>
									<input type="checkbox" checked="checked" value="<?php echo '{{resourceid}}'; ?>" class="preview-resource" />
									<?php echo '{{resource.name}}'; ?>
								</label>
							</div>
						<?php echo '{{/each}}'; ?>
					</div>
				</fieldset>
			<?php echo '{{/if}}'; ?>

			<div class="form-group row">
				<label for="newsuser" class="col-sm-2 col-form-label">Send to:</label>
				<div class="col-sm-10">
					<input type="text" name="to" id="mail-to" class="form-control form-users" data-uri="{{ route('api.users.index') }}?api_token={{ auth()->user()->api_token }}&search=%s" value="" />
				</div>
			</div>
		</div>
	</script>
	<div id="mailpreview" class="dialog" title="Mail Preview">
	</div>

	@csrf
	<input type="hidden" name="boxchecked" value="0" />
</form>

<dialog id="copy-article" class="dialog" title="{{ trans('news::news.copy article') }}">
	<form method="post" action="{{ route('admin.news.copy') }}">
		<h2 class="modal-title sr-only">{{ trans('news::news.copy article') }}</h2>

		<div class="px-3">
			<?php /*<div class="row">
				<div class="col-md-6">*/ ?>
			<div class="form-group">
				<label for="copy-start">{{ trans('news::news.copy to') }}:</label>
				<span class="input-group input-datetime">
					<input type="text" class="form-control date" name="start" id="copy-start" value="{{ Carbon\Carbon::now()->modify('+1 day')->format('Y-m-d') }}" />
					<span class="input-group-append"><span class="input-group-text icon-calendar"></span></span>
				</span>
			</div>

			<?php /*<div class="form-group">
				<label for="copy-days">{{ trans('news::news.days to copy') }}:</label>
				<input type="number" class="form-control" name="days" id="copy-days" value="1" />
			</div>

				</div>
				<div class="col-md-6">å
					<div class="form-group">
						<label for="copy-days">{{ trans('news::news.times to copy') }}:</label>
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
</dialog>

@stop