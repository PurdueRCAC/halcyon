@component('mail::message')
Hello {{ $user->name }},

@if ($user->can('manage orders'))
This is an automated message notifying that payment for Order #{{ $order->id }} has been **<span style="color:green">approved</span>**. This order is ready for fulfillment.
@else
This is an automated message notifying that the payment accounts for Order #{{ $order->id }} have been approved by the business office(s). {{ config('app.name') }} staff have begun the process of fulfilling this order. You will receive another email once the order is fulfilled.
@endif

---

@include('orders::mail.orderdetails', ['order' => $order])

---

You may view this order in detail from the [Order Management]({{ route('site.orders.read', ['id' => $order->id]) }}) page.

@endcomponent