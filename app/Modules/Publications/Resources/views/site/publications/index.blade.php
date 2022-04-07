@extends('layouts.master')

@push('styles')
<link rel="stylesheet" type="text/css" media="all" href="{{ asset('modules/publications/css/publications.css?v=' . filemtime(public_path('/modules/publications/css/publications.css'))) }}" />
@endpush

@section('title'){{ trans('publications::publications.publications') }}@stop

@php
app('pathway')
	->append(
		trans('publications::publications.publications'),
		route('site.publications.index')
	);
@endphp

@section('content')
<div class="row">
<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">

<h2 class="mt-0">{{ trans('publications::publications.publications') }}</h2>

<form action="{{ route('site.publications.index') }}" method="get" class="row">
	<div class="sidenav col-lg-3 col-md-3 col-sm-12 col-xs-12">

		<fieldset class="filters mt-0">
			<div class="form-group">
				<label for="filter_search">{{ trans('search.label') }}</label>
				<input type="text" name="search" id="filter_search" class="form-control filter" placeholder="Find by author or title" value="{{ $filters['search'] }}" />
			</div>
			@if (auth()->user() && auth()->user()->can('manage publications'))
			<div class="form-group">
				<label for="filter_state">{{ trans('publications::publications.state') }}</label>
				<select name="state" id="filter_state" class="form-control filter filter-submit">
					<option value="*"<?php if ($filters['state'] == '*'): echo ' selected="selected"'; endif;?>>{{ trans('global.all states') }}</option>
					<option value="published"<?php if ($filters['state'] == 'published'): echo ' selected="selected"'; endif;?>>{{ trans('global.published') }}</option>
					<option value="unpublished"<?php if ($filters['state'] == 'unpublished'): echo ' selected="selected"'; endif;?>>{{ trans('global.unpublished') }}</option>
					<option value="trashed"<?php if ($filters['state'] == 'trashed'): echo ' selected="selected"'; endif;?>>{{ trans('global.trashed') }}</option>
				</select>
			</div>
			@endif
			<div class="form-group">
				<label for="filter_type">{{ trans('publications::publications.type') }}</label>
				<select name="type" id="filter_type" class="form-control filter filter-submit">
					<option value="*"<?php if ($filters['type'] == '*'): echo ' selected="selected"'; endif;?>>{{ trans('publications::publications.all types') }}</option>
					@foreach ($types as $type)
						<option value="{{ $type->alias }}"<?php if ($filters['type'] == $type->alias): echo ' selected="selected"'; endif;?>>{{ $type->name }}</option>
					@endforeach
				</select>
			</div>
			<div class="form-group">
				<label for="filter_year">{{ trans('publications::publications.year') }}</label>
				<select name="year" id="filter_year" class="form-control filter filter-submit">
					<option value="*"<?php if ($filters['year'] == '*'): echo ' selected="selected"'; endif;?>>{{ trans('publications::publications.all years') }}</option>
					@foreach ($years as $year)
						<option value="{{ $year }}"<?php if ($filters['year'] == $year): echo ' selected="selected"'; endif;?>>{{ $year }}</option>
					@endforeach
				</select>
			</div>

			<input type="hidden" name="filter_order" value="{{ $filters['order'] }}" />
			<input type="hidden" name="filter_order_dir" value="{{ $filters['order_dir'] }}" />

			<div class="text-center">
				<button class="btn btn-secondary btn-block" type="submit">{{ trans('publications::publications.filter') }}</button>
			</div>
		</fieldset>
	</div>
	<div class="contentInner col-lg-9 col-md-9 col-sm-12 col-xs-12">
		<?php /*
		@if (auth()->user() && auth()->user()->can('manage publications'))
			<div class="text-right">
				<div class="dropdown btn-group">
					<button class="btn btn-primary dropdown-toggle" type="button" id="exportbutton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
						<span class="fa fa-table" aria-hidden="true"></span> {{ trans('publications::publications.export') }}
					</button>
					<div class="dropdown-menu dropdown-menu-right" aria-labelledby="exportbutton">
						<?php
						$filters['export'] = 'bibtex';
						?>
						<a href="{{ route('site.publications.index', $filters) }}" class="dropdown-item">
							{{ trans('publications::publications.export options.bibtex') }}
						</a>
						<?php
						$filters['export'] = 'endnote';
						?>
						<a href="{{ route('site.publications.index', $filters) }}" class="dropdown-item">
							{{ trans('publications::publications.export options.endnote') }}
						</a>
						<?php
						$filters['export'] = 'csv';
						?>
						<a href="{{ route('site.publications.index', $filters) }}" class="dropdown-item">
							{{ trans('publications::publications.export options.csv') }}
						</a>
					</div>
				</div>
				<a href="#import-publications" class="btn btn-secondary btn-import">
					<span class="fa fa-upload" aria-hidden="true"></span> {{ trans('publications::publications.import') }}
				</a>
			</div>
		@endif
		*/ ?>

		<div id="applied-filters" aria-label="{{ trans('publications::publications.applied filters') }}">
			<p class="sr-only">{{ trans('publications::publications.applied filters') }}:</p>
			<ul class="filters-list">
				<?php
				$allfilters = collect($filters);

				$keys = ['search', 'type', 'year'];
				if (auth()->user() && auth()->user()->can('manage publications'))
				{
					$keys[] = 'state';
				}

				foreach ($keys as $key):
					if (!isset($filters[$key]) || !$filters[$key] || $filters[$key] == '*'):
						continue;
					endif;

					$f = $allfilters
						->reject(function($v, $k) use ($key)
						{
							$ks = ['export', 'limit', 'page', 'order', 'order_dir'];

							return (in_array($k, $ks));
						})
						->map(function($v, $k) use ($key)
						{
							if ($k == $key)
							{
								$v = '*';
								$v = (in_array($k, ['search']) ? '' : $v);
							}
							return $v;
						})
						->toArray();

					$val = $filters[$key];
					$val = ($val == '*' ? 'all' : $val);
					if ($key == 'status'):
						$val = trans('publications::publications.' . $val);
					endif;
					if ($key == 'type'):
						foreach ($types as $type):
							if ($val == $type->alias):
								$val = $type->name;
								break;
							endif;
						endforeach;
					endif;
					?>
					<li>
						<strong>{{ trans('publications::publications.' . $key) }}</strong>: {{ $val }}
						<a href="{{ route('site.publications.index', $f) }}" class="icon-remove filters-x" title="{{ trans('publications::publications.remove filter') }}">
							<span class="fa fa-times" aria-hidden="true"><span class="sr-only">{{ trans('publications::publications.remove filter') }}</span>
						</a>
					</li>
					<?php
				endforeach;
				?>
			</ul>
		</div>

	@if (count($rows))
				<ul>
			@foreach ($rows as $i => $row)
			<li>
				<div class="publication">
					{!! $row->toHtml() !!}
				</div>
			</li>
			@endforeach
			</ul>

		{{ $rows->render() }}
	@else
		<div class="placeholder card text-center">
			<div class="placeholder-body card-body">
				<span class="fa fa-ban" aria-hidden="true"></span>
				<p>{{ trans('global.no results') }}</p>
			</div>
		</div>
	@endif

	@csrf
	</div>
</form>

<div id="import-publications" class="dialog" title="{{ trans('publications::publications.import') }}">
	<form action="{{ route('site.publications.import') }}" method="post" enctype="multipart/form-data">
		<p>CSV, XLSX (Excel), and ODS files are accepted. The first row must be headers with at least the following columns: order <code>ID</code>, <code>purchaseio</code> or <code>purchasewbse</code>, and <code>paymentdocid</code>.</p>

		<div class="form-group dropzone">
			<div id="uploader" class="fallback" data-instructions="Click or Drop files" data-list="#uploader-list">
				<label for="upload">Choose a file<span class="dropzone__dragndrop"> or drag it here</span></label>
				<input type="file" name="file" id="upload" class="form-control-file" multiple="multiple" />
			</div>
			<div class="file-list" id="uploader-list"></div>
			<input type="hidden" name="tmp_dir" id="ticket-tmp_dir" value="{{ ('-' . time()) }}" />
		</div>

		<div class="text-center">
			<input class="order btn btn-primary" type="submit" value="Import" />
		</div>

		@csrf
	</form>
</div>
</div>
</div>
@stop