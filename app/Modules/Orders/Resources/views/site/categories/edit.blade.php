@extends('layouts.master')

@section('toolbar')
	@if (auth()->user()->can('edit orders.categories'))
		{!! Toolbar::save(route('admin.orders.categories.store')) !!}
	@endif

	{!!
		Toolbar::spacer();
		Toolbar::cancel(route('admin.orders.categories.cancel'));
	!!}

	{!! Toolbar::render() !!}
@stop

@section('title')
{!! config('orders.name') !!}: {{ trans('orders::orders.categories') }}: {{ $row->id ? 'Edit: #' . $row->id : 'Create' }}
@stop

@section('content')
<form action="{{ route('admin.orders.categories.store') }}" method="post" name="adminForm" id="item-form" class="editform form-validate" data-invalid-msg="{{ trans('JGLOBAL_VALIDATION_FORM_FAILED') }}">

	@if ($errors->any())
		<div class="alert alert-error">
			<ul>
				@foreach ($errors->all() as $error)
					<li>{{ $error }}</li>
				@endforeach
			</ul>
		</div>
	@endif

	<div class="grid row">
		<div class="col col-md-7 span7">
			<fieldset class="adminform">
				<legend>{{ trans('global.details') }}</legend>

				<div class="form-group{{ $errors->has('parentordercategoryid') ? ' has-error' : '' }}">
					<label for="field-parentordercategoryid">{{ trans('orders::orders.parent category') }}</label>
					<select name="fields[parentordercategoryid]" id="field-parentordercategoryid" class="form-control">
						<option value="1"<?php if ($row->parentordercategoryid == 1): echo ' selected="selected"'; endif;?>>{{ trans('global.none') }}</option>
						<?php foreach ($categories as $category) { ?>
							<option value="{{ $category->id }}"<?php if ($row->parentordercategoryid == $category->id): echo ' selected="selected"'; endif;?>>{{ $category->name }}</option>
						<?php } ?>
					</select>
				</div>

				<div class="form-group{{ $errors->has('name') ? ' has-error' : '' }}">
					<label for="field-name">{{ trans('orders::orders.name') }}: <span class="required">{{ trans('global.required') }}</span></label>
					<input type="text" name="fields[name]" id="field-name" class="form-control required" maxlength="250" value="{{ $row->name }}" />
				</div>

				<div class="form-group{{ $errors->has('description') ? ' has-error' : '' }}">
					<label for="field-description">{{ trans('orders::orders.description') }}:</label>
					<textarea name="fields[description]" id="field-description" class="form-control" cols="30" rows="5">{{ $row->description }}</textarea>
				</div>
			</fieldset>
		</div>
		<div class="col col-md-5 span5">
			<fieldset class="adminform">
				<legend>{{ trans('global.publishing') }}</legend>

				<div class="form-group">
					<label for="field-state">{{ trans('global.state') }}:</label>
					<select class="form-control" name="state" id="field-state">
						<option value="published"<?php if (!$row->trashed()) { echo ' selected="selected"'; } ?>>{{ trans('global.published') }}</option>
						<option value="trashed"<?php if ($row->trashed()) { echo ' selected="selected"'; } ?>>{{ trans('global.trashed') }}</option>
					</select>
				</div>
			</fieldset>

			@if ($row->id)
				<div class="data-wrap">
					<h4>{{ trans('history::history.history') }}</h4>
					<ul class="entry-log">
						<?php
						$history = $row->history()->orderBy('created_at', 'desc')->get();

						if (count($history)):
							foreach ($history as $action):
								$actor = trans('global.unknown');

								if ($action->user):
									$actor = e($action->user->name);
								endif;

								$created = $action->created_at && $action->created_at != '0000-00-00 00:00:00' ? $action->created_at : trans('global.unknown');
								$old = Carbon\Carbon::now()->subDays(2); //->toDateTimeString();

								if ($action->action == 'updated')
								{
									if (is_object($action->new))
									{
										$fields = array_keys(get_object_vars($action->new));
									}
									else
									{
										$fields = array_keys($action->new);
									}

									foreach ($fields as $i => $k)
									{
										if (in_array($k, ['created_at', 'updated_at', 'deleted_at']))
										{
											unset($fields[$i]);
										}
									}
								}
								?>
								<li>
									<span class="entry-log-action">{{ trans('history::history.action ' . $action->action, ['user' => $actor, 'entity' => 'menu']) }}</span><br />
									<time datetime="{{ $action->created_at }}" class="entry-log-date">
										@if ($action->created_at < $old)
											{{ $action->created_at->format('d M Y') }}
										@else
											{{ $action->created_at->diffForHumans() }}
										@endif
									</time><br />
									@if ($action->action == 'updated')
										<span class="entry-diff">Changed fields: <?php echo implode(', ', $fields); ?></span>
									@endif
								</li>
								<?php
							endforeach;
						else:
							?>
							<li>
								<span class="entry-diff">{{ trans('history::history.none found') }}</span>
							</li>
							<?php
						endif;
						?>
					</ul>
				</div>
			@endif
		</div>
	</div>
	<input type="hidden" name="id" id="field-id" value="{{ $row->id }}" />

	@csrf
</form>
@stop