@extends('layouts.master')

@push('styles')
<link rel="stylesheet" type="text/css" media="all" href="{{ asset('modules/core/vendor/tagsinput/jquery.tagsinput.css?v=' . filemtime(public_path() . '/modules/core/vendor/tagsinput/jquery.tagsinput.css')) }}" />
<link rel="stylesheet" type="text/css" media="all" href="{{ asset('modules/core/vendor/select2/css/select2.css?v=' . filemtime(public_path() . '/modules/core/vendor/select2/css/select2.css')) }}" />
@endpush

@push('scripts')
<script src="{{ asset('modules/core/vendor/tagsinput/jquery.tagsinput.js?v=' . filemtime(public_path() . '/modules/core/vendor/tagsinput/jquery.tagsinput.js')) }}"></script>
<script src="{{ asset('modules/core/js/date.js?v=' . filemtime(public_path() . '/modules/core/js/date.js')) }}"></script>
<script src="{{ asset('modules/core/vendor/select2/js/select2.min.js?v=' . filemtime(public_path() . '/modules/core/vendor/select2/js/select2.min.js')) }}"></script>
<script src="{{ asset('modules/contactreports/js/site.js?v=' . filemtime(public_path() . '/modules/contactreports/js/site.js')) }}"></script>
@endpush

@php
app('pathway')->append(
	trans('contactreports::contactreports.contact reports'),
	route('site.contactreports.index')
);
@endphp

@section('title'){{ trans('contactreports::contactreports.contact reports') }}@stop

@section('content')
<div class="row">
<div class="sidenav col-lg-3 col-md-3 col-sm-12 col-xs-12">
	<h2>Quick Filters</h2>
	<ul class="nav flex-column">
		<li class="nav-item">
			<a class="nav-link" href="{{ route('site.contactreports.index') }}?people={{ auth()->user()->id }}">
				{{ trans('contactreports::contactreports.my reports') }}
			</a>
		</li>
		<li class="nav-item">
			<?php
			$start = Carbon\Carbon::now()->modify('-1 week')->format('Y-m-d');
			?>
			<a class="nav-link" href="{{ route('site.contactreports.index') }}?start={{ $start }}">
				{{ trans('contactreports::contactreports.past week') }}
			</a>
		</li>
		<li class="nav-item">
			<?php
			$start = Carbon\Carbon::now()->modify('-1 month')->format('Y-m-d');
			?>
			<a class="nav-link" href="{{ route('site.contactreports.index') }}?start={{ $start }}">
				{{ trans('contactreports::contactreports.past month') }}
			</a>
		</li>
	</ul>
</div>

