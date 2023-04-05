@extends('layouts.master')

@section('title') Manage News &amp; Events @stop

@push('styles')
<link rel="stylesheet" type="text/css" media="all" href="{{ asset('modules/core/vendor/tagsinput/jquery.tagsinput.css?v=' . filemtime(public_path() . '/modules/core/vendor/tagsinput/jquery.tagsinput.css')) }}" />
<link rel="stylesheet" type="text/css" media="all" href="{{ asset('modules/core/vendor/jquery-timepicker/jquery.timepicker.css?v=' . filemtime(public_path() . '/modules/core/vendor/jquery-timepicker/jquery.timepicker.css')) }}" />
<link rel="stylesheet" type="text/css" media="all" href="{{ asset('modules/core/vendor/select2/css/select2.css?v=' . filemtime(public_path() . '/modules/core/vendor/select2/css/select2.css')) }}" />
<link rel="stylesheet" type="text/css" media="all" href="{{ asset('modules/news/css/news.css?v=' . filemtime(public_path() . '/modules/news/css/news.css')) }}" />
@endpush

@push('scripts')
<script src="{{ asset('modules/core/vendor/tagsinput/jquery.tagsinput.js?v=' . filemtime(public_path() . '/modules/core/vendor/tagsinput/jquery.tagsinput.js')) }}"></script>
<script src="{{ asset('modules/core/vendor/jquery-timepicker/jquery.timepicker.js?v=' . filemtime(public_path() . '/modules/core/vendor/jquery-timepicker/jquery.timepicker.js')) }}"></script>
<script src="{{ asset('modules/core/vendor/select2/js/select2.min.js?v=' . filemtime(public_path() . '/modules/core/vendor/select2/js/select2.min.js')) }}"></script>
<script src="{{ asset('modules/news/js/site.js?v=' . filemtime(public_path() . '/modules/news/js/site.js')) }}"></script>
@endpush

@php
app('pathway')
	->append(
		config('module.news.module name', trans('news::news.news')),
		route('site.news.index')
	)
	->append(
		trans('news::news.manage news'),
		route('site.news.manage')
	);
@endphp

@section('content')
<div class="row">
<div class="sidenav col-lg-3 col-md-3 col-sm-12 col-xs-12">
	@include('news::site.menu', ['types' => $types, 'active' => 'manage'])
</div>

