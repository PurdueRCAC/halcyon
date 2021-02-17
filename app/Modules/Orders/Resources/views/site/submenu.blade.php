
<nav role="navigation" class="container-fluid mb-3">
	<ul class="nav nav-tabs">
		<li class="nav-item">
			<a class="nav-link @if (!trim($slot) || trim($slot) == 'orders') active @endif" href="{{ route('site.orders.index') }}">{{ trans('orders::orders.orders') }}</a>
		</li>
		<li class="nav-item">
			<a class="nav-link @if (trim($slot) == 'products') active @endif" href="{{ route('site.orders.products') }}">{{ trans('orders::orders.products') }}</a>
		</li>
		@if (auth()->user() && auth()->user()->can('manage orders'))
		<li class="nav-item">
			<a class="nav-link @if (trim($slot) == 'categories') active @endif" href="{{ route('site.orders.categories') }}">{{ trans('orders::orders.categories') }}</a>
		</li>
		<li class="nav-item">
			<a class="nav-link @if (trim($slot) == 'recur') active @endif" href="{{ route('site.orders.recurring') }}">{{ trans('orders::orders.recurring') }}</a>
		</li>
		@endif
	</ul>
</nav><!-- / .sub-navigation -->
