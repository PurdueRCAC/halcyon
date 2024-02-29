@extends('layouts.master')

@push('styles')
<link rel="stylesheet" type="text/css" media="all" href="{{ timestamped_asset('modules/core/vendor/tom-select/css/tom-select.bootstrap4.min.css') }}" />
@endpush

@push('scripts')
<script src="{{ timestamped_asset('modules/core/vendor/tom-select/js/tom-select.complete.min.js') }}"></script>
<script src="{{ timestamped_asset('modules/contactreports/js/admin.js') }}"></script>
@endpush

@php
app('request')->merge(['hidemainmenu' => 1]);

app('pathway')
	->append(
		trans('contactreports::contactreports.module name'),
		route('admin.contactreports.index')
	)
	->append(
		($row->id ? trans('global.edit') . ' #' . $row->id : trans('global.create'))
	);
@endphp

@section('toolbar')
	@if (auth()->user()->can('edit contactreports'))
		{!! Toolbar::save(route('admin.contactreports.store')) !!}
	@endif

	{!!
		Toolbar::spacer();
		Toolbar::cancel(route('admin.contactreports.cancel'));
	!!}

	{!! Toolbar::render() !!}
@stop

@section('title')
{{ trans('contactreports::contactreports.module name') }}: {{ $row->id ? trans('global.edit') . ': #' . $row->id : trans('global.create') }}
@stop

@section('content')
<form action="{{ route('admin.contactreports.store') }}" method="post" name="adminForm" id="item-form" class="editform form-validate">

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
		<div class="col-sm-12 col-md-7 mx-auto">
			<fieldset class="adminform">
				<legend>{{ trans('global.details') }}</legend>

				<div class="row">
					<div class="col-md-5">
						<div class="form-group">
							<label for="field-datetimecontact">{{ trans('contactreports::contactreports.contacted') }} <span class="required">{{ trans('global.required') }}</span></label>
							{!! Html::input('calendar', 'fields[datetimecontact]', $row->datetimecontact ? $row->datetimecontact->format('Y-m-d') : '', ['required' => true, 'time' => false]) !!}
							<span class="invalid-feedback">{{ trans('contactreports::contactreports.invalid.contacted') }}</span>
						</div>
					</div>
					<div class="col-md-7">
						<div class="form-group">
							<label for="field-contactreporttypeid">{{ trans('contactreports::contactreports.type') }}</label>
							<select name="fields[contactreporttypeid]" id="field-contactreporttypeid" class="form-control">
								<option value="0"<?php if (!$row->contactreporttypeid) { echo ' selected="selected"'; } ?>>{{ trans('global.none') }}</option>
								@foreach ($types as $type)
									<option value="{{ $type->id }}"<?php if ($row->contactreporttypeid == $type->id) { echo ' selected="selected"'; } ?>>{{ $type->name }}</option>
								@endforeach
							</select>
						</div>
					</div>
				</div>

				@if (\Nwidart\Modules\Facades\Module::isEnabled('resources'))
				<div class="form-group">
					<label for="field-resources">{{ trans('contactreports::contactreports.resources') }}</label>
					<select class="form-control basic-multiple" name="resources[]" multiple="multiple" data-placeholder="">
						<?php
						$r = $row->resources->pluck('resourceid')->toArray();
						$resources = App\Modules\Resources\Models\Asset::orderBy('name', 'asc')->get();
						foreach ($resources as $resource):
							?>
							<option value="{{ $resource->id }}"<?php if (in_array($resource->id, $r)) { echo ' selected="selected"'; } ?>>{{ $resource->name }}</option>
							<?php
						endforeach;
						?>
					</select>
				</div>
				@endif

				<div class="form-group">
					<?php
					$users = array();
					foreach ($row->users as $user):
						$users[] = ($user->user ? $user->user->name : trans('global.unknown')) . ':' . $user->userid;
					endforeach;
					?>
					<label for="field-people">{{ trans('contactreports::contactreports.users') }}</label>
					<input type="text" name="people" id="field-people" class="form-control form-users" data-uri="{{ url('/') }}/api/users/?api_token={{ auth()->user()->api_token }}&search=%s" value="{{ implode(',', $users) }}" />
				</div>

				@if (\Nwidart\Modules\Facades\Module::isEnabled('resources'))
				<div class="form-group">
					<label for="field-groupid">{{ trans('contactreports::contactreports.group') }}</label>
					<select name="fields[groupid]" id="field-groupid" class="form-control searchable-select">
						<option value="0"<?php if (!$row->groupid) { echo ' selected="selected"'; } ?>>{{ trans('global.none') }}</option>
						@foreach ($groups as $group)
							<option value="{{ $group->id }}"<?php if ($row->groupid == $group->id) { echo ' selected="selected"'; } ?>>{{ $group->name }}</option>
						@endforeach
					</select>
				</div>
				@endif

				<div class="form-group">
					<label for="field-report">{{ trans('contactreports::contactreports.report') }} <span class="required">{{ trans('global.required') }}</span></label>
					{!! markdown_editor('fields[report]', $row->report, ['rows' => 15, 'class' => ($errors->has('fields.report') ? 'is-invalid' : 'required'), 'required' => 'required']) !!}
					<span class="form-text text-muted">{!! trans('contactreports::contactreports.report formatting') !!}</span>
					<span class="invalid-feedback">{{ trans('contactreports::contactreports.invalid.report') }}</span>
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

