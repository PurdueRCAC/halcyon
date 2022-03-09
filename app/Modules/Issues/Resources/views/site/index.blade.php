@extends('layouts.master')

@push('styles')
<link rel="stylesheet" type="text/css" media="all" href="{{ asset('modules/core/vendor/tagsinput/jquery.tagsinput.css?v=' . filemtime(public_path() . '/modules/core/vendor/tagsinput/jquery.tagsinput.css')) }}" />
<link rel="stylesheet" type="text/css" media="all" href="{{ asset('modules/core/vendor/jquery-timepicker/jquery.timepicker.css?v=' . filemtime(public_path() . '/modules/core/vendor/jquery-timepicker/jquery.timepicker.css')) }}" />
<link rel="stylesheet" type="text/css" media="all" href="{{ asset('modules/issues/css/site.css?v=' . filemtime(public_path() . '/modules/issues/css/site.css')) }}" />
@endpush

@push('scripts')
<script src="{{ asset('modules/core/vendor/tagsinput/jquery.tagsinput.js?v=' . filemtime(public_path() . '/modules/core/vendor/tagsinput/jquery.tagsinput.js')) }}"></script>
<script src="{{ asset('modules/core/vendor/jquery-timepicker/jquery.timepicker.js?v=' . filemtime(public_path() . '/modules/core/vendor/jquery-timepicker/jquery.timepicker.js')) }}"></script>
<script src="{{ asset('modules/issues/js/site.js?v=' . filemtime(public_path() . '/modules/issues/js/site.js')) }}"></script>
@endpush

@php
app('pathway')->append(
	trans('issues::issues.issues'),
	route('site.issues.index')
);
@endphp

@section('content')
<div class="row">
<div class="sidenav col-lg-4 col-md-4 col-sm-12 col-xs-12">
	<h3 class="card-title panel-title">Checklist</h3>
	<div class="card panel panel-default panel-issuetodos tab-search">
		@if (auth()->user()->can('manage issues'))
			<div class="card-header panel-heading">
				<div class="row">
					<div class="col-md-12 text-right">
						<a href="{{ route('site.issues.todos') }}" class="btn btn-sm btn-default btn-secondary">Manage</a>
					</div>
				</div>
			</div>
		@endif
		<?php
		foreach ($todos as $i => $todo)
		{
			$now = new DateTime('now');

			// Check for completed todos in the recurring time period
			switch ($todo->timeperiod->name)
			{
				case 'hourly':
					$period = $now->format('Y-m-d h') . ':00:00';
					$badge = 'danger';
				break;

				case 'daily':
					$period = $now->format('Y-m-d') . ' 00:00:00';
					$badge = 'warning';
				break;

				case 'weekly':
					$day = date('w');
					$period = $now->modify('-' . $day . ' days')->format('Y-m-d') . ' 00:00:00';
					$badge = 'info';
				break;

				case 'monthly':
					$period = $now->format('Y-m-01') . ' 00:00:00';
				break;

				case 'annual':
					$period = $now->format('Y-01-01') . ' 00:00:00';
				break;

				default:
					$badge = 'secondary';
				break;
			}

			$issues = $todo->issues()->where('datetimecreated', '>=', $period)->count();

			// We found an item for this time period
			if ($issues)
			{
				unset($todos[$i]);
			}
		}
		?>
		@if (count($todos))
			<ul class="list-group checklist">
				@foreach ($todos as $todo)
					<li class="list-group-item">
						<div class="form-group">
							<div class="form-check">
								<input type="checkbox" class="form-check-input issue-todo" data-name="{{ $todo->name }}" data-id="{{ $todo->id }}" data-api="{{ route('api.issues.create') }}" name="todo{{ $todo->id }}" id="todo{{ $todo->id }}" value="1" />
								<label class="form-check-label" for="todo{{ $todo->id }}"><span class="badge badge-{{ $badge }}">{{ $todo->timeperiod->name }}</span> {{ $todo->name }}</label>
								@if ($todo->description)
									<div class="form-text text-muted">{!! $todo->formattedDescription !!}</div>
								@endif
							</div>
							<span class="issue-todo-alert tip"><span class="fa" aria-hidden="true"></span></span>
						</div>
					</li>
				@endforeach
			</ul>
		@else
			<ul class="list-group checklist">
				<li class="list-group-item text-center">All caught up!</li>
			</ul>
		@endif
	</div>
