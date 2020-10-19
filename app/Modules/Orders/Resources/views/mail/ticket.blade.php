@component('mail::message')
ASSIGNEES=RC_SUPPORT
CATEGORY=RESEARCH
SERVICE=RESEARCH COMPUTING
SERVICE OFFERING=HIGH-PERFORMANCE COMPUTING RESOURCES
TICKET TYPE=SERVICE REQUEST
URGENCY=WORKING NORMALLY
IMPACT=MINIMAL
TECH NOTES=Automatically generated from {{ route('site.orders.read', ['id' => $order->id]) }}

This is an automated message notifying that the payment accounts for Order #{{ $order->id }} have been approved by the business office(s). This order is ready for fulfillment.

Order #{{ $order->id }}
{{ route('site.orders.read', ['id' => $order->id]) }}
Order for {{ $order->user->name }}
Submitted by {{ $order->submitter->name }}
{{ $order->datetimecreated->format('Y-m-d h:i:s') }}

---

@include('orders::mail.orderdetails', ['order' => $order])

---

@endcomponent