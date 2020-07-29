
**Order:** [#{{ $order->id }}]({{ route('site.orders.show', ['id' => $order->id]) }})
**Order for:** {{ $order->user->name }}
**Submitted by:** {{ $order->submitter->name }}
**Submitted:** {{ $order->created }}

@component('mail::table')
| Product | Quantity | Price | Notes |
|---------|---------:|------:|-------|
@foreach ($products as $product)
| {$product} | {$quantity} | $${price} | {$renew} |
@endforeach
@endcomponent

@component('mail::table')
| Item               |    Amount | Notes |
| -------------------|----------:|-------|
| Order Total        | ${{ $total }} |       |
@foreach ($accounts as $account)
| Account ${account} | ${{ $accountamount }} | {{ $justification }} |
@endforeach
| Balance Remaining  | ${{ $remaining }} |   |
@endcomponent

**Notes:**

> {$order->usernotes}

