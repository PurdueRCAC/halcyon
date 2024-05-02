@extends('layouts.master')

@php
app('pathway')
	->append(
		trans('config::config.module name'),
		route('admin.config')
	)
	->append(
		trans($module->element . '::' . $module->element . '.module name'),
		route('admin.' . strtolower($module->element) . '.index')
	);
@endphp

@section('toolbar')
	@if (auth()->user()->can('edit storage'))
		{!! Toolbar::save(route('admin.config.module.update', ['module' => $module->element])) !!}
	@endif

	{!!
		Toolbar::spacer();
		Toolbar::cancel(route('admin.' . strtolower($module->element) . '.index'));
	!!}

	{!! Toolbar::render() !!}
@stop

@section('title')
{{ trans($module->element . '::system.name') . ': ' . trans('config::config.module configuration') }}
@stop

@section('content')
<form action="{{ route('admin.config.module.update', ['module' => $module->element]) }}" id="adminForm{{ request()->ajax() ? '-ajax' : '' }}" method="post" name="adminform" autocomplete="off" class="form-validate">
	@if (request()->ajax())
		<div class="toolbar-box">
			<div class="pagetitle text-right text-end">
				{!! Toolbar::render() !!}
			</div>
		</div>
	@endif

	<?php
	if ($form) :
		$fieldSets = $form->getFieldsets();

		if (count($fieldSets)):
			?>
			<nav class="container-fluid">
				<ul id="config-tabs" class="nav nav-tabs config-option-list" role="tablist">
					@php
					$i = 0;
					@endphp
					@foreach ($fieldSets as $name => $fieldSet)
						<li class="nav-item" role="presentation">
							<a class="nav-link{{ $i == 0 ? ' active' : '' }}" href="#config-tab-{{ $name }}" data-toggle="tab" data-bs-toggle="tab" role="tab" id="config-tab-{{ $name }}-tab" aria-controls="config-tab-{{ $name }}" aria-selected="true">
								{{ trans(empty($fieldSet->label) ? 'config::modules.' . $name . ' fieldset label' : $fieldSet->label) }}
							</a>
						</li>
						@php
						$i++;
						@endphp
					@endforeach
				</ul>
			</nav>
			<div class="tab-content" id="config-tabs-content">
			@php
			$i = 0;
			@endphp
			@foreach ($fieldSets as $name => $fieldSet)
				<div class="tab-pane{{ $i == 0 ? ' show active' : '' }}" id="config-tab-{{ $name }}" role="tabpanel" aria-labelledby="config-tab-{{ $name }}-tab">
					<fieldset>
						@if (isset($fieldSet->description) && !empty($fieldSet->description))
							<p class="tab-description">{{ trans($fieldSet->description) }}</p>
						@endif
						@foreach ($form->getFieldset($name) as $field)
							<div class="form-group">
								@if (!$field->hidden)
									<?php echo $field->label; ?>
								@endif
								<?php echo $field->input; ?>
							</div>
						@endforeach
					</fieldset>
				</div>
				@php
				$i++;
				@endphp
			@endforeach
			</div>
			<?php
		else:
			?>
			<p class="alert alert-warning">No configuration options found.</p>
			<?php
		endif;
	else :
		echo '<p class="alert alert-warning">' . trans('config::modules.not found', ['module' => $module->element]) . '</p>';
	endif;
	?>

	<input type="hidden" name="id" value="{{ $module->id }}" />
	<input type="hidden" name="module" value="{{ $module->element }}" />

	@csrf
</form>
@stop