
<nav class="container-fluid" aria-label="{{ trans('queues::queues.module sections') }}">
	<ul class="nav nav-tabs">
		<li class="nav-item">
			<a class="nav-link @if (!trim($slot) || trim($slot) == 'edit') active @endif" href="{{ route('admin.queues.index') }}">{{ trans('queues::queues.queues') }}</a>
		</li>
		<li class="nav-item">
			<a class="nav-link @if (trim($slot) == 'types') active @endif" href="{{ route('admin.queues.types') }}">{{ trans('queues::queues.types') }}</a>
		</li>
		<li class="nav-item">
			<a class="nav-link @if (trim($slot) == 'schedulers') active @endif" href="{{ route('admin.queues.schedulers') }}">{{ trans('queues::queues.schedulers') }}</a>
		</li>
		<li class="nav-item">
			<a class="nav-link @if (trim($slot) == 'schedulerpolicies') active @endif" href="{{ route('admin.queues.schedulerpolicies') }}">{{ trans('queues::queues.scheduler policies') }}</a>
		</li>
	</ul>
</nav><!-- / .sub-navigation -->
