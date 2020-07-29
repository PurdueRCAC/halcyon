@component('mail::message')
Hello {$admin->name},

This is an automated message notifying that an order has payment information entered. This order is ready for business office assignment.

Please assign business office approvers on the [Order Management](https://www.rcac.purdue.edu/order/{$order->id}) page.

---

@include('orders::mail.orderdetails', ['order' => $order])

---

You may view this order in detail or edit this order from the [Order Management](https://www.rcac.purdue.edu/order/{$order->id}) page.

@endcomponent