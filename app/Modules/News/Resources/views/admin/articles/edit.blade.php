@extends('layouts.master')

@push('styles')
<link rel="stylesheet" type="text/css" media="all" href="{{ asset('modules/core/vendor/tagsinput/jquery.tagsinput.css?v=' . filemtime(public_path() . '/modules/core/vendor/tagsinput/jquery.tagsinput.css')) }}" />
<link rel="stylesheet" type="text/css" media="all" href="{{ asset('modules/core/vendor/select2/css/select2.css?v=' . filemtime(public_path() . '/modules/core/vendor/select2/css/select2.css')) }}" />
@endpush

@push('scripts')
<script src="{{ asset('modules/core/vendor/tagsinput/jquery.tagsinput.js?v=' . filemtime(public_path() . '/modules/core/vendor/tagsinput/jquery.tagsinput.js')) }}"></script>
<script src="{{ asset('modules/core/vendor/select2/js/select2.min.js?v=' . filemtime(public_path() . '/modules/core/vendor/select2/js/select2.min.js')) }}"></script>
<script src="{{ asset('modules/news/js/admin.js?v=' . filemtime(public_path() . '/modules/news/js/admin.js')) }}"></script>
@endpush

@php
app('request')->merge(['hidemainmenu' => 1]);

app('pathway')
	->append(
		trans('news::news.module name'),
		route('admin.news.index')
	)
	->append(
		trans('news::news.articles'),
		route('admin.news.index')
	)
	->append(
		($row->id ? trans('global.edit') . ' #' . $row->id : trans('global.create'))
	);
@endphp

@section('toolbar')
	@if (auth()->user()->can('edit news'))
		{!! Toolbar::save(route('admin.news.store')) !!}
	@endif

	{!!
		Toolbar::spacer();
		Toolbar::cancel(route('admin.news.cancel'));
	!!}

	{!! Toolbar::render() !!}
@stop

@section('title')
{!! config('news.name') !!}: {{ $row->id ? 'Edit: #' . $row->id : 'Create' }}
@stop

