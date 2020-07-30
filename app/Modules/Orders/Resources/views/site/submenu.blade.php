
<nav role="navigation">
	<ul class="dropdown-menu">
		<li class="nav-item @if (!trim($slot) || trim($slot) == 'orders') active @endif">
			<a class="nav-link" href="{{ route('site.orders.index') }}">{{ trans('orders::orders.orders') }}</a>
		</li>
		<li class="nav-item @if (trim($slot) == 'products') active @endif">
			<a class="nav-link" href="{{ route('site.orders.products') }}">{{ trans('orders::orders.products') }}</a>
		</li>
		@if (auth()->user() && auth()->user()->can('manage orders'))
		<li class="nav-item @if (trim($slot) == 'categories') active @endif">
			<a class="nav-link" href="{{ route('site.orders.categories') }}">{{ trans('orders::orders.categories') }}</a>
		</li>
		<li class="nav-item @if (trim($slot) == 'recur') active @endif">
			<a class="nav-link" href="{{ route('site.orders.recurring') }}">{{ trans('orders::orders.recurring') }}</a>
		</li>
		@endif
	</ul>
</nav><!-- / .sub-navigation -->
