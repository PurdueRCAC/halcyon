@component('mail::message')
Hello {$user->name},

This is an automated message notifying that the payment accounts for Order #{$order->id} have been approved by the business office(s). ITaP staff have begun the process of fulfilling this order. You will receive another email once the order is fulfilled.

---

@include('orders::mail.orderdetails', ['order' => $order])

---

You may view this order in detail from the [Order Management](https://www.rcac.purdue.edu/order/{$order->id}) page. If you have any questions about this process please contact rcac-help@purdue.edu

@endcomponent