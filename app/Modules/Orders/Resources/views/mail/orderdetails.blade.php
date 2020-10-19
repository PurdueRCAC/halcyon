
**Order:** [#{{ $order->id }}]({{ route('site.orders.read', ['id' => $order->id]) }})<br />
**Order for:** {{ $order->user->name }}<br />
**Submitted by:** {{ $order->submitter ? $order->submitter->name : $order->user->name }}<br />
**Submitted:** {{ $order->datetimecreated->format('Y-m-d h:i:s') }}

@component('mail::table')
| Product | Quantity | Price | Notes |
|---------|---------:|------:|-------|
@foreach ($order->items as $item)
<?php
$renew = '';
if ($item->isRecurring())
{
	$timeperiod = $item->product->timeperiod;

	if ($item->timeperiodcount == 1)
	{
		$renew = 'Service for ' . $item->timeperiodcount . ' ' . $timeperiod->singular . ', then will renew at the ' . $timeperiod->name . ' rate';
	}
	else
	{
		$renew = 'Service for ' . $item->timeperiodcount . ' ' . $timeperiod->plural . ', then will renew at the ' . $timeperiod->name . ' rate';
	}

	if (!$item->isOriginal())
	{
		$renew = ucfirst($timeperiod->name) . ' renewal of a previous order';
	}
}
?>
| {{ $item->product->name }} | {{ $item->quantity }} | ${{ $item->price }} | {{ $renew }} |
@endforeach
@endcomponent

@if (count($order->accounts))
@php
$remaining = $order->total;
@endphp
@component('mail::table')
| Item               |    Amount | Notes |
| -------------------|----------:|-------|
| Order Total        | ${{ $order->formattedTotal }} |       |
@foreach ($order->accounts as $account)
| Account ${{ $account->account }} | ${{ $account->formattedAmount }} | {{ $account->budgetjustification }} |
@php
$remaining -= $account->amount;
@endphp
@endforeach
| Balance Remaining  | ${{ $order->formatNumber($remaining) }} |   |
@endcomponent
@endif

@if ($order->usernotes)
**Notes:**

> {{ $order->usernotes }}
@endif

@if ($user->can('manage orders'))
**Internal Notes:**

> {{ $order->staffnotes }}
@endif