</div>
<div class="contentInner col-lg-8 col-md-8 col-sm-12 col-xs-12">

<div id="everything">
	<ul class="nav nav-tabs issues-tabs">
		<li data-toggle="tab"><a id="TAB_search" class="tab activeTab" href="#search">Search</a></li>
		<li data-toggle="tab"><a id="TAB_add" class="tab" href="#add">Add New</a></li>
	</ul>
	<div class="tabMain" id="tabMain">
		<div id="DIV_search">
			<form method="get" action="{{ route('site.issues.index') }}" class="editform">
				<fieldset>
					<legend><span id="SPAN_header" data-search="Search Reports" data-add="Add New Reports" data-edit="Edit Reports">Search Reports</span></legend>

					<div class="form-group row tab-search tab-add tab-edit" id="TR_date">
						<label for="datestartshort" class="col-sm-2 col-form-label">Date from</label>
						<div class="col-sm-4">
							<?php
							$startdate = '';
							$starttime = '';
							if ($filters['start'])
							{
								$value = explode('!', $filters['start']);
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
							if ($filters['stop'] && $filters['stop'] != '0000-00-00 00:00:00')
							{
								$value = explode('!', $filters['stop']);
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

							//$now = new DateTime('now');
							//$startdate ?: $now->format('Y-m-d');
							//$starttime ?: $now->format('h:i a');
							?>
							<div class="input-group">
								<span class="input-group-addon"><span class="input-group-text fa fa-calendar" aria-hidden="true"></span></span>
								<input id="datestartshort" type="text" class="date-pick form-control" name="start" placeholder="YYYY-MM-DD" data-start="{{ $startdate }}" value="{{ $startdate }}" />
							</div>
							<div class="input-group input-time tab-add tab-edit hide">
								<span class="input-group-addon"><span class="input-group-text fa fa-clock-o" aria-hidden="true"></span></span>
								<input id="timestartshort" type="text" class="time-pick form-control" name="starttime" placeholder="h:mm AM/PM" value="{{ $starttime }}" />
							</div>
						</div>

						<label for="datestopshort" class="col-sm-2 col-form-label align-right tab-search">Date to</label>
						<div class="col-sm-4 tab-search">
							<div class="input-group" id="enddate">
								<span class="input-group-addon"><span class="input-group-text fa fa-calendar" aria-hidden="true"></span></span>
								<input id="datestopshort" type="text" class="date-pick form-control" name="stop" placeholder="YYYY-MM-DD" data-stop="{{ $stopdate }}" value="{{ $stopdate }}">
							</div>
							<div class="input-group input-time tab-add tab-edit hide">
								<span class="input-group-addon"><span class="input-group-text fa fa-clock-o" aria-hidden="true"></span></span>
								<input id="timestopshort" type="text" class="time-pick form-control" name="stoptime" placeholder="h:mm AM/PM" value="{{ $stoptime }}" />
							</div>
						</div>
					</div>

					<div class="form-group row tab-search" id="TR_keywords">
						<label for="keywords" class="col-sm-2 col-form-label">Keywords</label>
						<div class="col-sm-10">
							<input type="text" name="keyword" id="keywords" size="45" class="form-control" value="{{ $filters['keyword'] }}" />
						</div>
					</div>

					<div class="form-group row tab-search" id="TR_id">
						<label for="id" class="col-sm-2 col-form-label">ID#</label>
						<div class="col-sm-10">
							<input name="id" type="text" id="id" size="45" class="form-control" value="{{ $filters['id'] }}" />
						</div>
					</div>

					<div class="form-group row tab-search" id="TR_resolved">
						<label for="resolved" class="col-sm-2 col-form-label">Resolution</label>
						<div class="col-sm-10">
							<select name="resolved" id="resolved" class="form-control">
								<option value="">All</option>
								<option value="-1"<?php echo ($filters['resolved'] == -1 ? ' selected="selected"' : ''); ?>>Unresolved</option>
								<option value="1"<?php echo ($filters['resolved'] == 1 ? ' selected="selected"' : ''); ?>>Resolved</option>
							</select>
						</div>
					</div>

					<div class="form-group row tab-search tab-add tab-edit" id="TR_resource">
						<label for="resource" class="col-sm-2 col-form-label">Resource</label>
						<div class="col-sm-10">
							<?php
							$resources = array();
							if ($rs = $filters['resource'])
							{
								foreach (explode(',', $rs) as $r)
								{
									if (trim($r))
									{
										$resource = App\Modules\Resources\Models\Asset::find($r);
										$resources[] = $resource->name . ':' . $resource->id;
									}
								}
							}
							?>
							<input name="resource" id="resource" size="45" class="form-control" value="{{ implode(',', $resources) }}" data-uri="{{ route('api.resources.index') }}?search=%s" />
						</div>
					</div>

					<div class="form-group row tab-add tab-edit hide" id="TR_notes">
						<label for="NotesText" class="col-sm-2 col-form-label">Notes</label>
						<div class="col-sm-10">
							<textarea id="NotesText" class="form-control" rows="10" cols="80"></textarea>
						</div>
					</div>

					<div class="form-group row tab-search" id="TR_search">
						<div class="col-sm-2">
						</div>
						<div class="col-sm-10 offset-sm-10">
							<input type="submit" class="btn btn-primary" value="Search" id="INPUT_search" />
							<input type="reset" class="btn btn-default btn-clear" value="Clear" id="INPUT_clearsearch" />
						</div>
					</div>

					<div class="form-group row tab-add tab-edit hide" id="TR_create">
						<div class="col-sm-2">
						</div>
						<div class="col-sm-10 offset-sm-10">
							<input id="INPUT_add" type="submit" class="btn btn-primary" value="Add Report" disabled="true" />
							<input id="INPUT_clear" type="reset" class="btn btn-default btn-clear" value="Clear" />
						</div>
					</div>

					<input id="myuserid" type="hidden" value="{{ auth()->user()->id }}" />
					<input id="page" type="hidden" value="{{ $filters['page'] }}" />

					<span id="TAB_search_action"></span>
					<span id="TAB_add_action"></span>
					<span id="issue_action"></span>
				</fieldset>
			</form>

			<?php
			$valid_args = array('start', 'stop', 'id', 'resolved', 'resource', 'keyword');

			$string = array();

			foreach ($valid_args as $key)
			{
				if (request()->has($key))
				{
					$string[] = $key . '=' . request()->input($key);
				}
			}

			$string = implode('&', $string);
			$string = $string ?: 'page=1';
			?>
			<p class="tab-search"><strong id="matchingReports">Matching Reports</strong></p>

			<div id="reports" class="tab-search" data-query="<?php echo $string; ?>" data-api="{{ route('api.issues.index') }}">
				Reports are loading...
			</div>
		</div><!-- / #DIV_search -->
	</div><!-- / .tabMain -->
</div><!-- / #everything -->

<?php
if ($issue)
{
	$value = explode(' ', $issue->datetimecreated);
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

	$resources = $issue->resources->each(function ($res, $key)
	{
		$res->api = route('api.resources.read', ['id' => $res->resourceid]);
		$res->name = $res->resource->name;
	});
	?>
	<script type="application/json" id="report-data">
		{
			"original": {
				"id": "<?php echo $issue->id; ?>",
				"api": "<?php echo route('api.issues.update', ['id' => $issue->id]); ?>",
				"createddate": "<?php echo $issue->datetimecreated; ?>",
				"starttime": "<?php echo $starttime; ?>",
				"resources": <?php echo json_encode($resources); ?>,
				"report": <?php echo json_encode($issue->report); ?>
			}
		}
	</script>
	<?php
}
?>
</div>
</div>
@stop