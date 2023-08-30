@extends('layouts.master')

@push('styles')
<link rel="stylesheet" type="text/css" media="all" href="{{ timestamped_asset('modules/core/vendor/tom-select/css/tom-select.bootstrap4.min.css') }}" />
@endpush

@push('scripts')
<script src="{{ timestamped_asset('modules/core/vendor/tom-select/js/tom-select.complete.min.js') }}"></script>
<script src="{{ timestamped_asset('modules/core/vendor/chartjs/Chart.min.js') }}"></script>
<script src="{{ timestamped_asset('modules/contactreports/js/admin.js') }}"></script>
@endpush

@php
app('pathway')
	->append(
		trans('contactreports::contactreports.module name'),
		route('admin.contactreports.index')
	);
@endphp

@section('toolbar')
	@if (auth()->user()->can('delete contactreports'))
		{!! Toolbar::deleteList('', route('admin.contactreports.delete')) !!}
	@endif

	@if (auth()->user()->can('create contactreports'))
		{!! Toolbar::addNew(route('admin.contactreports.create')) !!}
	@endif

	@if (auth()->user()->can('admin contactreports'))
		{!!
			Toolbar::spacer();
			Toolbar::preferences('contactreports')
		!!}
	@endif

	{!! Toolbar::render() !!}
@stop

@section('title')
{{ trans('contactreports::contactreports.module name') }}
@stop

@section('content')
@component('contactreports::admin.submenu')
	reports
