@component('mail::message')
Hello {{ $user->name }},

This is an automated message notifying that Order #{{ $order->id }} has been **canceled**.

---

@include('orders::mail.orderdetails', ['order' => $order])

---

You may view this order in detail from the [Order Management]({{ route('site.orders.read', ['id' => $order->id]) }}) page. If you have any questions about this process please contact rcac-help@purdue.edu
@endcomponent