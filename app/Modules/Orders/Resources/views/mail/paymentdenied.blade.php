@component('mail::message')
Hello {{ $user->name }},

This is an automated message notifying that one or more purchase accounts on ITaP Research Computing Order #{{ $order->id }} have been **denied** by the business office. Please review this order from the [ITaP Research Computing Order Management](https://www.rcac.purdue.edu/order/{$order->id}) page. Denied accounts should be removed and replaced with the correct accounts.

---

@include('orders::mail.orderdetails', ['order' => $order])

---

You may view this order in detail or edit this order from the [Order Management](https://www.rcac.purdue.edu/order/{$order->id}) page. If you have any questions about this process please contact rcac-help@purdue.edu

@endcomponent