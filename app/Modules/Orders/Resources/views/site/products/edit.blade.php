@extends('layouts.master')

@section('title')
{!! config('orders.name') !!}: {{ trans('orders::orders.products') }}: {{ $row->id ? 'Edit: #' . $row->id : 'Create' }}
@stop

@php
	app('pathway')
		->append(
			trans('orders::orders.orders'),
			route('site.orders.index')
		)
		->append(
			trans('orders::orders.products'),
			route('site.orders.products')
		)
		->append(
			$row->name,
			route('site.orders.products.edit', ['id' => $row->id])
		);
@endphp

@section('content')
<form action="{{ route('admin.orders.products.store') }}" method="post" name="adminForm" id="item-form" class="editform form-validate" data-invalid-msg="{{ trans('JGLOBAL_VALIDATION_FORM_FAILED') }}">
	<div class="row">
		<div class="col col-md-7">
			<fieldset>
				<legend>{{ trans('global.details') }}</legend>

				<div class="form-group">
					<label for="field-parentordercategoryid">{{ trans('orders::orders.parent category') }}</label>
					<select name="fields[parentordercategoryid]" id="field-parentordercategoryid" class="form-control filter filter-submit">
						<option value="1"<?php if ($row->parentordercategoryid == 1): echo ' selected="selected"'; endif;?>>{{ trans('global.none') }}</option>
						<?php foreach ($categories as $category) { ?>
							<option value="<?php echo $category->id; ?>"<?php if ($row->ordercategoryid == $category->id): echo ' selected="selected"'; endif;?>>{{ $category->name }}</option>
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

				<div class="row">
					<div class="col col-md-6">
						<div class="form-group{{ $errors->has('unitprice') ? ' has-error' : '' }}">
							<label for="field-unitprice">{{ trans('orders::orders.price') }}: <span class="required">{{ trans('global.required') }}</span></label>
							<input type="text" name="fields[unitprice]" id="field-unitprice" class="form-control required" maxlength="250" value="{{ $row->unitprice }}" />
						</div>
					</div>
					<div class="col col-md-6">
						<div class="form-group{{ $errors->has('unit') ? ' has-error' : '' }}">
							<label for="field-unit">{{ trans('orders::orders.unit') }}: <span class="required">{{ trans('global.required') }}</span></label>
							<input type="text" name="fields[unit]" id="field-unit" class="form-control required" maxlength="250" value="{{ $row->unit }}" />
						</div>
					</div>
				</div>

				<div class="form-group{{ $errors->has('resourceid') ? ' has-error' : '' }}">
					<label for="field-resourceid">{{ trans('orders::orders.resource') }}:</label>
					<select class="form-control" name="fields[resourceid]" id="field-resourceid">
						<option value="0"<?php if (!$row->resourceid) { echo ' selected="selected"'; } ?>>{{ trans('global.none') }}</option>
						<?php foreach (App\Modules\Resources\Entities\Asset::all() as $resource): ?>
							<option value="{{ $resource->id }}"<?php if ($row->resourceid == $resource->id) { echo ' selected="selected"'; } ?>>{{ $resource->name }}</option>
						<?php endforeach; ?>
					</select>
				</div>

				<div class="form-group{{ $errors->has('mou') ? ' has-error' : '' }}">
					<label for="field-mou">{{ trans('orders::orders.mou') }}:</label>
					<input type="text" name="fields[mou]" id="field-mou" class="form-control" placeholder="http://" value="{{ $row->mou }}" />
				</div>

				<div class="row">
					<div class="col col-md-6">
						<div class="form-group{{ $errors->has('recurringtimeperiod') ? ' has-error' : '' }}">
							<label for="field-recurringtimeperiod">{{ trans('orders::orders.recurrence') }}:</label>
							<select class="form-control" name="fields[recurringtimeperiod]" id="field-recurringtimeperiod">
								<option value="0"<?php if (!$row->recurringtimeperiodid) { echo ' selected="selected"'; } ?>>{{ trans('global.none') }}</option>
								<?php foreach (App\Halcyon\Models\Timeperiod::all() as $period): ?>
									<option value="{{ $period->id }}"<?php if ($row->recurringtimeperiodid == $period->id) { echo ' selected="selected"'; } ?>>{{ $period->name }}</option>
								<?php endforeach; ?>
							</select>
						</div>
					</div>
					<div class="col col-md-6">
						<div class="form-check{{ $errors->has('restricteddata') ? ' has-error' : '' }}">
							<input class="form-check-input" type="checkbox" id="field-restricteddata" name="fields[restricteddata]" value="1"<?php if ($row->restricteddata) { echo ' checked="checked"'; } ?> />
							<label class="form-check-label" for="field-restricteddata">{{ trans('orders::orders.restricted data') }}</label>
							<span class="form-text">{{ trans('orders::orders.restricted data explanation') }}</span>
						</div>
					</div>
				</div>

				<div class="form-group{{ $errors->has('terms') ? ' has-error' : '' }}">
					<label for="field-terms">{{ trans('orders::orders.terms') }}:</label>
					<textarea name="fields[terms]" id="field-terms" class="form-control" cols="30" rows="5">{{ $row->terms }}</textarea>
				</div>
			</fieldset>
		</div>
		<div class="col col-md-5 span5">
			<fieldset class="adminform">
				<legend>{{ trans('global.publishing') }}</legend>

				<div class="form-group">
					<label for="field-state">{{ trans('global.state') }}:</label>
					<select class="form-control" name="fields[state]" id="field-state">
						<option value="0"<?php if (!$row->trashed()) { echo ' selected="selected"'; } ?>>{{ trans('global.published') }}</option>
						<option value="1"<?php if ($row->trashed()) { echo ' selected="selected"'; } ?>>{{ trans('global.trashed') }}</option>
					</select>
				</div>

				<div class="form-group">
					<label for="field-access">{{ trans('global.access') }}:</label>
					<select class="form-control" name="fields[public]" id="field-public">
						<?php foreach (App\Halcyon\Access\Viewlevel::all() as $access): ?>
							<option value="<?php echo $access->id; ?>"<?php if ($row->public == $access->id) { echo ' selected="selected"'; } ?>>{{ $access->title }}</option>
						<?php endforeach; ?>
					</select>
				</div>
			</fieldset>

			<?php if ($row->id): ?>
				<div class="data-wrap">
					<h4>{{ trans('global.history') }}</h4>
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

								$fields = array_keys(get_object_vars($action->new));
								foreach ($fields as $i => $k)
								{
									if (in_array($k, ['created_at', 'updated_at', 'deleted_at']))
									{
										unset($fields[$i]);
									}
								}
								$old = Carbon\Carbon::now()->subDays(2); //->toDateTimeString();
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
			<?php endif; ?>
		</div>
	</div>

	<p class="text-center">
		<input type="submit" class="btn btn-success" value="{{ trans('global.save') }}" />
		<a class="btn btn-outline" href="{{ route('site.orders.products') }}">{{ trans('global.cancel') }}</a>
	</p>

	<input type="hidden" name="id" id="field-id" value="{{ $row->id }}" />

	@csrf
</form>
@stop