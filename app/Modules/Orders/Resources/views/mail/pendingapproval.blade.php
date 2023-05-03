@component('mail::message')
Hello {{ $user->name }},

This is an automated message notifying that you have been designated as an approver for one or more purchase accounts on {{ config('app.name') }} Order #{{ $order->id }}. Please review this order and approve the purchase accounts from the [{{ config('app.name') }} Order Management]({{ route('site.orders.read', ['id' => $order->id]) }}) page.

---

@include('orders::mail.orderdetails', ['order' => $order])

---

You may view this order in detail from the [Order Management]({{ route('site.orders.read', ['id' => $order->id]) }}) page.
@endcomponent