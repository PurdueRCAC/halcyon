@extends('layouts.master')

@push('styles')
<link rel="stylesheet" type="text/css" media="all" href="{{ asset('modules/core/vendor/tagsinput/jquery.tagsinput.css?v=' . filemtime(public_path() . '/modules/core/vendor/tagsinput/jquery.tagsinput.css')) }}" />
<!-- <link rel="stylesheet" type="text/css" media="all" href="{{ asset('modules/core/vendor/select2/css/select2.css?v=' . filemtime(public_path() . '/modules/core/vendor/select2/css/select2.css')) }}" /> -->
@endpush

@push('scripts')
<script src="{{ asset('modules/core/vendor/tagsinput/jquery.tagsinput.js?v=' . filemtime(public_path() . '/modules/core/vendor/tagsinput/jquery.tagsinput.js')) }}"></script>
<!-- <script src="{{ asset('modules/core/vendor/select2/js/select2.min.js?v=' . filemtime(public_path() . '/modules/core/vendor/select2/js/select2.min.js')) }}"></script> -->
<script src="{{ asset('modules/courses/js/admin.js?v=' . filemtime(public_path() . '/modules/courses/js/admin.js')) }}"></script>
@endpush

@php
app('request')->merge(['hidemainmenu' => 1]);

app('pathway')
	->append(
		trans('courses::courses.module name'),
		route('admin.courses.index')
	)
	->append(
		trans('courses::courses.mail')
	);
@endphp

@section('toolbar')
	@if (auth()->user()->can('edit courses'))
		{!! Toolbar::save(route('admin.courses.send'), 'Send') !!}
	@endif

	{!!
		Toolbar::spacer();
		Toolbar::cancel(route('admin.courses.cancel'));
	!!}

	{!! Toolbar::render() !!}
@stop

@section('title')
{!! config('courses.name') !!}
@stop

@section('content')
<form action="{{ route('admin.courses.send') }}" method="post" name="adminForm" id="item-form" class="editform">

	@if ($errors->any())
		<div class="alert alert-danger">
			<ul>
				@foreach ($errors->all() as $error)
					<li>{{ $error }}</li>
				@endforeach
			</ul>
		</div>
	@endif

	<div class="row">
		<div class="col-md-7">
			<fieldset class="adminform">
				<legend>{{ trans('global.details') }}</legend>

				<div class="form-group">
					<label for="field-subject">{{ trans('courses::courses.subject') }} <span class="required">{{ trans('global.required') }}</span></label>
					<input type="text" name="subject" id="field-subject" class="form-control" required maxlength="255" value="" />
				</div>

				<div class="form-group">
					<label for="field-body">{{ trans('courses::courses.body') }} <span class="required">{{ trans('global.required') }}</span></label>
					{!! markdown_editor('body', '', ['rows' => 15, 'class' => ($errors->has('body') ? 'is-invalid' : 'required'), 'required' => 'required']) !!}
					<span class="form-text text-muted">{!! trans('courses::courses.body formatting') !!}</span>
					<span class="invalid-feedback">{{ trans('courses::courses.invalid.body') }}</span>
				</div>
			</fieldset>

			<fieldset class="adminform">
				<legend>{{ trans('courses::courses.recipients') }}</legend>

				<div class="form-group">
					<label for="field-bcc">{{ trans('courses::courses.bcc') }}</label>
					<input type="text" name="bcc" id="field-bcc" class="form-control form-users" data-uri="{{ url('/') }}/api/users/?api_token={{ auth()->user()->api_token }}&search=%s" value="" />
				</div>

				<!-- <div class="form-group">
					<div class="form-check">
						<input type="checkbox" id="user-self" name="self" value="{{ auth()->user()->id }}" class="form-check-input" />
						<label for="user-self" class="form-check-label">Send a copy to yourself</label>
					</div>
				</div> -->

				<table class="table">
					<caption class="sr-only">{{ trans('courses::courses.recipients') }}</caption>
					<thead>
						<tr>
							<th scope="col">Instructor</th>
							<th scope="col">Courses</th>
						</tr>
					</thead>
					<tbody>
						<?php
						$i = 0;
						?>
						<tr>
							<td>
								<div class="form-check">
									<input type="checkbox" id="user-{{ $i }}" name="user[{{ $i }}]" value="{{ auth()->user()->id }}" class="form-check-input" />
									<label for="user-{{ $i }}" class="form-check-label">{{ auth()->user()->name }} <span class="badge badge-info">You</span></label>
								</div>
							</td>
							<td>
								--
							</td>
						</tr>
					<?php
					$i++;
					foreach ($users as $userid => $courses):
						$user = App\Modules\Users\Models\User::find($userid);
						if (!$user):
							continue;
						endif;
						?>
						<tr>
							<td>
								<div class="form-check">
									<input type="checkbox" id="user-{{ $i }}" name="user[{{ $i }}]" value="{{ $userid }}" checked="checked" class="form-check-input" />
									<label for="user-{{ $i }}" class="form-check-label">{{ $user->name }}</label>
								</div>
							</td>
							<td>
								<ul>
								@foreach ($courses as $course)
									<li>{{ $course->isWorkshop() ? '' : $course->department . ' ' . $course->coursenumber . ': ' }}{{ $course->classname }} ({{ $course->semester }})</li>
								@endforeach
								</ul>
							</td>
						</tr>
						<?php
						$i++;
					endforeach;
					?>
					</tbody>
				</table>
			</fieldset>
		</div>
		<div class="col-md-5">
			<div class="help" id="markdown">
				<table class="table table-bordered">
					<caption>MarkDown Quick Guide</caption>
					<thead>
						<tr>
							<th scope="col">MarkDown</th>
							<th scope="col">HTML</th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td>*bold*</td>
							<td><strong>bold</strong></td>
						</tr>
						<tr>
							<td>_italic_</td>
							<td><em>italic</em></td>
						</tr>
						<tr>
							<td>`code`</td>
							<td><code>code</code></td>
						</tr>
						<tr>
							<td>[a link](https//:somewhere.com)</td>
							<td><a href="https//:somewhere.com">a link</a></td>
						</tr>
						<tr>
							<td>```<br />
code<br />
block<br />
```
							</td>
							<td><pre>code
block</pre></td>
						</tr>
						<tr>
							<td><pre>* Bullet 1
* Bullet 2</pre>
							</td>
							<td>
								<ul>
									<li>Bullet 1</li>
									<li>Bullet 2</li>
								</ul>
							</td>
						</tr>
						<tr>
							<td><pre>1. Bullet 1
2. Bullet 2</pre>
							</td>
							<td>
								<ol>
									<li>Bullet 1</li>
									<li>Bullet 2</li>
								</ol>
							</td>
						</tr>
						<tr>
							<td><pre>| Column 1 | Column 2 |
|----------|----------|
| item     | thing    |
| stuff    | object   |</pre>
							</td>
							<td>
								<table class="table table-bordered">
									<thead>
										<th scope="col">Column 1</th>
										<th scope="col">Column 2</th>
									</thead>
									<tbody>
										<tr>
											<td>item</td>
											<td>thing</td>
										</tr>
										<tr>
											<td>stuff</td>
											<td>object</td>
										</tr>
									</tbody>
								</table>
							</td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>
	</div>

	@csrf
</form>
@stop
