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

			<div class="form-group">
				<label for="filter_group">{{ trans('contactreports::contactreports.group') }}</label>
				<?php
				$grps = array();
				if ($groups = $filters['group']):
					foreach (explode(',', $groups) as $g):
						if (trim($g)):
							$grp = App\Modules\Groups\Models\Group::find($g);
							$grps[] = $grp->name . ':' . $g . '';
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
					foreach ($users as $u):
						if (trim($u)):
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
	<div id="results">
	@if (count($rows))
		<?php
		$highlight = config('module.contactreports.highlight', []);
		?>
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
									@if ($row->datetimecreated->format('Y-m-dTh:i:s') > Carbon\Carbon::now()->toDateTimeString())
										{{ $row->datetimecreated->diffForHumans() }}
									@else
										{{ $row->datetimecreated->format('M d, Y') }}
									@endif
								</time>
							@else
								<span class="never">{{ trans('global.unknown') }}</span>
							@endif
						</div>
						<div class="text-muted ml-4">
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
							@if (count($users))
								<span class="fa fa-user" aria-hidden="true"></span>
								{!! implode(', ', $staff) !!} {!! implode(', ', $users) !!}
							@endif
						</div>
						<div class="flex-fill text-right">
							@if (auth()->user()->can('create contactreports'))
								<a href="{{ route('admin.contactreports.create', ['groupid' => $row->groupid, 'contactreporttypeid' => $row->contactreporttypeid, 'people' => implode(',', $uids), 'resources' => implode(',', $row->resources->pluck('resourceid')->toArray())]) }}" class="mr-2" title="Start new Contact Report with these details.">
									<span class="fa fa-copy" aria-hidden="true"></span>
									<span class="sr-only">Start new Contact Report with these details.</span>
								</a>
							@endif
							@if (auth()->user()->can('edit contactreports'))
								<a href="{{ route('admin.contactreports.edit', ['id' => $row->id]) }}">
									<span class="fa fa-pencil" aria-hidden="true"></span>
									<span class="sr-only"># {{ $row->id }}</span>
								</a>
							@else
								# {{ $row->id }}
							@endif
						</div>
					</div>
				</div>
				<div class="card-body">
					{!! $row->toHtml() !!}
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
							echo '<p>' . implode(' ', $names) . '</p>';
						endif;
						?>
					@endif
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
	@else
		<div class="card mb-4">
			<div class="card-body text-muted text-center">{{ trans('global.no results') }}</div>
		</div>
	@endif
	</div>
		</div>
		</div>
	<input type="hidden" name="boxchecked" value="0" />
</form>

@stop