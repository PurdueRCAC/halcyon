@extends('layouts.master')

@push('styles')
<link rel="stylesheet" type="text/css" media="all" href="{{ asset('modules/core/vendor/tom-select/css/tom-select.bootstrap4.min.css?v=' . filemtime(public_path('/modules/core/vendor/tom-select/css/tom-select.bootstrap4.min.css'))) }}" />
@endpush

@push('scripts')
<script src="{{ asset('modules/core/vendor/tom-select/js/tom-select.complete.min.js?v=' . filemtime(public_path('/modules/core/vendor/tom-select/js/tom-select.complete.min.js'))) }}"></script>
<script src="{{ asset('modules/mailer/js/admin.js?v=' . filemtime(public_path() . '/modules/mailer/js/admin.js')) }}"></script>
@endpush

@php
app('request')->merge(['hidemainmenu' => 1]);

app('pathway')
	->append(
		trans('mailer::mailer.module name'),
		route('admin.mailer.index')
	)
	->append(
		trans('global.create')
	);
@endphp

@section('toolbar')
	{!! Toolbar::save(route('admin.mailer.send'), trans('mailer::mailer.send')) !!}
	{!! Toolbar::cancel(route('admin.mailer.cancel')) !!}
	{!! Toolbar::render() !!}
@stop

@section('subject')
{{ trans('mailer::mailer.module name') }}: {{ trans('mailer::mailer.create') }}
@stop