@section('content')
<form action="{{ route('admin.news.store') }}" method="post" name="adminForm" id="item-form" class="editform form-validate">

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

				<div class="form-group form-block">
					<div class="form-check">
						<input type="checkbox" name="fields[template]" id="field-template" class="form-check-input" value="1"<?php if ($row->template) { echo ' checked="checked"'; } ?> />
						<label for="field-template" class="form-check-label">{{ trans('news::news.template') }}</label>
						<span class="form-text text-muted">Templates do not appear as public articles.</span>
					</div>
				</div>

				<div class="form-group template-hide{{ $row->template ? ' hide' : '' }}">
					<label for="template_select">Template</label>
					<select id="template_select" name="template_select" class="form-control">
						<option value="0">(No Template)</option>
						@foreach ($templates as $template)
							<option value="{{ route('api.news.read', ['id' => $template['id']]) }}" data-api="{{ route('api.news.read', ['id' => $template['id']]) }}">{{ $template['headline'] }}</option>
						@endforeach
					</select>
				</div>

				<div class="form-group">
					<label for="field-newstypeid">{{ trans('news::news.type') }}: <span class="required">{{ trans('global.required') }}</span></label>
					<select name="fields[newstypeid]" id="field-newstypeid" class="form-control required" required>
						<?php foreach ($types as $type): ?>
							<option value="{{ $type->id }}"<?php if ($row->newstypeid == $type->id): echo ' selected="selected"'; endif;?>
								data-tagresources="{{ $type->tagresources }}"
								data-tagusers="{{ $type->tagusers }}"
								data-location="{{ $type->location }}"
								data-url="{{ $type->url }}"
								data-future="{{ $type->future }}"
								data-ongoing="{{ $type->ongoing }}">{{ $type->name }}</option>
						<?php endforeach; ?>
					</select>
					<span class="invalid-feedback">{{ trans('news::news.error.invalid type') }}</span>
				</div>

				<div class="form-group">
					<label for="field-headline">{{ trans('news::news.headline') }}: <span class="required">{{ trans('global.required') }}</span></label>
					<input type="text" name="fields[headline]" id="field-headline" class="form-control required" required value="{{ $row->headline }}" />
					<span class="invalid-feedback">{{ trans('news::news.error.invalid headline') }}</span>
				</div>

				<div class="form-group type-option type-location <?php if (!$row->type->location) { echo ' d-none'; } ?>">
					<label for="field-location">{{ trans('news::news.location') }}:</label>
					<input type="text" name="fields[location]" id="field-location" class="form-control" value="{{ $row->location }}" />
				</div>

				<div class="form-group type-option type-url <?php if (!$row->type->url) { echo ' d-none'; } ?>">
					<label for="field-url">{{ trans('news::news.url') }}:</label>
					<input type="text" name="fields[url]" id="field-url" class="form-control" value="{{ $row->url }}" />
				</div>

				<div class="form-group type-option type-tagresources <?php if (!$row->type->tagresources) { echo ' d-none'; } ?>">
					<?php
					$r = array();
					/*foreach ($row->resources as $resource)
					{
						$r[] = $resource->resource->name . ':' . $resource->id;
					}*/
					?>
					<label for="field-resources">{{ trans('news::news.tag resources') }}:</label>
					<!-- <input type="text" name="resources" id="field-resources" class="form-control form-resources" data-uri="{{ url('/') }}/api/resources/?api_token={{ auth()->user()->api_token }}&search=%s" value="{{ implode(', ', $r) }}" /> -->
					<select class="form-control basic-multiple" name="resources[]" id="field-resources" multiple="multiple" data-placeholder="Select resource...">
						<?php
						$resources = App\Modules\Resources\Models\Asset::orderBy('name', 'asc')->get();
						foreach ($resources as $resource):
							$selected = '';
							foreach ($row->resources as $r):
								if ($r->resourceid == $resource->id):
									$selected = ' selected="selected"';
									break;
								endif;
							endforeach;
							?>
							<option value="{{ $resource->id }}"{!! $selected !!}>{{ $resource->name }}</option>
							<?php
						endforeach;
						?>
					</select>
				</div>

				<div class="form-group type-option type-tagusers <?php if (!$row->type->tagusers) { echo ' d-none'; } ?>">
					<?php
					$r = array();
					foreach ($row->associations()->where('assoctype', '=', 'user')->get() as $assoc):
						$u = App\Modules\Users\Models\User::find($assoc->associd);
						$r[] = ($u ? $u->name : trans('global.unknown')) . ':' . $assoc->id;
					endforeach;
					?>
					<label for="field-users">{{ trans('news::news.tag users') }}:</label>
					<input type="text" name="associations" id="field-users" class="form-control form-users" data-uri="{{ route('api.users.index') }}?api_token={{ auth()->user()->api_token }}&search=%s" value="{{ implode(', ', $r) }}" />
				</div>

				<div class="form-group">
					<label for="field-body">{{ trans('news::news.body') }}: <span class="required">{{ trans('global.required') }}</span></label>
					<span class="form-text text-muted">{!! trans('news::news.body formatting') !!} <button class="btn btn-link preview float-right" data-id="{{ $row->id }}" data-api="{{ route('api.news.preview') }}">Preview</button></span>
					{!! markdown_editor('fields[body]', $row->body, ['rows' => 35, 'class' => ($errors->has('fields.body') ? 'is-invalid' : 'required'), 'required' => 'required']) !!}
					<span class="invalid-feedback">{{ trans('queues::queues.error.invalid body') }}</span>
				</div>
			</fieldset>
		</div>
		<div class="col col-md-5">
			<fieldset class="adminform">
				<legend>{{ trans('global.publishing') }}</legend>

				<div class="form-group">
					<label for="field-published">{{ trans('pages::pages.state') }}:</label>
					<select name="fields[published]" id="field-published" class="form-control">
						<option value="0"<?php if ($row->published == 0) { echo ' selected="selected"'; } ?>>{{ trans('global.unpublished') }}</option>
						<option value="1"<?php if ($row->published == 1) { echo ' selected="selected"'; } ?>>{{ trans('global.published') }}</option>
					</select>
				</div>

				<div class="form-group template-hide{{ $row->template ? ' hide' : '' }}">
					<label for="field-datetimenews">{{ trans('news::news.publish up') }}:</label>
					<span class="input-group input-datetime">
						<input type="text" class="form-control datetime" name="fields[datetimenews]" id="field-datetimenews" value="{{ $row->hasStart() ? $row->datetimenews->toDateTimeString() : '' }}" placeholder="{{ trans('news::news.now') }}" />
						<span class="input-group-append"><span class="input-group-text icon-calendar"></span></span>
					</span>
				</div>

				<div class="form-group template-hide{{ $row->template ? ' hide' : '' }}">
					<label for="field-datetimenewsend">{{ trans('news::news.publish down') }}:</label>
					<span class="input-group input-datetime">
						<input type="text" class="form-control datetime" name="fields[datetimenewsend]" id="field-datetimenewsend" value="{{ $row->hasEnd() ? $row->datetimenewsend->toDateTimeString() : '' }}" placeholder="{{ trans('news::news.never') }}" />
						<span class="input-group-append"><span class="input-group-text icon-calendar"></span></span>
					</span>
				</div>
			</fieldset>

			<fieldset class="adminform">
				<legend>Variables</legend>

				<p>Variables can be included in the headline or body of an article. These allow for inserting information set in other fields, such as start date, end date, location, and/or resources.</p>

				<table class="table table-bordered">
					<caption class="sr-only">Available Variables</caption>
					<tbody>
						<tr>
							<th scope="row">%date%</th>
							<td>
								Includes end date &amp; time, if set. Example:<br />
								Thursday, April 15, 2021<br />
								April 15 - 16, 2021
							</td>
						</tr>
						<tr>
							<th scope="row">%datetime%</th>
							<td>
								Includes end date &amp; time, if set. Example:<br />
								Thursday, April 15, 2021 at 3:45pm<br />
								Thursday, April 15, 2021 from 3:45pm - 4:45pm<br />
								Thursday, April 15, 2021 at 3:45pm - Friday, April 16, 2021 at 3:45pm
							</td>
						</tr>
						<tr>
							<th scope="row">%time%</th>
							<td>
								Includes end date &amp; time, if set. Example:<br />
								3:45pm<br />
								3:45pm - 4:45pm
							</td>
						</tr>
						<tr>
							<th scope="row">%updatedatetime%</th>
							<td>
								Updated date &amp; time
							</td>
						</tr>
						<tr>
							<th scope="row">%startdatetime%</th>
							<td>
								Start date &amp; time
							</td>
						</tr>
						<tr>
							<th scope="row">%startdate%</th>
							<td>
								Start date
							</td>
						</tr>
						<tr>
							<th scope="row">%starttime%</th>
							<td>
								Start time
							</td>
						</tr>
						<tr>
							<th scope="row">%enddatetime%</th>
							<td>
								End date &amp; time
							</td>
						</tr>
						<tr>
							<th scope="row">%enddate%</th>
							<td>
								End date
							</td>
						</tr>
						<tr>
							<th scope="row">%endtime%</th>
							<td>
								End time
							</td>
						</tr>
						<tr>
							<th scope="row">%location%</th>
							<td>
								Location
							</td>
						</tr>
						<tr>
							<th scope="row">%resources%</th>
							<td>
								List of tagged resource names. Example:<br />
								Bell, Brown and Halstead
							</td>
						</tr>
					</tbody>
				</table>
			</fieldset>

			<input type="hidden" name="id" id="field-id" value="{{ $row->id }}" />

			<?php /*@include('history::admin.history')*/ ?>
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

