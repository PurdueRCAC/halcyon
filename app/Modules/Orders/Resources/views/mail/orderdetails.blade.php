
**Order:** [#{{ $order->id }}]({{ route('site.orders.read', ['id' => $order->id]) }})<br />
**Order for:** {{ ($order->user ? $order->user->name : '') }}<br />
**Submitted by:** {{ $order->submitter ? $order->submitter->name : ($order->user ? $order->user->name : '') }}<br />
**Submitted:** {{ $order->datetimecreated->format('M d, Y H:i a') }}

@component('mail::table')
| Product         | Quantity | Price |
|-----------------|---------:|------:|
@foreach ($order->items as $item)
<?php
$renew = '';
if ($item->isRecurring()):
	$timeperiod = $item->product->timeperiod;

	$renew = 'Service for ' . $item->timeperiodcount;
	if ($timeperiod):
		$renew .= ' ' . ($item->timeperiodcount > 1 ? $timeperiod->plural : $timeperiod->singular) . ', then will renew at the ' . $timeperiod->name . ' rate';

		if (!$item->isOriginal()):
			$renew = ucfirst($timeperiod->name) . ' renewal of a previous order';
		endif;
	endif;
endif;
?>
| {{ $item->product->name }}{!! $renew ? '<br />' . '_' . e($renew) . '_' : '' !!} | {{ $item->quantity }} | ${{ $item->formattedPrice }}{!! $item->product->unit ? '<br />_per ' . $item->product->unit . '_' : '' !!} |
@endforeach
| **Order Total** |          | ${{ $order->formattedTotal }} |
@endcomponent

@if (count($order->accounts))
@php
$remaining = $order->total;
@endphp
@component('mail::table')
| Payment               |    Amount |
| ----------------------|----------:|
@foreach ($order->accounts as $account)
| Account {{ $account->account }}{!! $account->budgetjustification ? '<br />' . '_' . e(trim($account->budgetjustification)) . '_' : '' !!} | ${{ $account->formattedAmount }} |
@php
$remaining -= $account->amount;
@endphp
@endforeach
| **Balance Remaining** | ${{ $order->formatNumber($remaining) }} |
@endcomponent
@endif

@if ($order->usernotes)
**Notes:**

> {!! str_replace("\n", '<br />', $order->usernotes) !!}
@endif

@if ($user->can('manage orders') && $order->staffnotes)
**Internal Notes:**

> {!! str_replace("\n", '<br />', $order->staffnotes) !!}
@endif
