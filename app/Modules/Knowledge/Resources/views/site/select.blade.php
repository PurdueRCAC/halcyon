@extends('layouts.master')

@push('styles')
<link rel="stylesheet" type="text/css" media="all" href="{{ timestamped_asset('modules/core/vendor/tom-select/css/tom-select.bootstrap4.min.css') }}" />
<link rel="stylesheet" type="text/css" media="all" href="{{ timestamped_asset('modules/knowledge/css/knowledge.css') }}" />
@endpush

@push('scripts')
<script src="{{ timestamped_asset('modules/core/vendor/tom-select/js/tom-select.complete.min.js') }}"></script>
<script src="{{ timestamped_asset('modules/knowledge/js/site.js') }}"></script>
@endpush

@php
app('pathway')
	->append(
		trans('knowledge::knowledge.module name'),
		route('site.knowledge.index')
	)
	->append(
		$node->page->headline,
		route('site.knowledge.page', ['uri' => $node->path])
	)
	->append(
		trans('knowledge::knowledge.attach')
	);
@endphp

@section('title')
{!! config('knowledge.name') !!}: {{ trans('knowledge::knowledge.attach') }}
@stop

@section('content')
<div class="row">
<div class="sidenav col-lg-3 col-md-3 col-sm-12 col-xs-12">
	@php
	$children = $root->publishedChildren();
	$path = explode('/', $node->path);
	@endphp
	@include('knowledge::site.list', ['nodes' => $children, 'path' => '', 'current' => $path, 'variables' => $root->page->variables])
</div>

<div class="contentInner col-lg-9 col-md-9 col-sm-12 col-xs-12">
	<div class="row">
		<h2>{{ $node->page->headline }}</h2>

		<form action="{{ route('site.knowledge.attach') }}" method="post" name="adminForm" id="item-form" class="editform w-100">

			@if ($errors->any())
				<div class="alert alert-danger">
					<ul>
						@foreach ($errors->all() as $error)
							<li>{{ $error }}</li>
						@endforeach
					</ul>
				</div>
			@endif

			<fieldset class="adminform">
				<legend>{{ trans('knowledge::knowledge.attach child page') }}</legend>

				<div class="form-group">
					<label for="field-parent_id">{{ trans('knowledge::knowledge.parent') }}:</label>
					<select name="parent_id" id="field-parent_id" class="form-control searchable-select">
						<?php
						$page = null;
						foreach ($parents as $pa): ?>
							<?php
							$selected = ($pa->id == $node->id ? ' selected="selected"' : '');
							?>
							<option value="{{ $pa->id }}"<?php echo $selected; ?> data-path="/{{ $pa->path }}"><?php echo '/' . ltrim($pa->path, '/')  . ' &mdash; ' . e(Illuminate\Support\Str::limit($pa->title, 70)); ?></option>
						<?php endforeach; ?>
					</select>
				</div>

				<table class="table table-hover">
					<tbody>
						@foreach ($snippets as $snippet)
							<tr<?php if ($snippet->level > 1) { echo ' class="d-none"'; } ?> id="snippet{{ $snippet->id }}" data-parent="{{ $snippet->parent_id }}">
								<td>
									<a href="#snippet{{ $snippet->id }}" class="toggle-tree" data-id="{{ $snippet->id }}">
										<span class="sr-only">{{ trans('knowledge::knowledge.toggle open close') }}</span>
									</a>
								</td>
								<td>
									{!! str_repeat('<span class="gi text-muted">|&mdash;</span>', $snippet->level - 1) !!}
									<input type="checkbox" name="snippets[{{ $snippet->parent_id }}][{{ $snippet->id }}][page_id]" id="snippet{{ $snippet->id }}" data-id="{{ $snippet->id }}" value="{{ $snippet->page_id }}" class="snippet-checkbox" />
									<label for="snippet{{ $snippet->id }}">{{ Illuminate\Support\Str::limit($snippet->title, 70) }}</label>
								</td>
								<td>
									<span class="form-text text-muted">{{ $snippet->path }}</span>
								</td>
								<td>
									<label for="field-{{ $snippet->id }}-access" class="sr-only">{{ trans('knowledge::knowledge.access') }}:</label>
									<select class="form-control" name="snippets[{{ $snippet->parent_id }}][{{ $snippet->id }}][access]" id="field-{{ $snippet->id }}-access">
										@foreach (App\Halcyon\Access\Viewlevel::all() as $access)
											<option value="{{ $access->id }}">{{ $access->title }}</option>
										@endforeach
									</select>
								</td>
								<td>
									<label for="field-{{ $snippet->id }}-state" class="sr-only">{{ trans('knowledge::knowledge.state') }}:</label>
									<select class="form-control" name="snippets[{{ $snippet->parent_id }}][{{ $snippet->id }}][state]" id="field-{{ $snippet->id }}-state">
										<option value="1">{{ trans('global.published') }}</option>
										<option value="0">{{ trans('global.unpublished') }}</option>
									</select>
								</td>
							</tr>
						@endforeach
					</tbody>
				</table>
			</fieldset>

			<p class="text-center">
				<button type="submit" class="btn btn-primary">
					{{ trans('knowledge::knowledge.attach') }}
				</button>

				<a href="{{ $node ? route('site.knowledge.page', ['uri' => $node->path]) : route('site.knowledge.index') }}" class="btn">{{ trans('global.button.cancel') }}</a>
			</p>
			@csrf
		</form>
	</div>
</div>
</div>
@stop
