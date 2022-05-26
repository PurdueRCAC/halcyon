@extends('layouts.master')

@php
app('request')->merge(['hidemainmenu' => 1]);

app('pathway')
	->append(
		trans('mailer::mailer.module name'),
		route('admin.mailer.index')
	)
	->append(
		'#' . $row->id
	);
@endphp

@section('toolbar')
	{!! Toolbar::cancel(route('admin.mailer.cancel')) !!}
	{!! Toolbar::render() !!}
@stop

@section('subject')
{{ trans('mailer::mailer.module name') }}: #{{ $row->id }}
@stop

@section('content')
<form action="{{ route('admin.mailer.send') }}" method="post" name="adminForm" id="item-form" class="editform form-validate">

	<div class="row">
		<div class="col col-md-7">
			<fieldset class="adminform">
				<legend>{{ trans('global.details') }}</legend>

				<div class="form-group">
					<label for="field-subject">{{ trans('mailer::mailer.subject') }}</label>
					<input type="text" name="subject" id="field-subject" readonly class="form-control-plaintext" value="{{ $row->subject }}" />
				</div>

				<div class="form-group">
					<label for="field-body">{{ trans('mailer::mailer.body') }} <span class="required">{{ trans('global.required') }}</span></label>
					<textarea name="body" id="field-body" readonly class="form-control-plaintext" cols="45" rows="40">{{ $row->body }}</textarea>
				</div>
			</fieldset>
		</div>
		<div class="col col-md-5">
			<fieldset class="adminform">
				<legend>{{ trans('mailer::mailer.send to') }}</legend>

				<div class="form-group">
					<label for="field-user">{{ trans('mailer::mailer.to') }}</label>
					<?php
					$rec = $row->recipients->get('to', []);
					$rec = empty($rec) ? [trans('global.none')] : $rec;
					?>
					<input type="text" name="user" id="field-user" readonly class="form-control-plaintext" value="{{ implode(', ', $rec) }}" />
				</div>

				<div class="form-group">
					<label for="field-cc">{{ trans('mailer::mailer.cc') }}</label>
					<?php
					$rec = $row->recipients->get('cc', []);
					$rec = empty($rec) ? [trans('global.none')] : $rec;
					?>
					<input type="text" name="cc" id="field-cc" readonly class="form-control-plaintext" value="{{ implode(', ', $rec) }}" />
				</div>

				<div class="form-group">
					<label for="field-bcc">{{ trans('mailer::mailer.bcc') }}</label>
					<?php
					$rec = $row->recipients->get('bcc', []);
					$rec = empty($rec) ? [trans('global.none')] : $rec;
					?>
					<input type="text" name="bcc" id="field-bcc" readonly class="form-control-plaintext" value="{{ implode(', ', $rec) }}" />
				</div>
			</fieldset>

			<?php /*<iframe src="{{ route('admin.mailer.preview', ['id' => $row->id]) }}" height="700" width="100%"></iframe>*/ ?>
		</div>
	</div>

	@csrf
</form>
@stop