@section('content')
<form action="{{ route('admin.mailer.send') }}" method="post" name="adminForm" id="item-form" class="editform form-validate">

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
		<div class="col col-md-7">
			<fieldset class="adminform">
				<legend>{{ trans('global.details') }}</legend>

				<div class="row">
					<div class="col-md-5">
						<div class="form-group">
							<label for="field-fromemail">{{ trans('mailer::mailer.from email') }} <span class="required">{{ trans('global.required') }}</span></label>
							<input type="email" name="fromemail" id="field-fromemail" class="form-control{{ $errors->has('fromemail') ? ' is-invalid' : '' }}" required maxlength="320" value="{{ config('mail.from.address') }}" data-value="{{ config('mail.from.address') }}" />
							<span class="invalid-feedback">{{ trans('mailer::mailer.invalid.subject') }}</span>
							{!! $errors->first('fromemail', '<span class="form-text text-danger">:message</span>') !!}
						</div>
					</div>
					<div class="col-md-6">
						<div class="form-group">
							<label for="field-fromname">{{ trans('mailer::mailer.from name') }}</label>
							<input type="text" name="fromname" id="field-fromname" class="form-control" maxlength="150" value="{{ config('mail.from.name') }}" data-value="{{ config('mail.from.name') }}" />
						</div>
					</div>
					<div class="col-md-1">
						<div class="form-group">
							<br />
							<div class="form-check">
								<input type="checkbox" name="fromme" id="field-fromme" class="form-check-input" value="{{ auth()->user()->email }}" data-name="{{ auth()->user()->name }}" />
								<label for="field-fromme" class="form-check-label">{{ trans('mailer::mailer.from me') }}</label>
							</div>
						</div>
					</div>
				</div>

				<div class="form-group">
					<label for="field-template">{{ trans('mailer::mailer.template') }}</label>
					<select name="usetemplate" id="field-template" class="form-control">
						<option value="">{{ trans('global.none') }}</option>
						@foreach ($templates as $template)
							<option value="template{{ $template->id }}"<?php if ($row->id == $template->id) { echo ' selected'; } ?>>{{ $template->name ? $template->name : $template->subject }}</option>
						@endforeach
					</select>
					@foreach ($templates as $template)
						<input type="hidden" name="template{{ $template->id }}body" id="template{{ $template->id }}body" value="{{ $template->body }}" />
						<input type="hidden" name="template{{ $template->id }}subject" id="template{{ $template->id }}subject" value="{{ $template->subject }}" />
					@endforeach
				</div>

				<div class="form-group">
					<label for="field-subject">{{ trans('mailer::mailer.subject') }} <span class="required">{{ trans('global.required') }}</span></label>
					<input type="text" name="subject" id="field-subject" class="form-control{{ $errors->has('subject') ? ' is-invalid' : '' }}" required maxlength="250" value="{{ $row->subject }}" />
					<span class="invalid-feedback">{{ trans('mailer::mailer.invalid.subject') }}</span>
					{!! $errors->first('subject', '<span class="form-text text-danger">:message</span>') !!}
				</div>

				<div class="form-group">
					<label for="field-body">{{ trans('mailer::mailer.body') }} <span class="required">{{ trans('global.required') }}</span></label>
					{!! markdown_editor('body', $row->body, ['id' => 'field-body', 'rows' => 50, 'class' => ($errors->has('body') ? 'is-invalid' : 'required'), 'required' => 'required']) !!}
					<span class="form-text text-muted">{!! trans('mailer::mailer.body formatting') !!}</span>
					<span class="invalid-feedback">{{ trans('mailer::mailer.invalid.body') }}</span>
					{!! $errors->first('body', '<span class="form-text text-danger">:message</span>') !!}
				</div>
			</fieldset>

			<fieldset class="adminform">
				<legend>{{ trans('mailer::mailer.options') }}</legend>

				<div class="form-group">
					<label for="field-alert">{{ trans('mailer::mailer.alert level') }}</label>
					<select name="alert" id="field-alert" class="form-control">
						<option value=""<?php if (!$row->alert) { echo ' selected'; } ?>>{{ trans('global.none') }}</option>
						<option value="info"<?php if ($row->alert == 'info') { echo ' selected'; } ?>>{{ trans('mailer::mailer.alert.info') }}</option>
						<option value="warning"<?php if ($row->alert == 'warning') { echo ' selected'; } ?>>{{ trans('mailer::mailer.alert.warning') }}</option>
						<option value="danger"<?php if ($row->alert == 'danger') { echo ' selected'; } ?>>{{ trans('mailer::mailer.alert.danger') }}</option>
					</select>
					<span class="form-text text-muted">{{ trans('mailer::mailer.alert level description') }}</span>
				</div>
			</fieldset>

			<fieldset class="adminform">
				<legend>{{ trans('mailer::mailer.variables') }}</legend>

				<div class="form-group">
					<p>{{ trans('mailer::mailer.variable replacement') }}</p>
					<table>
						<thead>
							<tr>
								<th scope="col">{{ trans('mailer::mailer.variable') }}</th>
								<th scope="col">{{ trans('mailer::mailer.example result') }}</th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td><code>{user.id}</code></td>
								<td>{{ auth()->user()->id }}</td>
							</tr>
							<tr>
								<td><code>{user.name}</code></td>
								<td>{{ auth()->user()->name }}</td>
							</tr>
							<tr>
								<td><code>{user.username}</code></td>
								<td>{{ auth()->user()->username }}</td>
							</tr>
							<tr>
								<td><code>{user.email}</code></td>
								<td>{{ auth()->user()->email }}</td>
							</tr>
							<tr>
								<td><code>{site.name}</code></td>
								<td>{{ config('app.name') }}</td>
							</tr>
							<tr>
								<td><code>{site.url}</code></td>
								<td>{{ url('/') }}</td>
							</tr>
						</tbody>
					</table>
				</div>
			</fieldset>
		</div>
		<div class="col col-md-5">
			<fieldset class="adminform">
				<legend>{{ trans('mailer::mailer.send to') }}</legend>

				<div class="form-group">
					<label for="field-user">{{ trans('mailer::mailer.to individuals') }}</label>
					<input type="text" name="user" id="field-user" class="form-control form-users" data-uri="{{ route('api.users.index') }}?api_token={{ auth()->user()->api_token }}&search=%s" value="" />
					<span class="form-text text-muted">{{ trans('mailer::mailer.send to hint') }}</span>
				</div>

				<details>
					<summary class="mb-4">Bulk options</summary>
					<div>
						<div class="form-group">
							<label for="field-group">{{ trans('mailer::mailer.to group') }}</label>
							<input type="text" name="group" id="field-group" class="form-control form-groups" data-uri="{{ route('api.groups.index') }}?api_token={{ auth()->user()->api_token }}&search=%s" value="" />
							<div class="alert alert-warning d-none" id="field-group-confirmation">{{ trans('mailer::mailer.group confirmation') }}</div>
						</div>

						<fieldset class="form-group" id="field-roles">
							<legend>{{ trans('mailer::mailer.to role') }}</legend>
							<div class="alert alert-warning d-none">{{ trans('mailer::mailer.role confirmation') }}</div>
							<?php
							echo App\Halcyon\Html\Builder\Access::roles('role', [], true);
							?>
						</fieldset>
					</div>
				</details>

				<div class="form-group">
					<label for="field-cc">{{ trans('mailer::mailer.cc') }}</label>
					<input type="text" name="cc" id="field-cc" class="form-control form-users" data-uri="{{ url('/') }}/api/users/?api_token={{ auth()->user()->api_token }}&search=%s" value="" />
					<span class="form-text text-muted">{{ trans('mailer::mailer.send to hint') }}</span>
				</div>

				<div class="form-group">
					<label for="field-bcc">{{ trans('mailer::mailer.bcc') }}</label>
					<input type="text" name="bcc" id="field-bcc" class="form-control form-users" data-uri="{{ url('/') }}/api/users/?api_token={{ auth()->user()->api_token }}&search=%s" value="" />
					<span class="form-text text-muted">{{ trans('mailer::mailer.send to hint') }}</span>
				</div>
			</fieldset>
		</div>
	</div>

		<?php
