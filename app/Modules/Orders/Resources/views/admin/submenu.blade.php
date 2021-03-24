
<nav class="container-fluid" aria-label="{{ trans('orders::orders.module sections') }}">
	<ul class="nav nav-tabs">
		<li class="nav-item">
			<a class="nav-link @if (!trim($slot) || trim($slot) == 'orders') active @endif" href="{{ route('admin.orders.index') }}">{{ trans('orders::orders.orders') }}</a>
		</li>
		<li class="nav-item">
			<a class="nav-link @if (trim($slot) == 'products') active @endif" href="{{ route('admin.orders.products') }}">{{ trans('orders::orders.products') }}</a>
		</li>
		<li class="nav-item">
			<a class="nav-link @if (trim($slot) == 'categories') active @endif" href="{{ route('admin.orders.categories') }}">{{ trans('orders::orders.categories') }}</a>
		</li>
	</ul>
</nav><!-- / .sub-navigation -->