$help1d = "You can also mention and link another contact report by referencing its ID:

CRM#658
";

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
								<a href="#help1d" id="help1d-tab" class="nav-link" data-toggle="tab" role="tab" aria-controls="help1d" aria-selected="false">Other Reports</a>
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
							$article = new App\Modules\ContactReports\Models\Report(['report' => $help1a]);
							?>
							<div class="form-group">
								<label for="help1ainput">{{ trans('contactreports::contactreports.input text') }}:</label>
								<textarea id="help1ainput" class="form-control samplebox" rows="5" data-sample="a"><?php echo $help1a; ?></textarea>
							</div>
							<p>{{ trans('contactreports::contactreports.output text') }}:<p>
							<div id="help1aoutput" class="sampleoutput"><?php echo $article->toHtml(); ?></div>
						</div>
						<div class="tab-pane" id="help1b" role="tabpanel" aria-labelledby="help1b-tab">
							<?php
							$article = new App\Modules\ContactReports\Models\Report(['report' => $help1b]);
							?>
							<div class="form-group">
								<label for="help1binput">{{ trans('contactreports::contactreports.input text') }}:</label>
								<textarea id="help1binput" class="form-control samplebox" rows="5" data-sample="b"><?php echo $help1b; ?></textarea>
							</div>
							<p>{{ trans('contactreports::contactreports.output text') }}:<p>
							<div id="help1boutput" class="sampleoutput"><?php echo $article->toHtml(); ?></div>
						</div>
						<div class="tab-pane" id="help1c" role="tabpanel" aria-labelledby="help1c-tab">
							<?php
							$article = new App\Modules\ContactReports\Models\Report(['report' => $help1c]);
							?>
							<div class="form-group">
								<label for="help1cinput">{{ trans('contactreports::contactreports.input text') }}:</label>
								<textarea id="help1cinput" class="form-control samplebox" rows="5" data-sample="c"><?php echo $help1c; ?></textarea>
							</div>
							<p>{{ trans('contactreports::contactreports.output text') }}:<p>
							<div id="help1coutput" class="sampleoutput"><?php echo $article->toHtml(); ?></div>
						</div>
						<div class="tab-pane" id="help1d" role="tabpanel" aria-labelledby="help1d-tab">
							<?php
							$article = new App\Modules\ContactReports\Models\Report(['report' => $help1d]);
							?>
							<div class="form-group">
								<label for="help1dinput">{{ trans('contactreports::contactreports.input text') }}:</label>
								<textarea id="help1dinput" class="form-control samplebox" rows="5" data-sample="d"><?php echo $help1d; ?></textarea>
							</div>
							<p>{{ trans('contactreports::contactreports.output text') }}:<p>
							<div id="help1doutput" class="sampleoutput"><?php echo $article->toHtml(); ?></div>
						</div>
						<div class="tab-pane" id="help1e" role="tabpanel" aria-labelledby="help1e-tab">
							<?php
							$article = new App\Modules\ContactReports\Models\Report(['report' => $help1e]);
							?>
							<div class="form-group">
								<label for="help1einput">{{ trans('contactreports::contactreports.input text') }}:</label>
								<textarea id="help1einput" class="form-control samplebox" rows="5" data-sample="e"><?php echo $help1e; ?></textarea>
							</div>
							<p>{{ trans('contactreports::contactreports.output text') }}:<p>
							<div id="help1eoutput" class="sampleoutput"><?php echo $article->toHtml(); ?></div>
						</div>
						<div class="tab-pane" id="help1f" role="tabpanel" aria-labelledby="help1f-tab">
							<?php
							$article = new App\Modules\ContactReports\Models\Report(['report' => $help1f]);
							?>
							<div class="form-group">
								<label for="help1finput">{{ trans('contactreports::contactreports.input text') }}:</label>
								<textarea id="help1finput" class="form-control samplebox" rows="5" data-sample="f"><?php echo $help1f; ?></textarea>
							</div>
							<p>{{ trans('contactreports::contactreports.output text') }}:<p>
							<div id="help1foutput" class="sampleoutput"><?php echo $article->toHtml(); ?></div>
						</div>
						<div class="tab-pane" id="help1g" role="tabpanel" aria-labelledby="help1g-tab">
							<?php
							$article = new App\Modules\ContactReports\Models\Report(['report' => $help1g]);
							?>
							<div class="form-group">
								<label for="help1ginput">{{ trans('contactreports::contactreports.input text') }}:</label>
								<textarea id="help1ginput" class="form-control samplebox" rows="5" data-sample="g"><?php echo $help1g; ?></textarea>
							</div>
							<p>{{ trans('contactreports::contactreports.output text') }}:<p>
							<div id="help1goutput" class="sampleoutput"><?php echo $article->toHtml(); ?></div>
						</div>
					</div>

				</div><!-- / .modal-body -->
			</div><!-- / .modal-content -->
		</div><!-- / .modal-dialog -->
	</div><!-- / .modal -->

	<input type="hidden" name="id" id="field-id" value="{{ $row->id }}" />

	@csrf
</form>
@stop