<div class="contentInner col-lg-9 col-md-9 col-sm-12 col-xs-12">
	<h2>Manage News &amp; Events</h2>

	<div id="everything">
		<ul class="nav nav-tabs" id="tabs">
			<li class="nav-item"><a id="TAB_search" class="nav-link active tab activeTab" href="#search">{{ trans('news::news.search') }}</a></li>
			<li class="nav-item"><a id="TAB_add" class="nav-link tab" href="#add">Add New</a></li>
		</ul>
		<div class="tabMain" id="tabMain">
			<div id="DIV_news">

				<form method="get" action="{{ route('site.news.manage') }}" class="mb-3 editform">
					<div class="card card-news">
						<div class="card-body">
						<!-- <legend><span id="SPAN_header" data-search="Search News" data-add="Add New News" data-edit="Edit News">Search News</span></legend> -->

						<div class="form-group row tab-search tab-add tab-edit" id="TR_date">
							<label for="datestartshort" class="col-sm-2 col-form-label">{{ trans('news::news.publish up') }}</label>
							<div class="col-sm-4">
								<?php
								$startdate = '';
								$starttime = '';
								if ($value = $filters['start'])
								{
									$value = explode('!', $value);
									$startdate = $value[0];
									if (isset($value[1]))
									{
										$starttime = $value[1];
										// Convert to human readable form
										$values = explode(':', $starttime);
										if ($values[0] > 12)
										{
											$values[0] -= 12;
											$starttime = $values[0] . ':' . $values[1] . ' PM';
										}
										else if ($values[0] == 12)
										{
											$starttime = $values[0] . ':' . $values[1] . ' PM';
										}
										else if ($values[0] == 0)
										{
											$values[0] += 12;
											$starttime = $values[0] . ':' . $values[1] . ' AM';
										}
										else
										{
											$starttime = $values[0] . ':' . $values[1] . ' AM';
										}
										$starttime = preg_replace('/^0/', '', $starttime);
									}
								}

								$stopdate = '';
								$stoptime = '';

								$value = $filters['stop'];
								if ($value && $value != '0000-00-00 00:00:00')
								{
									$value = explode('!', $value);
									$stopdate = $value[0];
									if (isset($value[1]) && $value[1] != '00:00:00')
									{
										$stoptime = $value[1];
										// Convert to human readable form
										$values = explode(':', $stoptime);
										if ($values[0] > 12)
										{
											$values[0] -= 12;
											$stoptime = $values[0] . ':' . $values[1] . ' PM';
										}
										else if ($values[0] == 12)
										{
											$stoptime = $values[0] . ':' . $values[1] . ' PM';
										}
										else if ($values[0] == 0)
										{
											$values[0] += 12;
											$stoptime = $values[0] . ':' . $values[1] . ' AM';
										}
										else
										{
											$stoptime = $values[0] . ':' . $values[1] . ' AM';
										}
										$stoptime = preg_replace('/^0/', '', $stoptime);
									}
								}

								if ($starttime == '12:00 AM' && $stoptime == '12:00 AM')
								{
									$starttime = $stoptime;
								}
								?>
								<div class="input-group">
									<span class="input-group-prepend"><span class="input-group-text fa fa-calendar" aria-hidden="true"></span></span>
									<input id="datestartshort" type="text" class="date-pick form-control" name="start" placeholder="YYYY-MM-DD" data-start="{{ $startdate }}" value="{{ $startdate }}" />
								</div>
								<div class="input-group input-time tab-add tab-edit">
									<label for="timestartshort" class="sr-only">{{ trans('news::news.time from') }}</label>
									<span class="input-group-prepend"><span class="input-group-text fa fa-clock-o" aria-hidden="true"></span></span>
									<input id="timestartshort" type="text" class="time-pick form-control" name="starttime" placeholder="h:mm AM/PM" value="{{ $starttime }}" />
								</div>
							</div>

							<label for="datestopshort" class="col-sm-2 col-form-label align-right">{{ trans('news::news.publish down') }}</label>
							<div class="col-sm-4">
								<div class="input-group" id="enddate">
									<span class="input-group-prepend"><span class="input-group-text fa fa-calendar" aria-hidden="true"></span></span>
									<input id="datestopshort" type="text" class="date-pick form-control" name="stop" placeholder="YYYY-MM-DD" data-stop="{{ $stopdate }}" value="{{ $stopdate }}">
								</div>
								<div class="input-group input-time tab-add tab-edit">
									<label for="timestopshort" class="sr-only">{{ trans('news::news.time to') }}</label>
									<span class="input-group-prepend"><span class="input-group-text fa fa-clock-o" aria-hidden="true"></span></span>
									<input id="timestopshort" type="text" class="time-pick form-control" name="stoptime" placeholder="h:mm AM/PM" value="{{ $stoptime }}" />
								</div>
							</div>
						</div>

						<div class="form-group row tab-search tab-add tab-edit" id="TR_newstype">
							<label for="newstype" class="col-sm-2 col-form-label">{{ trans('news::news.type') }}</label>
							<div class="col-sm-10">
								<select id="newstype" name="newstype" class="custom-select">
									<option id="OPTION_all" value="-1">All</option>
									@foreach (App\Modules\News\Models\Type::tree() as $type)
										<option value="{{ $type->id }}"<?php if ($filters['newstype'] == $type->id) { echo ' selected="selected"'; } ?> data-tagresources="{{ $type->tagresources }}" data-tagusers="{{ $type->tagusers }}" data-taglocation="{{ $type->location }}" data-tagurl="{{ $type->url }}">{{ ($type->level ? str_repeat('|_', $type->level) . ' ' : '') . $type->name }}</option>
									@endforeach
								</select>
							</div>
						</div>
						<div class="form-group row tab-search" id="TR_keywords">
							<label for="keywords" class="col-sm-2 col-form-label">{{ trans('news::news.keywords') }}</label>
							<div class="col-sm-10">
								<input type="text" name="keyword" id="keywords" size="45" class="form-control" value="{{ request('keywords') }}" />
							</div>
						</div>
						<div class="form-group row tab-search tab-add tab-edit" id="TR_resource">
							<label for="newsresource" class="col-sm-2 col-form-label">{{ trans('news::news.resources') }}</label>
							<div class="col-sm-10">
								<?php
								$selected = array();
								if ($res = $filters['resource'])
								{
									$selected = explode(',', $res);
									$selected = array_map('trim', $selected);
								}
								?>
								<?php /*<input name="resource" id="newsresource" size="45" class="form-control" value="{{ implode(',', $resources) }}" data-uri="{{ route('api.resources.index') }}?search=%s" />*/ ?>
								<select class="form-control searchable-select-multi" multiple="multiple" name="resource[]" id="newsresource">
									<?php
									$resources = App\Modules\Resources\Models\Asset::query()
										->where('listname', '!=', '')
										->where('display', '>', 0)
										->orderBy('name')
										->get();

									$types = array();
									foreach ($resources as $resource)
									{
										if (!isset($types[$resource->resourcetype]))
										{
											$types[$resource->resourcetype] = array();
										}
										$types[$resource->resourcetype][] = $resource;
									}
									ksort($types);

									foreach ($types as $t => $res)
									{
										$type = App\Modules\Resources\Models\Type::find($t);
										if (!$type)
										{
											$type = new App\Modules\Resources\Models\Type;
											$type->name = 'Services';
										}
										?>
										<optgroup label="{{ $type->name }}" class="select2-result-selectable">
											<?php
											foreach ($res as $resource)
											{
												?>
												<option value="{{ $resource->id }}"<?php if (in_array($resource->id, $selected)) { echo ' selected="selected"'; } ?>>{{ $resource->name }}</option>
												<?php
											}
											?>
										</optgroup>
										<?php
									}
									?>
								</select>
							</div>
						</div>
						<div class="form-group row tab-add tab-edit" id="TR_user">
							<label for="newsuser" class="col-sm-2 col-form-label">User</label>
							<div class="col-sm-10">
								<?php
								$usrs = array();
								if ($users = request('user'))
								{
									foreach (explode(',', $users) as $u)
									{
										if (trim($u))
										{
											$usr = App\Modules\Users\Models\User::find($u);
											$usrs[] = $usr->name . ':' . $u . '';
										}
									}
								}
								?>
								<input name="user" id="newsuser" size="45" class="form-control" value="{{ implode(',', $usrs) }}" data-uri="{{ route('api.users.index') }}?search=%s" />
							</div>
						</div>
						<div class="form-group row tab-search tab-add tab-edit" id="TR_published">
							<label for="published" class="col-sm-2 col-form-label">
								{{ trans('news::news.published') }}
								<a href="#help2" data-toggle="modal" class="text-info tip" title="Help">
									<span class="fa fa-question-circle" aria-hidden="true"></span>
									<span class="sr-only">Help</span>
								</a>
							</label>
							<div class="col-sm-10">
								<input type="checkbox" id="published" name="published" value="1" checked="checked" />
							</div>
						</div>
						<div class="form-group row tab-search" id="TR_template">
							<label for="template" class="col-sm-2 col-form-label">{{ trans('news::news.template') }}</label>
							<div class="col-sm-10">
								<input type="checkbox" id="template" />
							</div>
						</div>
						<div class="form-group row tab-add tab-edit" id="TR_use_template">
							<label for="template_select" class="col-sm-2 col-form-label">
								{{ trans('news::news.template') }}
								<a href="#help4" data-toggle="modal" class="text-info tip" title="Help">
									<span class="fa fa-question-circle" aria-hidden="true"></span>
									<span class="sr-only">Help</span>
								</a>
							</label>
							<div class="col-sm-10">
								<select id="template_select" name="template_select" class="form-control">
									<option value="0">(No Template)</option>
									<option value="savetemplate">(Save as New Template)</option>
									@foreach ($templates as $template)
										<option value="{{ route('api.news.read', ['id' => $template['id']]) }}" data-api="{{ route('api.news.read', ['id' => $template['id']]) }}">{{ $template['headline'] }}</option>
									@endforeach
								</select>
							</div>
						</div>
						<div class="form-group row tab-add tab-edit" id="TR_headline">
							<label for="Headline" class="col-sm-2 col-form-label">{{ trans('news::news.headline') }}</label>
							<div class="col-sm-10">
								<input id="Headline" name="headline" type="text" size="78" maxlength="255" class="form-control" />
							</div>
						</div>
						<div class="form-group row tab-search tab-add tab-edit" id="TR_location">
							<label for="location" class="col-sm-2 col-form-label">{{ trans('news::news.location') }}</label>
							<div class="col-sm-10">
								<input name="location" id="location" type="text" size="45" maxlength="32" class="form-control" />
							</div>
						</div>
						<div class="form-group row tab-add tab-edit" id="TR_url">
							<label for="url" class="col-sm-2 col-form-label">{{ trans('news::news.url') }}</label>
							<div class="col-sm-10">
								<input name="url" id="url" type="text" size="45" class="form-control" />
							</div>
						</div>
						<div class="form-group row tab-add tab-edit" id="TR_notes">
							<label for="NotesText" class="col-sm-2 col-form-label">
								{{ trans('news::news.body') }}
								<a href="#markdown-help" data-toggle="modal" class="text-info tip" title="Help">
									<span class="fa fa-question-circle" aria-hidden="true"></span>
									<span class="sr-only">Help</span>
								</a>
							</label>
							<div class="col-sm-10">
								{!! markdown_editor('NotesText', '', ['rows' => 15, 'cols' =>77, 'class' => 'required', 'required' => 'required']) !!}
							</div>
						</div>
						<div class="form-group row tab-search" id="TR_id">
							<label for="id" class="col-sm-2 col-form-label">{{ trans('news::news.id') }} #</label>
							<div class="col-sm-10">
								<input name="id" type="text" id="id" size="45" class="form-control" value="{{ request('id') }}" />
							</div>
						</div>
						<div class="form-group row tab-search" id="TR_search">
							<div class="col-sm-2">
							</div>
							<div class="col-sm-10">
								<input type="submit" class="btn btn-primary" value="Search" id="INPUT_search" />
								<input type="reset" class="btn btn-link" value="Clear" id="INPUT_clearsearch" />
							</div>
						</div>
						<div class="form-group row tab-add tab-edit" id="TR_create">
							<div class="col-sm-2">
							</div>
							<div class="col-sm-10">
								<input id="INPUT_add" type="submit" class="btn btn-primary" data-add="Add News" data-edit="Save Changes" value="Add News" disabled="true" />
								<input id="INPUT_preview" type="button" data-toggle="modal" data-target="#preview-modal" class="btn btn-secondary" value="Preview" data-id="{{ request('id') }}" />
								<input id="INPUT_clear" type="reset" class="btn btn-danger" data-add="Add News" data-edit="Save Changes" value="Clear" />
							</div>
						</div>

						<div id="news_action" class="alert alert-danger d-none"></div>
					</div>
				</div>

					@csrf
				</form>

				<?php
				$string = '';
				$params = request()->input();
				if (count($params) > 0)
				{
					$valid_args = array('start', 'stop', 'newstype', 'id', 'resource');

					foreach ($params as $key => $value)
					{
						if ($key != 'keywords' && $value != '')
						{
							if (in_array($key, $valid_args))
							{
								$string .= $key . ':' . $value . ' ';
							}
						}
						else
						{
							// Try to sanitize a little. We target quotes to prevent XSS attmepts
							$string .= str_replace('"', '', $value);
						}
					}
				}

				if ($string == '')
				{
					$string = 'start:0000-00-00';
				}
				?>
				<p><strong id="matchingnews">{{ trans('news::news.search results') }}:</strong></p>
				<div id="news" data-query="<?php echo $string; ?>" data-api="{{ route('api.news.index') }}">
					{{ trans('global.loading') }}
				</div>
			</div>
		</div>
	</div>

	@include('news::formatting')

	<div class="modal" id="help2" tabindex="-1" aria-labelledby="help2-title" aria-hidden="true">
		<div class="modal-dialog modal-dialog-centered">
			<div class="modal-content shadow-sm">
				<div class="modal-header">
					<div class="modal-title" id="help2-title">Published</div>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					<p>Check this box if you wish to publish this new article to the website for the public to see. Leaving this box unchecked will create the article in draft mode where only other news editors can read it.</p>
				</div>
			</div>
		</div>
	</div>

	<div class="modal" id="help3" tabindex="-1" aria-labelledby="help3-title" aria-hidden="true">
		<div class="modal-dialog modal-dialog-centered">
			<div class="modal-content shadow-sm">
				<div class="modal-header">
					<div class="modal-title" id="help3-title">Update</div>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					<p>Check this box if you wish to publically flag this news article as being updated. Articles will be flagged as being updated with the current timestamp.</p>
					<p>Typically this box is used when adding new information to an article. Minor corrections to articles, such as fixing typos, do not need to be publicized as being updated.</p>
				</div>
			</div>
		</div>
	</div>

	<div class="modal" id="help4" tabindex="-1" aria-labelledby="help4-title" aria-hidden="true">
		<div class="modal-dialog modal-dialog-centered">
			<div class="modal-content shadow-sm">
				<div class="modal-header">
					<div class="modal-title" id="help4-title">Template</div>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					<p>From this drop down, you may choose to create a new template, populate article from a template, or leave the selection alone and create an article from scratch.</p>
				</div>
			</div>
		</div>
	</div>

	<div class="modal" id="preview-modal" tabindex="-1" aria-labelledby="preview-title" aria-hidden="true">
		<div class="modal-dialog modal-lg modal-dialog-centered">
			<div class="modal-content shadow-sm">
				<div class="modal-header">
					<div class="modal-title" id="preview-title">News Preview</div>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body" id="preview">
					<div class="spinner-border" role="status">
						<span class="sr-only">Loading...</span>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="modal" id="mailpreview-modal" tabindex="-1" aria-labelledby="mailpreview-title" aria-hidden="true">
		<div class="modal-dialog modal-lg modal-dialog-centered">
			<div class="modal-content shadow-sm">
				<div class="modal-header">
					<div class="modal-title" id="mailpreview-title">Mail Preview</div>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body" id="mailpreview">
					<div class="spinner-border" role="status">
						<span class="sr-only">Loading...</span>
					</div>
				</div>
				<div class="modal-footer text-right">
					<button id="mailsend" data-dismiss="modal" class="btn btn-success" data-confirm="You have unsaved changes that need to be saved before mailing news item. Would you like to save the changes?">Send mail</button>
				</div>
			</div>
		</div>
	</div>

	<div class="modal" id="mailwrite-modal" tabindex="-1" aria-labelledby="mailwrite-title" aria-hidden="true">
		<div class="modal-dialog modal-lg modal-dialog-centered">
			<div class="modal-content dialog-content shadow-sm">
				<div class="modal-header">
					<div class="modal-title" id="mailwrite-title">Write Mail</div>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body dialog-body" id="mailwrite">
					<form method="post" action="{{ route('site.news.manage') }}">
						<div class="form-group row">
							<label for="newsuser" class="col-sm-2 col-form-label">To</label>
							<div class="col-sm-10">
								<input name="to" id="mail-to" class="form-control" value="" data-uri="{{ route('api.users.index') }}?search=%s" />
							</div>
						</div>
						<div class="form-group row">
							<label for="mail-from" class="col-sm-2 col-form-label">From</label>
							<div class="col-sm-10">
								<input id="mail-from" name="from" type="text" disabled="disabled" readonly="readonly" class="form-control" value="{{ auth()->user()->name }}" />
							</div>
						</div>
						<div class="form-group row">
							<label for="mail-subject" class="col-sm-2 col-form-label">Subject</label>
							<div class="col-sm-10">
								<input id="mail-subject" name="subject" type="text" class="form-control" />
							</div>
						</div>
						<div class="form-group row">
							<label for="NotesText" class="col-sm-2 col-form-label">
								Body
								<a href="#help1" data-toggle="modal" class="text-info tip" title="Help">
									<span class="fa fa-question-circle" aria-hidden="true"></span><span class="sr-only">Help</span>
								</a>
							</label>
							<div class="col-sm-10">
								<textarea name="body" id="mail-body" rows="15" cols="77" class="form-control"></textarea>
							</div>
						</div>
						@csrf
					</form>
				</div>
				<div class="modal-footer text-right">
					<button id="mailsend-write" data-dismiss="modal" class="btn btn-success">Send mail</button>
				</div>
			</div>
		</div>
	</div>

	<?php
	if ($id = request('id'))
	{
		$news = App\Modules\News\Models\Article::findOrFail($id);

		if ($news && auth()->user()->can('edit news'))
		{
			$value = explode(' ', $news->datetimenews->toDateTimeString());
			$startdate = $value[0];
			$starttime = $value[1];
			// Convert to human readable form
			$values = explode(':', $starttime);
			if ($values[0] > 12)
			{
				$values[0] -= 12;
				$starttime = $values[0] . ':' . $values[1] . ' PM';
			}
			else if ($values[0] == 12)
			{
				$starttime = $values[0] . ':' . $values[1] . ' PM';
			}
			else if ($values[0] == 0)
			{
				$values[0] += 12;
				$starttime = $values[0] . ':' . $values[1] . ' AM';
			}
			else
			{
				$starttime = $values[0] . ':' . $values[1] . ' AM';
			}
			$starttime = preg_replace('/^0/', '', $starttime);

			if ($news->hasEnd())
			{
				$value = explode(' ', $news->datetimenewsend->toDateTimeString());
			}
			else
			{
				$value = explode(' ', '0000-00-00 00:00:00');
			}
			$stopdate = $value[0];
			$stoptime = $value[1];
			// Convert to human readable form
			$values = explode(':', $stoptime);
			if ($values[0] > 12)
			{
				$values[0] -= 12;
				$stoptime = $values[0] . ':' . $values[1] . ' PM';
			}
			else if ($values[0] == 12)
			{
				$stoptime = $values[0] . ':' . $values[1] . ' PM';
			}
			else if ($values[0] == 0)
			{
				$values[0] += 12;
				$stoptime = $values[0] . ':' . $values[1] . ' AM';
			}
			else
			{
				$stoptime = $values[0] . ':' . $values[1] . ' AM';
			}
			$stoptime = preg_replace('/^0/', '', $stoptime);

			// Only display 12:00AM in certain circumstances
			if ($starttime == '12:00 AM' && $stoptime == '12:00 AM')
			{
				$starttime = $stoptime = '';
			}
			if ($stopdate == '0000-00-00')
			{
				$stoptime = '';
			}
			if ($stopdate == '0000-00-00' && $starttime == '12:00 AM')
			{
				$starttime = '';
			}

			$data = (object)$news->toArray();
			$data->api = route('api.news.update', ['id' => $news->id]);
			$data->news = $news->body;
			$data->startdate = $startdate;
			$data->stopdate = ($stopdate == '0000-00-00' ? '' : $stopdate);
			$data->starttime = $starttime;
			$data->stoptime = $stoptime;
			$data->resources = array();
			$data->vars = $news->getContentVars();
			foreach ($news->resources as $r)
			{
				//$r->resourcename = $r->resource->name;
				$data->resources[] = array(
					'id' => $r->id,
					'resourceid' => $r->resourceid,
					'newsid' => $r->newsid,
					'resourcename' => $r->resource->name
				);
			}
			$data->associations = array();
			foreach ($news->associations as $association)
			{
				$association->assocname = $association->associated ? $association->associated->name . ' (' . $association->associated->username . ')' : trans('global.unknown');
				$data->associations[] = $association;
			}
			$data->lastedit = $news->datetimeedit ? $news->datetimeedit->toDateTimeString() : '0000-00-00 00:00:00';
			?>
			<script type="application/json" id="news-data">
				<?php echo json_encode($data); ?>
			</script>
			<?php
		}
	}
	?>
</div>
</div>
@stop