@if ($row->id && method_exists($row, 'history'))
	<div class="data-wrap">
		<h4>{{ trans('history::history.history') }}</h4>
		<ul class="entry-log timeline">
			<?php
			$history = $row->history()->orderBy('created_at', 'desc')->get();

			if (count($history)):
				foreach ($history as $action):
					$actor = trans('global.unknown');

					if ($action->user):
						$actor = e($action->user->name);
					endif;

					$created = $action->created_at ? $action->created_at : trans('global.unknown');

					if (is_object($action->new)):
						$f = get_object_vars($action->new);
					elseif (is_array($action->new)):
						$f = $action->new;
					endif;

					$fields = array_keys($f);
					foreach ($fields as $i => $k):
						if (in_array($k, ['created_at', 'updated_at', 'deleted_at'])):
							unset($fields[$i]);
						endif;
					endforeach;

					$old = Carbon\Carbon::now()->subDays(2); //->toDateTimeString();
					?>
					<li class="{{ $action->action }}">
						<span class="entry-action">{{ trans('history::history.action ' . $action->action, ['user' => $actor, 'entity' => 'menu']) }}</span><br />
						<span class="entry-date">
							<time datetime="{{ $action->created_at->toDateTimeLocalString() }}">
							@if ($action->created_at < $old)
								{{ $action->created_at->format('d M Y') }}
							@else
								{{ $action->created_at->diffForHumans() }}
							@endif
							</time>
						</span>
						@if ($action->action == 'updated')
							<span class="entry-diff">{{ trans('history::history.changed fields') }}: <code><?php echo implode('</code>, <code>', $fields); ?></code></span>
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