1. One
2. Two
3. Three";

$help1c = "Hyperlinks can be made in the following way.

http://www.example.edu

[Example University](http://www.example.edu)

By using [Title] notation immediately preceding a URL in parentheses, you can give it another title.

Email addresses will automatically be converted into mailto links: help@example.edu";

$help1d = "You can also mention and link another news article by referencing its news ID and the title of the article will be automatically retrieved:

NEWS#658

or you can replace the title of the article in the same way as hyperlinks.

NEWS#658{Give it another title}";

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

The line right before the table will be used as the caption for the table.

| Node     | Cores | Memory |
|----------|------:|-------:|
| Carter-A |    16 |   32GB |
| Carter-B |    16 |   64GB |
";
?>
	<div id="markdown" class="dialog dialog-help tabs" title="MarkDown Help">
		<ul>
			<li><a href="#help1a">Fonts</a></li>
			<li><a href="#help1b">Lists</a></li>
			<li><a href="#help1c">Links</a></li>
			<li><a href="#help1d">Other News</a></li>
			<li><a href="#help1e">Line Breaks</a></li>
			<li><a href="#help1f">Code</a></li>
			<li><a href="#help1g">Tables</a></li>
		</ul>
		<div id="help1a">
			<?php
			$article = new App\Modules\News\Models\Article(['body' => $help1a]);
			?>
			<div class="form-group">
				<label for="help1ainput">Input text:</label>
				<textarea id="help1ainput" class="form-control samplebox" rows="5" data-sample="a"><?php echo $help1a; ?></textarea>
			</div>
			<div>Output text:</div>
			<div id="help1aoutput" class="sampleoutput"><?php echo $article->formattedbody; ?></div>
		</div>
		<div id="help1b">
			<?php
			$article = new App\Modules\News\Models\Article(['body' => $help1b]);
			?>
			<div class="form-group">
				<label for="help1binput">Input text:</label>
				<textarea id="help1binput" class="form-control samplebox" rows="5" data-sample="b"><?php echo $help1b; ?></textarea>
			</div>
			<div>Output text:</div>
			<div id="help1boutput" class="sampleoutput"><?php echo $article->formattedBody; ?></div>
		</div>
		<div id="help1c">
			<?php
			$article = new App\Modules\News\Models\Article(['body' => $help1c]);
			?>
			<div class="form-group">
				<label for="help1cinput">Input text:</label>
				<textarea id="help1cinput" class="form-control samplebox" rows="5" data-sample="c"><?php echo $help1c; ?></textarea>
			</div>
			<div>Output text:</div>
			<div id="help1coutput" class="sampleoutput"><?php echo $article->formattedBody; ?></div>
		</div>
		<div id="help1d">
			<?php
			$article = new App\Modules\News\Models\Article(['body' => $help1d]);
			?>
			<div class="form-group">
				<label for="help1dinput">Input text:</label>
				<textarea id="help1dinput" class="form-control samplebox" rows="5" data-sample="d"><?php echo $help1d; ?></textarea>
			</div>
			<div>Output text:</div>
			<div id="help1doutput" class="sampleoutput"><?php echo $article->formattedBody; ?></div>
		</div>
		<div id="help1e">
			<?php
			$article = new App\Modules\News\Models\Article(['body' => $help1e]);
			?>
			<div class="form-group">
				<label for="help1einput">Input text:</label>
				<textarea id="help1einput" class="form-control samplebox" rows="5" data-sample="e"><?php echo $help1e; ?></textarea>
			</div>
			<div>Output text:</div>
			<div id="help1eoutput" class="sampleoutput"><?php echo $article->formattedBody; ?></div>
		</div>
		<div id="help1f">
			<?php
			$article = new App\Modules\News\Models\Article(['body' => $help1f]);
			?>
			<div class="form-group">
				<label for="help1finput">Input text:</label>
				<textarea id="help1finput" class="form-control samplebox" rows="5" data-sample="f"><?php echo $help1f; ?></textarea>
			</div>
			<div>Output text:</div>
			<div id="help1foutput" class="sampleoutput"><?php echo $article->formattedBody; ?></div>
		</div>
		<div id="help1g">
			<?php
			$article = new App\Modules\News\Models\Article(['body' => $help1g]);
			?>
			<div class="form-group">
				<label for="help1ginput">Input text:</label>
				<textarea id="help1ginput" class="form-control samplebox" rows="5" data-sample="g"><?php echo $help1g; ?></textarea>
			</div>
			<div>Output text:</div>
			<div id="help1goutput" class="sampleoutput"><?php echo $article->formattedBody; ?></div>
		</div>
	</div>

	<div id="preview" class="dialog" title="News Preview">
	</div>

	<div id="mailpreview" class="dialog" title="Mail Preview">
	</div>

	<div id="dialog-confirm" class="dialog" title="Unsaved Changes">
		<p>You have unsaved changes that need to be saved before mailing news item.</p>
		<p>Would you like to save the changes?</p>
	</div>

	@csrf
</form>
@stop