@endcomponent
<form action="{{ route('admin.contactreports.index') }}" data-api="{{ route('api.contactreports.index') }}" method="get" name="adminForm" id="adminForm" class="form-inlin">

	<div class="row">
		<div class="col-md-3">
		<fieldset id="filter-bar" class="container-fluid">
			<div class="form-group">
				<div class="form-group">
					<label class="sr-only" for="filter_search">{{ trans('search.label') }}</label>
					<span class="input-group">
						<input type="text" name="search" id="filter_search" class="form-control filter" placeholder="{{ trans('search.placeholder') }}" value="{{ $filters['search'] }}" />
						<span class="input-group-append"><button type="submit" class="input-group-text"><span class="icon-search" aria-hidden="true"></span><span class="sr-only">{{ trans('search.submit') }}</span></button></span>
					</span>
				</div>
			</div>
			<div class="form-group">
				<label for="filter_contactreporttypeid">{{ trans('contactreports::contactreports.type') }}</label>
				<select name="type" id="filter_contactreporttypeid" class="form-control filter filter-submit">
					<option value="*"<?php if ($filters['type'] == '*') { echo ' selected="selected"'; } ?>>{{ trans('contactreports::contactreports.all types') }}</option>
					<option value="0"<?php if (!$filters['type']) { echo ' selected="selected"'; } ?>>{{ trans('global.none') }}</option>
					@foreach ($types as $type)
						<option value="{{ $type->id }}"<?php if ($filters['type'] == $type->id) { echo ' selected="selected"'; } ?>>{{ $type->name }}</option>
					@endforeach
				</select>
			</div>
			<div class="form-group">
				<div class="dropdown">
					<?php
					Carbon\Carbon::setWeekStartsAt(Carbon\Carbon::SUNDAY);
					Carbon\Carbon::setWeekEndsAt(Carbon\Carbon::SATURDAY);
					$thisweek = Carbon\Carbon::now();
					$thismonth = Carbon\Carbon::now();
					$thisyear = Carbon\Carbon::now();
					$lastweek = Carbon\Carbon::now()->modify('-1 week');
					$lastmonth = Carbon\Carbon::now()->modify('-1 month');
					$lastyear = Carbon\Carbon::now()->modify('-1 year');
					?>
					Time range
					<button class="btn btn-form-control btn-block dropdown-toggle text-left" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
						<?php
						$active = 'all';
						$text = 'All time';

						if ($filters['start'] || $filters['stop'])
						{
							if ($filters['start'] == $thisweek->startOfWeek()->format('Y-m-d') && $filters['stop'] == $thisweek->endOfWeek()->format('Y-m-d'))
							{
								$text = 'This week <span class="text-muted float-right">' . $thisweek->startOfWeek()->format('d M') . ' - ' . $thisweek->endOfWeek()->format('d M') . ', ' . $thisweek->format('Y') . '</span>';
								$active = 'thisweek';
								$filters['start'] = '';
								$filters['stop'] = '';
							}
							elseif ($filters['start'] == $thismonth->startOfMonth()->format('Y-m-d') && $filters['stop'] == $thismonth->endOfMonth()->format('Y-m-d'))
							{
								$text = 'This month <span class="text-muted float-right">' . $thismonth->startOfMonth()->format('M, Y') . '</span>';
								$active = 'thismonth';
								$filters['start'] = '';
								$filters['stop'] = '';
							}
							elseif ($filters['start'] == $thisyear->format('Y-01-01') && $filters['stop'] == $thisyear->format('Y-12-31'))
							{
								$text = 'This year <span class="text-muted float-right">' . $thisyear->format('Y') . '</span>';
								$active = 'thisyear';
								$filters['start'] = '';
								$filters['stop'] = '';
							}
							elseif ($filters['start'] == $lastweek->startOfWeek()->format('Y-m-d') && $filters['stop'] == $lastweek->endOfWeek()->format('Y-m-d'))
							{
								$text = 'Last week <span class="text-muted float-right">' . $lastweek->startOfWeek()->format('d M') . ' - ' . $lastweek->endOfWeek()->format('d M') . ', ' . $lastweek->format('Y') . '</span>';
								$active = 'lastweek';
								$filters['start'] = '';
								$filters['stop'] = '';
							}
							elseif ($filters['start'] == $lastmonth->startOfMonth()->format('Y-m-d') && $filters['stop'] == $lastmonth->endOfMonth()->format('Y-m-d'))
							{
								$text = 'Last month <span class="text-muted float-right">' . $lastmonth->startOfMonth()->format('M, Y') . '</span>';
								$active = 'lastmonth';
								$filters['start'] = '';
								$filters['stop'] = '';
							}
							elseif ($filters['start'] == $lastyear->format('Y-01-01') && $filters['stop'] == $lastyear->format('Y-12-31'))
							{
								$text = 'Last year <span class="text-muted float-right">' . $lastyear->format('Y') . '</span>';
								$active = 'lastyear';
								$filters['start'] = '';
								$filters['stop'] = '';
							}
							else
							{
								$active = 'custom';

								if ($filters['start'] && !$filters['stop'])
								{
									$text = $filters['start'] . ' to now';
								}
								elseif (!$filters['start'] && $filters['stop'])
								{
									$text = 'until ' . $filters['stop'];
								}
								elseif ($filters['start'] && $filters['stop'])
								{
									$text = $filters['start'] . ' - ' . $filters['stop'];
								}
							}
						}

						echo $text;
						?>
					</button>
					<div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
						@if ($active != 'all')
						<a class="dropdown-item<?php if ($active == 'all') { echo ' active'; } ?>" href="{{ route('admin.contactreports.index', ['start' => '', 'stop' => '']) }}">All time</span></a>
						<div class="dropdown-divider"></div>
						@endif
						<a class="dropdown-item<?php if ($active == 'thisweek') { echo ' active'; } ?>" href="{{ route('admin.contactreports.index', ['start' => $thisweek->startOfWeek()->format('Y-m-d'), 'stop' => $thisweek->endOfWeek()->format('Y-m-d')]) }}">This week <span class="text-muted float-right">{{ $thisweek->startOfWeek()->format('d M') }} - {{ $thisweek->endOfWeek()->format('d M') }}, {{ $thisweek->format('Y') }}</span></a>
						<a class="dropdown-item<?php if ($active == 'thismonth') { echo ' active'; } ?>" href="{{ route('admin.contactreports.index', ['start' => $thismonth->startOfMonth()->format('Y-m-d'), 'stop' => $thismonth->endOfMonth()->format('Y-m-d')]) }}">This month <span class="text-muted float-right">{{ $thismonth->format('M, Y') }}</span></a>
						<a class="dropdown-item<?php if ($active == 'thisyear') { echo ' active'; } ?>" href="{{ route('admin.contactreports.index', ['start' => $thisyear->format('Y') . '-01-01', 'stop' => $thisyear->format('Y-12-31')]) }}">This year <span class="text-muted float-right">{{ $thisyear->format('Y') }}</span></a>
						<div class="dropdown-divider"></div>
						<a class="dropdown-item<?php if ($active == 'lastweek') { echo ' active'; } ?>" href="{{ route('admin.contactreports.index', ['start' => $lastweek->startOfWeek()->format('Y-m-d'), 'stop' => $lastweek->endOfWeek()->format('Y-m-d')]) }}">Last week <span class="text-muted float-right">{{ $lastweek->startOfWeek()->format('d M') }} - {{ $lastweek->endOfWeek()->format('d M') }}, {{ $lastweek->format('Y') }}</span></a>
						<a class="dropdown-item<?php if ($active == 'lastmonth') { echo ' active'; } ?>" href="{{ route('admin.contactreports.index', ['start' => $lastmonth->startOfMonth()->format('Y-m-d'), 'stop' => $lastmonth->endOfMonth()->format('Y-m-d')]) }}">Last month <span class="text-muted float-right">{{ $lastmonth->format('M, Y') }}</span></a>
						<a class="dropdown-item<?php if ($active == 'lastyear') { echo ' active'; } ?>" href="{{ route('admin.contactreports.index', ['start' => $lastyear->format('Y') . '-01-01', 'stop' => $lastyear->format('Y') . '-12-31']) }}">Last year <span class="text-muted float-right">{{ $lastyear->format('Y') }}</span></a>
						<div class="dropdown-divider"></div>
						<h6 class="dropdown-header">Custom range</h6>
						<div class="px-4">
							<div class="form-group">
								<label for="filter_start">{{ trans('contactreports::contactreports.start') }}</label>
								<span class="input-group">
									<input type="text" name="start" id="filter_start" class="form-control filter filter-submit date" value="{{ $filters['start'] }}" />
									<span class="input-group-append"><span class="input-group-text"><span class="fa fa-calendar" aria-hidden="true"></span></span>
								</span>
							</div>
							<div class="form-group">
								<label for="filter_stop">{{ trans('contactreports::contactreports.stop') }}</label>
								<span class="input-group">
									<input type="text" name="stop" id="filter_stop" class="form-control filter filter-submit date" value="{{ $filters['stop'] }}" placeholder="{{ trans('global.never') }}" />
									<span class="input-group-append"><span class="input-group-text"><span class="fa fa-calendar" aria-hidden="true"></span></span></span>
								</span>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="form-group">
				<label for="filter_group">{{ trans('contactreports::contactreports.group') }}</label>
				<?php
				$grps = array();
				if ($groups = $filters['group']):
					foreach (explode(',', $groups) as $g):
						$g = trim($g);
						if (!$g):
							continue;
						endif;
						if (strstr($g, ':')):
							$grps[] = $g;
						else:
							$grp = App\Modules\Groups\Models\Group::find($g);
							$grps[] = $grp->name . ':' . $g;
						endif;
					endforeach;
				endif;
				?>
				<input name="group" id="filter_group" size="45" class="form-control form-groups filter-submit" value="{{ implode(',', $grps) }}" data-uri="{{ route('api.groups.index') }}?search=%s" data-api="{{ route('api.groups.index') }}" />
			</div>

			<div class="form-group">
				<label for="filter_people">{{ trans('contactreports::contactreports.users') }}</label>
				<?php
				$usrs = array();
				if ($users = $filters['people']):
					foreach (explode(',', $users) as $u):
						$u = trim($u);
						if (!$u):
							continue;
						endif;
						if (strstr($u, ':')):
							$usrs[] = $u;
						else:
							if (!is_numeric($u)):
								$usr = App\Modules\Users\Models\User::findByUsername($u);
							else:
								$usr = App\Modules\Users\Models\User::find($u);
							endif;
							$usrs[] = ($usr ? $usr->name : trans('global.unknown')) . ':' . $u;
						endif;
					endforeach;
				endif;
				?>
				<input name="people" id="filter_people" size="45" class="form-control form-users filter-submit" value="{{ implode(',', $usrs) }}" data-uri="{{ route('api.users.index') }}?search=%s" data-api="{{ route('api.users.index') }}" />
			</div>

			<div class="form-group">
				<label for="crmresource">{{ trans('contactreports::contactreports.resources') }}</label>
				<?php
				$selected = array();
				if ($res = $filters['resource']):
					$selected = is_string($res) ? explode(',', $res) : $res;
					$selected = array_map('trim', $selected);
				endif;
				?>
				<select class="form-control filter-submit searchable-select-multi" multiple="multiple" name="resource[]" id="crmresource" data-api="{{ route('api.resources.index') }}">
					<?php
					$resources = App\Modules\Resources\Models\Asset::query()
						->where('listname', '!=', '')
						->where('display', '>', 0)
						->orderBy('name')
						->get();

					$types = array();
					foreach ($resources as $resource):
						if (!isset($types[$resource->resourcetype])):
							$types[$resource->resourcetype] = array();
						endif;
						$types[$resource->resourcetype][] = $resource;
					endforeach;
					ksort($types);

					foreach ($types as $t => $res):
						$type = App\Modules\Resources\Models\Type::find($t);
						if (!$type):
							$type = new App\Modules\Resources\Models\Type;
							$type->name = 'Services';
						endif;
						?>
						<optgroup label="{{ $type->name }}" class="select2-result-selectable">
							<?php
							foreach ($res as $resource):
								?>
								<option value="{{ $resource->id }}"<?php if (in_array($resource->id, $selected)) { echo ' selected="selected"'; } ?>>{{ $resource->name }}</option>
								<?php
							endforeach;
							?>
						</optgroup>
						<?php
					endforeach;
					?>
				</select>
			</div>

			<div class="form-group">
				<label for="filter_tag">{{ trans('contactreports::contactreports.tags') }}</label>
				<?php
				$tags = array();
				if ($tg = $filters['tag']):
					foreach (explode(',', $tg) as $t):
						if (trim($t)):
							$tag = App\Modules\Tags\Models\Tag::query()->where('slug', '=', $t)->first();
							if (!$tag):
								continue;
							endif;
							$tags[] = $tag->slug; // . ':' . $t;
						endif;
					endforeach;
				endif;
				?>
				<input name="tag" id="filter_tag" size="45" class="form-control form-tags filter-submit" value="{{ implode(', ', $tags) }}" data-uri="{{ route('api.tags.index') }}?search=%s" data-api="{{ route('api.tags.index') }}" />
			</div>

			<input type="hidden" name="order" value="{{ $filters['order'] }}" />
			<input type="hidden" name="order_dir" value="{{ $filters['order_dir'] }}" />

			<button class="btn btn-secondary sr-only" type="submit">{{ trans('search.submit') }}</button>
		</fieldset>
		</div>
		<div class="col-md-9">
	@if (count($rows))
		<?php
		$highlight = config('module.contactreports.highlight', []);
		?>
		<div id="results">
		@foreach ($rows as $i => $row)
			<div class="card mb-3">
				<div class="card-header">
					<div class="d-flex">
						@if (auth()->user()->can('delete contactreports'))
							<div>
								{!! Html::grid('id', $i, $row->id) !!}
							</div>
						@endif
						<div class="text-muted ml-4">
							<span class="fa fa-calendar" aria-hidden="true"></span>
							@if ($row->datetimecreated)
								<time datetime="{{ $row->datetimecreated->toDateTimeLocalString() }}">
									@if ($row->datetimecreated->timestamp >= Carbon\Carbon::now()->modify('-1 day')->timestamp)
										{{ $row->datetimecreated->diffForHumans() }}
									@else
										{{ $row->datetimecreated->format('M d, Y') }}
									@endif
								</time>
							@else
								<span class="never">{{ trans('global.unknown') }}</span>
							@endif
						</div>
						<?php
						$uids = $row->users->pluck('userid')->toArray();
						?>
						<div class="flex-fill text-right">
							@if (auth()->user()->can('create contactreports'))
								<a href="{{ route('admin.contactreports.create', ['groupid' => $row->groupid, 'contactreporttypeid' => $row->contactreporttypeid, 'people' => implode(',', $uids), 'resources' => implode(',', $row->resources->pluck('resourceid')->toArray())]) }}" class="bt bt-sm ml-3" title="Start new Contact Report with these details.">
									<span class="fa fa-copy" aria-hidden="true"></span>
									<span class="sr-onl">Duplicate</span>
								</a>
							@endif
							@if (auth()->user()->can('edit contactreports') || (auth()->user()->can('edit.own contactreports') && $row->userid = auth()->user()->id))
								<a href="{{ route('admin.contactreports.edit', ['id' => $row->id]) }}" class="bt bt-sm ml-3" title="Edit this Contact Report.">
									<span class="fa fa-pencil" aria-hidden="true"></span>
									<span class="sr-onl">{{ trans('global.button.edit') }}</span>
								</a>
							@else
								# {{ $row->id }}
							@endif
							@if (auth()->user()->can('delete contactreports'))
								<a href="{{ route('admin.contactreports.delete', ['id' => $row->id]) }}" class="bt bt-sm ml-3" data-confirm="Are you sure you want to remove this entry?" title="Delete this Contact Report.">
									<span class="fa fa-trash" aria-hidden="true"></span>
									<span class="sr-onl">{{ trans('global.button.delete') }}</span>
								</a>
							@endif
						</div>
					</div>
				</div>
				<div class="card-body">
					<?php
					$uids = $row->users->pluck('userid')->toArray();

					$users = array();
					$staff = array();
					if ($row->userid && !in_array($row->userid, $uids)):
						$staff[] = ($row->creator ? '<a href="' . route('admin.users.show', ['id' => $row->userid]) . '"class="badge badge-danger">' . $row->creator->name . '</a>' : $row->userid . ' <span class="unknown">' . trans('global.unknown') . '</span>');
					endif;

					foreach ($row->users as $user):
						$cls = 'secondary';

						if ($user->user):
							foreach ($user->user->getAuthorisedRoles() as $role):
								if (in_array($role, $highlight)):
									$cls = 'danger';
									break;
								endif;
							endforeach;
						endif;

						$u  = '<a href="' . route('admin.users.show', ['id' => $user->userid]) . '" class="badge badge-' . $cls . '">';
						$u .= ($user->user ? $user->user->name : $user->userid . ' <span class="unknown">' . trans('global.unknown') . '</span>');

						if ($user->notified()):
							$u .= ' <span class="fa fa-envelope" data-tip="Followup email sent on ' . $user->datetimelastnotify->toDateTimeString() . '"><time class="sr-only" datetime="' . $user->datetimelastnotify->toDateTimeString() . '">' . $user->datetimelastnotify->format('Y-m-d') . '</time></span>';
						endif;
						$u .= '</a>';

						if ($cls == 'danger'):
							$staff[] = $u;
						else:
							$users[] = $u;
						endif;
					endforeach;
					?>
					@if (count($users) || count($row->resources))
						<div class="pb-2">
							@if (count($users))
								<div class="text-muted mb-2">
									<span class="fa fa-user" aria-hidden="true"></span>
									{!! implode(', ', $staff) !!} {!! implode(', ', $users) !!}
								</div>
							@endif
							@if (count($row->resources))
								<?php
								$names = array();

								foreach ($row->resources as $res):
									if ($res->resource):
										$names[] = '<span class="badge badge-info">' . e($res->resource->name) . '</span>';
									else:
										$names[] = $res->resourceid;
									endif;
								endforeach;

								asort($names);

								if (count($names)):
									?>
									<div class="text-muted mb-2">
										<span class="fa fa-server" aria-hidden="true"></span>
										<?php echo implode(' ', $names); ?>
									</div>
									<?php
								endif;
								?>
							@endif
						</div>
					@endif

					{!! App\Halcyon\Utility\Str::highlight($row->toHtml(), $filters['search'], ['html' => true]) !!}
				</div>
				<div class="card-footer">
					<div class="d-flex text-muted">
						<div class="flex-fill">
							<span class="fa fa-folder" aria-hidden="true"></span>
							{{ $row->type ? $row->type->name : trans('global.none') }}
						</div>
						<div class="flex-fill text-right">
							<span class="fa fa-comment" aria-hidden="true"></span>
							@if (count($row->comments))
								<a href="#comments_{{ $row->id }}" class="comments-show">{{ number_format(count($row->comments)) }}</a>
							@else
								<span class="none">0</span>
							@endif
						</div>
					</div>
				</div>
			</div>
			<?php
			$comments = $row->comments->sortBy('datetimecreated');

			if (count($comments) > 0):
				?>
				<div class="ml-4 mb-3 d-none" id="comments_{{ $row->id }}">
					<ul class="list-group">
						@foreach ($comments as $comment)
							<li id="comment_{{ $comment->id }}" class="list-group-item" data-api="{{ route('api.contactreports.comments.update', ['id' => $comment->id]) }}">
								{!! $comment->formattedComment !!}

								<div class="d-flex text-muted">
									<div class="mr-4">
										<span class="fa fa-calendar" aria-hidden="true"></span>
										<time datetime="{{ $comment->datetimecreated->toDateTimeLocalString() }}">
											{{ $comment->datetimecreated->format('M d, Y') }} @ {{ $comment->datetimecreated->format('h:i a') }}
										</time>
									</div>
									<div class="mr-4">
										@if ($comment->creator)
											<span class="fa fa-user" aria-hidden="true"></span>
											{{ $comment->creator->name }}
										@endif
									</div>
									<div class="flex-fill text-right">
									</div>
								</div>
							</li>
						@endforeach
					</ul>
				</div>
				<?php
			endif;
			?>
		@endforeach

		{{ $rows->render() }}
		</div>
	@else
		<div class="d-flex h-100 align-items-center">
			<div class="text-center w-50 mx-auto">
				<div class="display-4"><span class="fa fa-ban" aria-hidden="true"></span></div>
				<p>{{ trans('global.no results') }}</p>
			</div>
		</div>
	@endif
	
		</div>
	</div>
	<input type="hidden" name="boxchecked" value="0" />
</form>

@stop