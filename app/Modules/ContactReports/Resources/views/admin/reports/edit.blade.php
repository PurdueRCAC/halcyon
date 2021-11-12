@extends('layouts.master')

@section('styles')
<link rel="stylesheet" type="text/css" media="all" href="{{ asset('modules/core/vendor/tagsinput/jquery.tagsinput.css?v=' . filemtime(public_path() . '/modules/core/vendor/tagsinput/jquery.tagsinput.css')) }}" />
<link rel="stylesheet" type="text/css" media="all" href="{{ asset('modules/core/vendor/select2/css/select2.css?v=' . filemtime(public_path() . '/modules/core/vendor/select2/css/select2.css')) }}" />
@stop

@section('scripts')
<script src="{{ asset('modules/core/vendor/tagsinput/jquery.tagsinput.js?v=' . filemtime(public_path() . '/modules/core/vendor/tagsinput/jquery.tagsinput.js')) }}"></script>
<script src="{{ asset('modules/core/vendor/select2/js/select2.min.js?v=' . filemtime(public_path() . '/modules/core/vendor/select2/js/select2.min.js')) }}"></script>
<script src="{{ asset('modules/contactreports/js/admin.js?v=' . filemtime(public_path() . '/modules/contactreports/js/admin.js')) }}"></script>
@stop

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
{!! config('contactreports.name') !!}: {{ $row->id ? 'Edit: #' . $row->id : 'Create' }}
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
		<div class="col-md-7">
			<fieldset class="adminform">
				<legend>{{ trans('global.details') }}</legend>

				<div class="form-group">
					<label for="field-datetimecontact">{{ trans('contactreports::contactreports.contacted') }}: <span class="required">{{ trans('global.required') }}</span></label>
					{!! Html::input('calendar', 'fields[datetimecontact]', $row->datetimecontact ? $row->datetimecontact->format('Y-m-d') : '', ['required' => true, 'time' => false]) !!}
					<span class="invalid-feedback">{{ trans('contactreports::contactreports.invalid.contacted') }}</span>
				</div>

				<div class="form-group">
					<label for="field-contactreporttypeid">{{ trans('contactreports::contactreports.type') }}:</label>
					<select name="fields[contactreporttypeid]" id="field-contactreporttypeid" class="form-control">
						<option value="0"<?php if (!$row->contactreporttypeid) { echo ' selected="selected"'; } ?>>{{ trans('global.none') }}</option>
						@foreach ($types as $type)
							<option value="{{ $type->id }}"<?php if ($row->contactreporttypeid == $type->id) { echo ' selected="selected"'; } ?>>{{ $type->name }}</option>
						@endforeach
					</select>
				</div>

				<div class="form-group">
					<?php
					/*$resources = array();
					foreach ($row->resources as $resource)
					{
						$resources[] = ($resource->resource ? $resource->resource->name : trans('global.unknown')) . ':' . $resource->resourceid;
					}*/
					?>
					<label for="field-resources">{{ trans('contactreports::contactreports.resources') }}:</label>
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

				<div class="form-group">
					<?php
					$users = array();
					foreach ($row->users as $user):
						$users[] = ($user->user ? $user->user->name : trans('global.unknown')) . ':' . $user->userid;
					endforeach;
					?>
					<label for="field-people">{{ trans('contactreports::contactreports.users') }}:</label>
					<input type="text" name="people" id="field-people" class="form-control form-users" data-uri="{{ url('/') }}/api/users/?api_token={{ auth()->user()->api_token }}&search=%s" value="{{ implode(',', $users) }}" />
				</div>

				<div class="form-group">
					<label for="field-groupid">{{ trans('contactreports::contactreports.group') }}:</label>
					<select name="fields[groupid]" id="field-groupid" class="form-control searchable-select">
						<option value="0"<?php if (!$row->groupid) { echo ' selected="selected"'; } ?>>{{ trans('global.none') }}</option>
						@foreach ($groups as $group)
							<option value="{{ $group->id }}"<?php if ($row->groupid == $group->id) { echo ' selected="selected"'; } ?>>{{ $group->name }}</option>
						@endforeach
					</select>
				</div>

				<div class="form-group">
					<label for="field-report">{{ trans('contactreports::contactreports.report') }}: <span class="required">{{ trans('global.required') }}</span></label>
					<span class="form-text text-muted">{!! trans('contactreports::contactreports.report formatting') !!}</span>
					{!! markdown_editor('fields[report]', $row->report, ['rows' => 15, 'class' => ($errors->has('fields.report') ? 'is-invalid' : 'required'), 'required' => 'required']) !!}
					<span class="invalid-feedback">{{ trans('contactreports::contactreports.invalid.report') }}</span>
				</div>
			</fieldset>
		</div>
		<div class="col-md-5">
			@if ($row->id)
				<fieldset class="adminform">
					<legend>{{ trans('contactreports::contactreports.comments') }}</legend>

					<ul id="comments">
					<?php
					$comments = $row->comments()->orderBy('datetimecreated', 'asc')->get();

					if (count($comments) > 0):
					?>
						@foreach ($comments as $comment)
						<li id="comment_{{ $comment->id }}" data-api="{{ route('api.contactreports.comments.update', ['id' => $comment->id]) }}">
							<a href="#comment_{{ $comment->id }}_comment" class="btn btn-link comment-edit hide-when-editing">
								<span class="fa fa-pencil"><span class="sr-only">{{ trans('global.button.edit') }}</span></span>
							</a>
							<a href="#comment_{{ $comment->id }}" class="btn btn-link comment-delete" data-confirm="{{ trans('global.confirm delete') }}">
								<span class="fa fa-trash"><span class="sr-only">{{ trans('global.button.delete') }}</span></span>
							</a>
							<div id="comment_{{ $comment->id }}_text">
								{!! $comment->formattedComment !!}
							</div>
							<div id="comment_{{ $comment->id }}_edit" class="show-when-editing">
								<div class="form-group">
									<label for="comment_{{ $comment->id }}_comment" class="sr-only">{{ trans('contactreports::contactreports.comment') }}</label>
									<textarea name="comment" id="comment_{{ $comment->id }}_comment" class="form-control" cols="45" rows="3">{{ $comment->comment }}</textarea>
								</div>
								<div class="form-group text-right">
									<button class="btn btn-secondary comment-save" data-parent="#comment_{{ $comment->id }}">{{ trans('global.button.save') }}</button>
									<a href="#comment_{{ $comment->id }}" class="btn btn-link comment-cancel">
										{{ trans('global.button.cancel') }}
									</a>
								</div>
							</div>
							<p>{{ trans('contactreports::contactreports.posted by', ['who' => ($comment->creator ? $comment->creator->name : trans('global.unknown')), 'when' => $comment->datetimecreated->toDateTimeString()]) }}</p>
						</li>
						@endforeach
					<?php
					endif;
					?>
						<li id="comment_<?php echo '{id}'; ?>" class="d-none" data-api="{{ route('api.contactreports.comments') }}/<?php echo '{id}'; ?>">
							<a href="#comment_<?php echo '{id}'; ?>_comment" class="btn btn-link comment-edit hide-when-editing">
								<span class="fa fa-pencil"><span class="sr-only">{{ trans('global.button.edit') }}</span></span>
							</a>
							<a href="#comment_<?php echo '{id}'; ?>" class="btn btn-link comment-delete" data-confirm="{{ trans('global.confirm delete') }}">
								<span class="fa fa-trash"><span class="sr-only">{{ trans('global.button.delete') }}</span></span>
							</a>
							<div id="comment_<?php echo '{id}'; ?>_text">
							</div>
							<div id="comment_<?php echo '{id}'; ?>_edit" class="show-when-editing">
								<div class="form-group">
									<label for="comment_<?php echo '{id}'; ?>_comment" class="sr-only">{{ trans('contactreports::contactreports.comment') }}</label>
									<textarea name="comment" id="comment_<?php echo '{id}'; ?>_comment" class="form-control" cols="45" rows="3"></textarea>
								</div>
								<div class="form-group text-right">
									<button class="btn btn-secondary comment-save" data-parent="#comment_<?php echo '{id}'; ?>">{{ trans('global.button.save') }}</button>
									<a href="#comment_<?php echo '{id}'; ?>" class="btn btn-link comment-cancel">
										{{ trans('global.button.cancel') }}
									</a>
								</div>
							</div>
							<p>{{ trans('contactreports::contactreports.posted by', ['who' => '{who}', 'when' => '{when}']) }}</p>
						</li>
						<li id="comment_new" data-api="{{ route('api.contactreports.comments.create') }}">
							<div class="form-group">
								<label for="comment_new_comment" class="sr-only">{{ trans('contactreports::contactreports.comment') }}</label>
								<textarea name="comment" id="comment_new_comment" class="form-control" cols="45" rows="3"></textarea>
							</div>
							<div class="form-group text-right">
								<button class="btn btn-secondary comment-add" data-parent="#comment_new">{{ trans('contactreports::contactreports.add') }}</button>
							</div>
						</li>
					</ul>
				</fieldset>
			@endif
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
	<div id="markdown" class="dialog dialog-help tabs" title="MarkDown Help">
		<ul>
			<li><a href="#help1a">Fonts</a></li>
			<li><a href="#help1b">Lists</a></li>
			<li><a href="#help1c">Links</a></li>
			<li><a href="#help1d">Other Reports</a></li>
			<li><a href="#help1e">Line Breaks</a></li>
			<li><a href="#help1f">Code</a></li>
			<li><a href="#help1g">Tables</a></li>
		</ul>
		<div id="help1a">
			<?php
			$article = new App\Modules\ContactReports\Models\Report(['report' => $help1a]);
			?>
			<div class="form-group">
				<label for="help1ainput">{{ trans('contactreports::contactreports.input text') }}:</label>
				<textarea id="help1ainput" class="form-control samplebox" rows="5" data-sample="a"><?php echo $help1a; ?></textarea>
			</div>
			<p>{{ trans('contactreports::contactreports.output text') }}:<p>
			<div id="help1aoutput" class="sampleoutput"><?php echo $article->formattedReport; ?></div>
		</div>
		<div id="help1b">
			<?php
			$article = new App\Modules\ContactReports\Models\Report(['report' => $help1b]);
			?>
			<div class="form-group">
				<label for="help1binput">{{ trans('contactreports::contactreports.input text') }}:</label>
				<textarea id="help1binput" class="form-control samplebox" rows="5" data-sample="b"><?php echo $help1b; ?></textarea>
			</div>
			<p>{{ trans('contactreports::contactreports.output text') }}:<p>
			<div id="help1boutput" class="sampleoutput"><?php echo $article->formattedReport; ?></div>
		</div>
		<div id="help1c">
			<?php
			$article = new App\Modules\ContactReports\Models\Report(['report' => $help1c]);
			?>
			<div class="form-group">
				<label for="help1cinput">{{ trans('contactreports::contactreports.input text') }}:</label>
				<textarea id="help1cinput" class="form-control samplebox" rows="5" data-sample="c"><?php echo $help1c; ?></textarea>
			</div>
			<p>{{ trans('contactreports::contactreports.output text') }}:<p>
			<div id="help1coutput" class="sampleoutput"><?php echo $article->formattedReport; ?></div>
		</div>
		<div id="help1d">
			<?php
			$article = new App\Modules\ContactReports\Models\Report(['report' => $help1d]);
			?>
			<div class="form-group">
				<label for="help1dinput">{{ trans('contactreports::contactreports.input text') }}:</label>
				<textarea id="help1dinput" class="form-control samplebox" rows="5" data-sample="d"><?php echo $help1d; ?></textarea>
			</div>
			<p>{{ trans('contactreports::contactreports.output text') }}:<p>
			<div id="help1doutput" class="sampleoutput"><?php echo $article->formattedReport; ?></div>
		</div>
		<div id="help1e">
			<?php
			$article = new App\Modules\ContactReports\Models\Report(['report' => $help1e]);
			?>
			<div class="form-group">
				<label for="help1einput">{{ trans('contactreports::contactreports.input text') }}:</label>
				<textarea id="help1einput" class="form-control samplebox" rows="5" data-sample="e"><?php echo $help1e; ?></textarea>
			</div>
			<p>{{ trans('contactreports::contactreports.output text') }}:<p>
			<div id="help1eoutput" class="sampleoutput"><?php echo $article->formattedReport; ?></div>
		</div>
		<div id="help1f">
			<?php
			$article = new App\Modules\ContactReports\Models\Report(['report' => $help1f]);
			?>
			<div class="form-group">
				<label for="help1finput">{{ trans('contactreports::contactreports.input text') }}:</label>
				<textarea id="help1finput" class="form-control samplebox" rows="5" data-sample="f"><?php echo $help1f; ?></textarea>
			</div>
			<p>{{ trans('contactreports::contactreports.output text') }}:<p>
			<div id="help1foutput" class="sampleoutput"><?php echo $article->formattedReport; ?></div>
		</div>
		<div id="help1g">
			<?php
			$article = new App\Modules\ContactReports\Models\Report(['report' => $help1g]);
			?>
			<div class="form-group">
				<label for="help1ginput">{{ trans('contactreports::contactreports.input text') }}:</label>
				<textarea id="help1ginput" class="form-control samplebox" rows="5" data-sample="g"><?php echo $help1g; ?></textarea>
			</div>
			<p>{{ trans('contactreports::contactreports.output text') }}:<p>
			<div id="help1goutput" class="sampleoutput"><?php echo $article->formattedReport; ?></div>
		</div>
	</div>

	<input type="hidden" name="id" id="field-id" value="{{ $row->id }}" />

	@csrf
</form>
@stop