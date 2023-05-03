@component('mail::message')
Hello {{ $user->name }},

@if ($order->total > 0)
This is an automated message notifying that a {{ $order->type }} order has been entered and is awaiting payment information so it may be processed.

Please review your order and enter the account numbers to be used for payment on the [Order Management]({{ route('site.orders.read', ['id' => $order->id]) }}) page. You may edit or cancel the order before entering account information. Once payment information is entered, your order will be sent to the appropriate business office(s) for funding approval.
@else
This is an automated message notifying that a {{ $order->type }} order has been entered and is awaiting processing. You will receive another email once the order is fulfilled.
@endif

---

@include('orders::mail.orderdetails', ['order' => $order])

---

You may view this order in detail or edit this order from the [Order Management]({{ route('site.orders.read', ['id' => $order->id]) }}) page.
@endcomponent