$help1a = "The news interface supports basic font formatting:

**Bold** _example_, or you can have **_both_**.

These examples are fully interactive. Just type in the top box and see the formatting below live.";

$help1b = "Unordered lists can be made using '-' or '*' to denote list items. Ordered lists can be made in a similar fashion.
- This
- Is
* A
* List

1) One
2) Two
3. Three";

$help1c = "Hyperlinks can be made in the following way.

http://www.example.edu

[Example University](http://www.example.edu)

By using [Title] notation immediately preceding a URL in parentheses, you can give it another title.

Email addresses will automatically be converted into mailto links: help@example.edu";

$help1e = "      The news interface will ignore any artificial
line breaking or   extra spaces .
A full empty line is required to
get a line break to display.



As well, extra line breaks are
ignored.";

$help1f = "Inline code can be created with single back-ticks to mark the beginning and end. Example: `this is inline code`. Code blocks can be created using triple back-ticks to mark the beginning and end of a code block. Text inside the code block will be exempt from other formatting rules and will display exactly as typed.

```
// This is an example of some code

int main (int argc, char * argv[]) {
    printf(\"hello world!\\n\");
    return 0;
}
```
";

$help1g = "Tables can be created using \"|\" to start a line to mark the beginning and end of a table row. Cell divisions in the table are marked by a single \"|\". The other formatting rules apply within the cells.

| *Node*   | *Cores* | *Memory* |
|----------|--------:|---------:|
| Carter-A |      16 |     32GB |
| Carter-B |      16 |     64GB |
";
?>
	<div class="modal dialog" id="markdown" tabindex="-1" aria-labelledby="markdown-title" aria-hidden="true" title="MarkDown Help">
		<div class="modal-dialog modal-dialog-centered">
			<div class="modal-content dialog-content shadow-sm">
				<div class="modal-header">
					<div class="modal-title" id="markdown-title">MarkDown Help</div>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body dialog-body">

					<nav class="container-fluid">
						<ul id="help-tabs" class="nav nav-tabs" role="tablist">
							<li class="nav-item" role="presentation">
								<a href="#help1a" id="help1a-tab" class="nav-link active" data-toggle="tab" role="tab" aria-controls="help1a" aria-selected="true">Fonts</a>
							</li>
							<li class="nav-item" role="presentation">
								<a href="#help1b" id="help1b-tab" class="nav-link" data-toggle="tab" role="tab" aria-controls="help1b" aria-selected="false">Lists</a>
							</li>
							<li class="nav-item" role="presentation">
								<a href="#help1c" id="help1c-tab" class="nav-link" data-toggle="tab" role="tab" aria-controls="help1c" aria-selected="false">Links</a>
							</li>
							<li class="nav-item" role="presentation">
								<a href="#help1e" id="help1e-tab" class="nav-link" data-toggle="tab" role="tab" aria-controls="help1e" aria-selected="false">Line Breaks</a>
							</li>
							<li class="nav-item" role="presentation">
								<a href="#help1f" id="help1f-tab" class="nav-link" data-toggle="tab" role="tab" aria-controls="help1f" aria-selected="false">Code</a>
							</li>
							<li class="nav-item" role="presentation">
								<a href="#help1g" id="help1g-tab" class="nav-link" data-toggle="tab" role="tab" aria-controls="help1g" aria-selected="false">Tables</a>
							</li>
						</ul>
					</nav>
					<div class="tab-content" id="help-tabs-contant">
						<div class="tab-pane show active" id="help1a" role="tabpanel" aria-labelledby="help1a-tab">
							<?php
							$converter = new League\CommonMark\CommonMarkConverter([
								'html_input' => 'allow',
							]);
							$converter->getEnvironment()->addExtension(new League\CommonMark\Extension\Table\TableExtension());
							$converter->getEnvironment()->addExtension(new League\CommonMark\Extension\Strikethrough\StrikethroughExtension());
							$converter->getEnvironment()->addExtension(new League\CommonMark\Extension\Autolink\AutolinkExtension());

							$text = (string) $converter->convertToHtml($help1a);
							?>
							<div class="form-group">
								<label for="help1ainput">{{ trans('mailer::mailer.input text') }}:</label>
								<textarea id="help1ainput" class="form-control samplebox" rows="5" data-sample="a"><?php echo $help1a; ?></textarea>
							</div>
							<p>{{ trans('mailer::mailer.output text') }}:<p>
							<div id="help1aoutput" class="sampleoutput"><?php echo $text; ?></div>
						</div>
						<div class="tab-pane" id="help1b" role="tabpanel" aria-labelledby="help1b-tab">
							<?php
							$text = (string) $converter->convertToHtml($help1b);
							?>
							<div class="form-group">
								<label for="help1binput">{{ trans('mailer::mailer.input text') }}:</label>
								<textarea id="help1binput" class="form-control samplebox" rows="5" data-sample="b"><?php echo $help1b; ?></textarea>
							</div>
							<p>{{ trans('mailer::mailer.output text') }}:<p>
							<div id="help1boutput" class="sampleoutput"><?php echo $text; ?></div>
						</div>
						<div class="tab-pane" id="help1c" role="tabpanel" aria-labelledby="help1c-tab">
							<?php
							$text = (string) $converter->convertToHtml($help1c);
							?>
							<div class="form-group">
								<label for="help1cinput">{{ trans('mailer::mailer.input text') }}:</label>
								<textarea id="help1cinput" class="form-control samplebox" rows="5" data-sample="c"><?php echo $help1c; ?></textarea>
							</div>
							<p>{{ trans('mailer::mailer.output text') }}:<p>
							<div id="help1coutput" class="sampleoutput"><?php echo $text; ?></div>
						</div>
						<div class="tab-pane" id="help1e" role="tabpanel" aria-labelledby="help1e-tab">
							<?php
							$text = (string) $converter->convertToHtml($help1e);
							?>
							<div class="form-group">
								<label for="help1einput">{{ trans('mailer::mailer.input text') }}:</label>
								<textarea id="help1einput" class="form-control samplebox" rows="5" data-sample="e"><?php echo $help1e; ?></textarea>
							</div>
							<p>{{ trans('mailer::mailer.output text') }}:<p>
							<div id="help1eoutput" class="sampleoutput"><?php echo $text; ?></div>
						</div>
						<div class="tab-pane" id="help1f" role="tabpanel" aria-labelledby="help1f-tab">
							<?php
							$text = (string) $converter->convertToHtml($help1f);
							?>
							<div class="form-group">
								<label for="help1finput">{{ trans('mailer::mailer.input text') }}:</label>
								<textarea id="help1finput" class="form-control samplebox" rows="5" data-sample="f"><?php echo $help1f; ?></textarea>
							</div>
							<p>{{ trans('mailer::mailer.output text') }}:<p>
							<div id="help1foutput" class="sampleoutput"><?php echo $text; ?></div>
						</div>
						<div class="tab-pane" id="help1g" role="tabpanel" aria-labelledby="help1g-tab">
							<?php
							$text = (string) $converter->convertToHtml($help1g);
							?>
							<div class="form-group">
								<label for="help1ginput">{{ trans('mailer::mailer.input text') }}:</label>
								<textarea id="help1ginput" class="form-control samplebox" rows="5" data-sample="g"><?php echo $help1g; ?></textarea>
							</div>
							<p>{{ trans('mailer::mailer.output text') }}:<p>
							<div id="help1goutput" class="sampleoutput"><?php echo $text; ?></div>
						</div>
					</div>

				</div><!-- / .modal-body -->
			</div><!-- / .modal-content -->
		</div><!-- / .modal-dialog -->
	</div><!-- / .modal -->

	@csrf
</form>
@stop