<div class="contentInner col-lg-9 col-md-9 col-sm-12 col-xs-12">
	<h2>{{ trans('contactreports::contactreports.contact reports') }}</h2>

	<?php /*<div id="contactreports">
		<reports></reports>
	</div>
	<script type="text/javascript" src="{{ asset('/modules/contactreports/js/app.js') }}"></script>*/ ?>

	<div id="everything">
		<ul class="nav nav-tabs crm-tabs">
			<li class="nav-item">
				<a id="TAB_search" class="nav-link active tab activeTab" href="#search">{{ trans('contactreports::contactreports.search') }}</a>
			</li>
			<li class="nav-item">
				<a id="TAB_add" class="nav-link tab"
					data-txt-search="{{ trans('contactreports::contactreports.add new') }}"
					data-txt-add="{{ trans('contactreports::contactreports.add new') }}"
					data-txt-edit="Edit Report"
					data-txt-follow="{{ trans('contactreports::contactreports.add new') }}"
					href="#add">{{ trans('contactreports::contactreports.add new') }}</a>
			</li>
			<li class="nav-item">
				<a id="TAB_follow" class="nav-link tab" href="#follow">{{ trans('contactreports::contactreports.follow') }}</a>
			</li>
		</ul>
		<div class="tabMain" id="tabMain">
			<div id="DIV_crm">

				<form method="get" action="{{ route('site.contactreports.index') }}" class="editform">
					<fieldset>
						<legend><span id="SPAN_header" data-txt-search="Search Reports" data-txt-add="Add New Report" data-txt-edit="Edit Report" data-txt-follow="Follow New Reports">Search Reports</span></legend>

						<div class="form-group row tab-search tab-add tab-edit" id="TR_date">
							<label for="datestartshort" class="col-sm-2 col-form-label">{{ trans('contactreports::contactreports.date from') }}</label>
							<div class="col-sm-4">
								<div class="input-group">
									<span class="input-group-addon"><span class="input-group-text fa fa-calendar" aria-hidden="true"></span></span>
									<input id="datestartshort" type="text" class="date-pick form-control" name="start" placeholder="YYYY-MM-DD" value="{{ $filters['start'] }}" />
								</div>
							</div>

							<label for="datestopshort" class="col-sm-2 col-form-label align-right tab-search">{{ trans('contactreports::contactreports.date to') }}</label>
							<div class="col-sm-4 tab-search">
								<div class="input-group" id="enddate">
									<span class="input-group-addon"><span class="input-group-text fa fa-calendar" aria-hidden="true"></span></span>
									<input id="datestopshort" type="text" class="date-pick form-control" name="stop" placeholder="YYYY-MM-DD" value="{{ $filters['stop'] }}">
								</div>
							</div>
						</div>

						<div class="form-group row tab-search" id="TR_keywords">
							<label for="keywords" class="col-sm-2 col-form-label">{{ trans('contactreports::contactreports.keywords') }}</label>
							<div class="col-sm-10">
								<input type="text" name="keyword" id="keywords" size="45" class="form-control" value="{{ $filters['search'] }}" />
							</div>
						</div>

						<div class="form-group row tab-search tab-add tab-edit tab-follow" id="TR_group">
							<label for="group" class="col-sm-2 col-form-label">{{ trans('contactreports::contactreports.group') }}</label>
							<div class="col-sm-10">
								<?php
								$grps = array();
								if ($groups = $filters['group'])
								{
									foreach (explode(',', $groups) as $g)
									{
										if (trim($g))
										{
											/*if (!strstr($g, '/'))
											{
												$g = ROOT_URI . 'group/' . $g;
											}*/
											$grp = App\Modules\Groups\Models\Group::find($g);
											$grps[] = $grp->name . ':' . $g . '';
										}
									}
								}
								?>
								<input name="group" id="group" size="45" class="form-control" value="{{ implode(',', $grps) }}" data-uri="{{ route('api.groups.index') }}?search=%s" data-api="{{ route('api.groups.index') }}" />
							</div>
						</div>

						<div class="form-group row tab-search tab-add tab-edit tab-follow" id="TR_people">
							<label for="people" class="col-sm-2 col-form-label">{{ trans('contactreports::contactreports.users') }}</label>
							<div class="col-sm-10">
								<?php
								$usrs = array();
								if ($users = $filters['people'])
								{
									foreach (explode(',', $users) as $u)
									{
										if (trim($u))
										{
											if (!is_numeric($u))
											{
												$usr = App\Modules\Users\Models\User::findByUsername($u);
											}
											else
											{
												$usr = App\Modules\Users\Models\User::find($u);
											}
											$usrs[] = $usr->name . ':' . $u;
										}
									}
								}
								?>
								<input name="people" id="people" size="45" class="form-control" value="{{ implode(',', $usrs) }}" data-uri="{{ route('api.users.index') }}?search=%s" data-api="{{ route('api.users.index') }}" />
							</div>
						</div>

						<div class="form-group row tab-search" id="TR_tags">
							<label for="people" class="col-sm-2 col-form-label">{{ trans('contactreports::contactreports.tags') }}</label>
							<div class="col-sm-10">
								<?php
								$tags = array();
								if ($tg = $filters['tag'])
								{
									foreach (explode(',', $tg) as $t)
									{
										if (trim($t))
										{
											$tag = App\Modules\Tags\Models\Tag::query()->where('slug', '=', $t)->first();
											$tags[] = $tag->name . ':' . $t;
										}
									}
								}
								?>
								<input name="tag" id="tag" size="45" class="form-control" value="{{ implode(', ', $tags) }}" data-uri="{{ route('api.tags.index') }}?search=%s" data-api="{{ route('api.tags.index') }}" />
							</div>
						</div>

						<div class="form-group row tab-search tab-add tab-edit" id="TR_type">
							<label for="crmtype" class="col-sm-2 col-form-label">Category</label>
							<div class="col-sm-10">
								<select id="crmtype" name="crmtype" class="form-control">
									<option id="OPTION_all" value="-1">All</option>
									<option id="OPTION_uncategorized" value="0">Uncategorized</option>
									<?php
									foreach ($types as $type):
										$selected = '';
										if ($type->id == $filters['type']):
											$selected = ' selected="selected"';
										endif;
										?>
										<option value="{{ $type->id }}"<?php echo $selected; ?>>{{ $type->name }}</option>
										<?php
									endforeach;
									?>
								</select>
							</div>
						</div>

						<div class="form-group row tab-search tab-add tab-edit" id="TR_resource">
							<label for="newsresource" class="col-sm-2 col-form-label">{{ trans('contactreports::contactreports.resources') }}</label>
							<div class="col-sm-10">
								<?php
								/*$resources = array();
								if ($rs = $filters['resource'])
								{
									foreach (explode(',', $rs) as $r)
									{
										if (trim($r))
										{
											$resource = App\Modules\Resources\Models\Asset::find($r);
											$resources[] = $resource->name . ':' . $r;
										}
									}
								}
								?>
								<input name="resource" id="crmresource" size="45" class="form-control" value="{{ implode(',', $resources) }}" data-uri="{{ route('api.resources.index') }}?search=%s" data-api="{{ route('api.resources.index') }}" />
								<div class="col-sm-10">
								<?php*/
								$selected = array();
								if ($res = $filters['resource'])
								{
									$selected = is_string($res) ? explode(',', $res) : $res;
									$selected = array_map('trim', $selected);
								}
								?>
								<select class="form-control searchable-select-multi" multiple="multiple" name="resource[]" id="crmresource" data-api="{{ route('api.resources.index') }}">
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

						<div class="form-group row tab-search" id="TR_id">
							<label for="id" class="col-sm-2 col-form-label">CR#</label>
							<div class="col-sm-10">
								<input name="id" type="text" id="id" size="45" class="form-control" value="{{ $filters['id'] }}" />
							</div>
						</div>

						<div class="form-group row tab-add tab-edit" id="TR_notes">
							<label for="NotesText" class="col-sm-2 col-form-label">
								{{ trans('contactreports::contactreports.notes') }}
							</label>
							<div class="col-sm-10">
								{!! markdown_editor('NotesText', '', ['rows' => 10, 'cols' => 80, 'class' => 'required', 'required' => 'required']) !!}
								<span class="form-text text-muted">Reports can be formatted with <a href="#help1" class="help tip" title="MarkDown Formatting Help">MarkDown</a>. Hash tags (e.g., #python) can be used to tag entries.</span>
							</div>
						</div>

						<div class="form-group row tab-search" id="TR_search">
							<div class="col-sm-2">
							</div>
							<div class="col-sm-10">
								<input type="submit" class="btn btn-primary" value="{{ trans('contactreports::contactreports.search') }}" id="INPUT_search" />
								<input type="reset" class="btn btn-link btn-clear" value="{{ trans('contactreports::contactreports.clear') }}" id="INPUT_clearsearch" />
							</div>
						</div>

						<div class="form-group row tab-add tab-edit" id="TR_create">
							<div class="col-sm-2">
							</div>
							<div class="col-sm-10">
								<input id="INPUT_add" 
									type="submit"
									class="btn btn-primary"
									data-txt-search="{{ trans('contactreports::contactreports.search') }}"
									data-txt-add="{{ trans('contactreports::contactreports.add report') }}"
									data-txt-edit="Save changes"
									data-txt-follow="Save changes"
									value="{{ trans('contactreports::contactreports.add report') }}"
									disabled="true" />
								<input id="INPUT_clear" type="reset"
									class="btn btn-link btn-clear"
									data-txt-search="{{ trans('contactreports::contactreports.clear') }}"
									data-txt-add="{{ trans('contactreports::contactreports.clear') }}"
									data-txt-edit="Cancel edit"
									data-txt-follow="Cancel changes"
									value="{{ trans('contactreports::contactreports.clear') }}" />
							</div>
						</div>

						<input id="myuserid" type="hidden" value="{{ auth()->user()->id }}" />
						<input id="page" type="hidden" value="{{ request()->input('page', 1) }}" />

						<span id="TAB_search_action"></span>
						<span id="TAB_add_action"></span>
						<span id="crm_action"></span>
					</fieldset>
				</form>

			</div><!-- / #DIV_crm -->
		</div><!-- / #tabMain -->

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

http://www.example.com

[" . config('app.name') . "](http://www.example.com)

By using [Title] notation immediately preceding a URL in parentheses, you can give it another title.

Email addresses will automatically be converted into mailto links: " . config('mail.from.address');


$help1e = "      The interface will ignore any artificial
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

| Node     | Cores   | Memory   |
|----------|--------:|---------:|
| Carter-A | 16      | 32GB     |
| Carter-B | 16      | 64GB     |
";
?>
		<div id="help1" class="dialog dialog-help" title="Text Formatting">
			<ul>
				<li><a href="#help1a">Fonts</a></li>
				<li><a href="#help1b">Lists</a></li>
				<li><a href="#help1c">Links</a></li>
				<li><a href="#help1e">Line Breaks</a></li>
				<li><a href="#help1f">Code</a></li>
				<li><a href="#help1g">Tables</a></li>
				<li><a href="#help1h">Variables</a></li>
			</ul>
			<div id="help1a">
				<?php
				$article = new App\Modules\ContactReports\Models\Report(['report' => $help1a]);
				?>
				<p>Input text: <textarea id="help1ainput" class="samplebox" data-sample="a"><?php echo $help1a; ?></textarea></p>
				<p>Output text: <br/><div id="help1aoutput" class="sampleoutput"><?php echo $article->toHtml(); ?></div></p>
			</div>
			<div id="help1b">
				<?php
				$article = new App\Modules\ContactReports\Models\Report(['report' => $help1b]);
				?>
				<p>Input text: <textarea id="help1binput" class="samplebox" data-sample="b"><?php echo $help1b; ?></textarea></p>
				<p>Output text: <br/><div id="help1boutput" class="sampleoutput"><?php echo $article->toHtml(); ?></div></p>
			</div>
			<div id="help1c">
				<?php
				$article = new App\Modules\ContactReports\Models\Report(['report' => $help1c]);
				?>
				<p>Input text: <textarea id="help1cinput" class="samplebox" data-sample="c"><?php echo $help1c; ?></textarea></p>
				<p>Output text: <br/><div id="help1coutput" class="sampleoutput"><?php echo $article->toHtml(); ?></div></p>
			</div>
			<div id="help1e">
				<?php
				$article = new App\Modules\ContactReports\Models\Report(['report' => $help1e]);
				?>
				<p>Input text: <textarea id="help1einput" class="samplebox" data-sample="e"><?php echo $help1e; ?></textarea></p>
				<p>Output text: <br/><div id="help1eoutput" class="sampleoutput"><?php echo $article->toHtml(); ?></div></p>
			</div>
			<div id="help1f">
				<?php
				$article = new App\Modules\ContactReports\Models\Report(['report' => $help1f]);
				?>
				<p>Input text: <textarea id="help1finput" class="samplebox" data-sample="f"><?php echo $help1f; ?></textarea></p>
				<p>Output text: <br/><div id="help1foutput" class="sampleoutput"><?php echo $article->toHtml(); ?></div></p>
			</div>
			<div id="help1g">
				<?php
				$article = new App\Modules\ContactReports\Models\Report(['report' => $help1g]);
				?>
				<p>Input text: <textarea id="help1ginput" class="samplebox" data-sample="g"><?php echo $help1g; ?></textarea></p>
				<p>Output text: <br/><div id="help1goutput" class="sampleoutput"><?php echo $article->toHtml(); ?></div></p>
			</div>
		</div>

		<?php
		$valid_args = array('start', 'stop', 'id', 'group', 'people', 'resource', 'search', 'tag');

		$string = array();

		foreach ($valid_args as $key)
		{
			if (request()->has($key))
			{
				$val = request()->input($key);

				if (is_array($val))
				{
					$string[] = $key . '[]=' . implode('&' . $key . '[]=', $val);
				}
				elseif ($val)
				{
					$string[] = $key . '=' . $val;
				}
			}
		}

		$string = implode('&', $string);
		?>
		<div id="reports" data-query="{{ $string }}" data-api="{{ route('api.contactreports.index') }}" data-comments="{{ route('api.contactreports.comments') }}">
			<?php
			/*
			@foreach ($rows as $i => $row)
				<article id="{{ $row->id }}" class="crm-item newEntries">
					<div class="panel panel-default">
						<div class="panel-heading news-admin">
							<span class="crmid"><a href="{{ route('site.contactreports.show', ['id' => $row->id]) }}">#{{ $row->id }}</a></span>
						</div>
						<div class="panel-heading">
							<h3 class="panel-title crmcontactdate">{{ $row->datetimecontact->format('M d, Y') }}</h3>
							<ul class="panel-meta news-meta">
								<li class="news-date"><span class="crmpostdate">Posted on {{ $row->datetimecreated->format('M d, Y') }}</span></li>
								<li class="news-author"><span class="crmposter">Posted by {{ $row->creator->name }}</span></li>
							</ul>
						</div>
						<div class="panel-body">
							<div class="newsposttext">
								<span id="{{ $row->id }}_text">{!! $row->toHtml() !!}</span>
								<span><textarea id="{{ $row->id }}_textarea" rows="7" cols="45" class="form-control crmreportedittextbox" style="display: none;"></textarea></span>
							</div>
						</div>
					</div>
					<div class="crmnewcomment panel panel-default">
						<div class="panel-heading">
							<div class="crmcomment crmsubscribe" id="{{ $row->id }}_subscribed">
								<a href="{{ route('site.contactreports.show', ['id' => $row->id, 'subscribe' => 1]) }}" class="btn btn-default btn-sm">Subscribe</a>
							</div>
						</div>
						<div class="panel-body">
							<div id="{{ $row->id }}_newupdate">
								<textarea class="form-control crmcommentbox" placeholder="Write a comment..." id="5037_newcommentbox" rows="1" cols="45"></textarea>
								<a href="/news/manage?update&amp;id=5037" title="Add a new comment."><span class="fa fa-save" aria-hidden="true" id="5037_newcommentboxsave" style="display: none;"></span></a>
							</div>
						</div>
					</div>
					<ul id="{{ $row->id }}_comments" class="crm-comments">
						@foreach ($row->comments()->orderBy('datetimecreated', 'asc')->get() as $comment)
						@endforeach
					</ul>
				</article>
			@endforeach
			*/
			?>
		</div><!-- / #reports -->
		<?php /*
		{{ $rows->render() }}
		*/ ?>

		@if ($report)
		<script type="application/json" id="crm-data">
			{
				"original": {
					"id": "<?php echo $report->id; ?>",
					"api": "<?php echo route('api.contactreports.update', ['id' => $report->id]); ?>",
					"datetimecontact": "<?php echo $report->datetimecontact->format('Y-m-d'); ?>",
					"groupid": "<?php echo $report->groupid; ?>",
					"groupname": "<?php echo $report->group ? $report->group->name : ''; ?>",
					"groupage": "<?php echo $report->groupage; ?>",
					"age": "",
					"users": <?php echo json_encode($report->users); ?>,
					"resources": <?php echo json_encode($report->resources); ?>,
					"note": <?php echo json_encode($report->report); ?>,
					"contactreporttype": "<?php echo $report->type ? $report->type->name : ''; ?>",
					"contactreporttypeid": "<?php echo $report->contactreporttypeid; ?>"
				},
				"originalusers": [],
				"originalcontactusers": []
			}
		</script>
		@endif

		<script type="application/json" id="crm-search-data">
			{
				"followerofgroups": <?php echo json_encode($followinggroups); ?>,
				"followerofusers": <?php echo json_encode($followingusers); ?>,
				"groups": <?php echo request()->has('groups') ? json_encode(explode(',', request()->input('groups'))) : '[]'; ?>,
				"people": <?php echo request()->has('people') ? json_encode(explode(',', request()->input('people'))) : '[]'; ?>
			}
		</script>
	</div><!-- / #everything -->
</div><!-- / .contentInner -->
</div>
@stop