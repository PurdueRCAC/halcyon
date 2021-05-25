@component('mail::message')
Hello {{ $user->name }},

@if ($user->can('manage orders'))
This is an automated message notifying that a payment account on {{ config('app.name') }} Order #{{ $order->id }} has been **denied**. Please review this order.
@else
This is an automated message notifying that one or more purchase accounts on {{ config('app.name') }} Order #{{ $order->id }} have been **denied** by the business office. Please review this order from the [{{ config('app.name') }} Order Management]({{ route('site.orders.read', ['id' => $order->id]) }}) page. Denied accounts should be removed and replaced with the correct accounts.
@endif

---

@include('orders::mail.orderdetails', ['order' => $order])

---

You may view this order in detail or edit this order from the [Order Management]({{ route('site.orders.read', ['id' => $order->id]) }}) page. If you have any questions about this process please contact {{ config('mail.from.address') }}.

@endcomponent