
{{ trans('widget.helpform::helpform.email header') }}

---

<?php if ($data['user']): ?>
{{ trans('widget.helpform::helpform.groups') }}:

<?php
  // Owner groups
  $memberships = $data['user']->groups()
      ->where('groupid', '>', 0)
      ->whereIsManager()
      ->get();

  $q = array();
  foreach ($memberships as $membership)
  {
      $group = $membership->group;

      $unixgroups = $group->unixGroups->pluck('longname')->toArray();
?>
* {{ $group->name }} ({{ $membership->type->name }})
@foreach ($group->queues as $queue)
  * {{ trans('widget.helpform::helpform.queue') }}: {{ $queue->name }} ({{ $queue->subresource->name }})
@endforeach
@if (!empty($unixgroups))
  * {{ trans('widget.helpform::helpform.unix groups') }}: {{ implode(', ', $unixgroups) }}
@endif
<?php
}

$queues = $data['user']->queues()
    //->where('groupid', '>', 0)
    ->whereIn('membertype', [1, 4])
    ->whereNotIn('id', $q)
    ->withTrashed()
    ->whereIsActive()
    ->get();

foreach ($queues as $qu)
{
    if ($qu->isMember()
    && $qu->isTrashed())
    {
        continue;
    }

    $queue = $qu->queue;

    if (!$queue || $queue->isTrashed())
    {
        continue;
    }

    if (!$queue->scheduler || $queue->scheduler->isTrashed())
    {
        continue;
    }

    $group = $queue->group;

    if (!$group || !$group->id)
    {
        continue;
    }

    $unixgroups = $group->unixGroups->pluck('longname')->toArray();
?>
* {{ $group->name }} ({{ $qu->type->name }})
  * {{ trans('widget.helpform::helpform.queue') }}: {{ $queue->name }} ({{ $queue->subresource->name }})
@if (!empty($unixgroups))
  * {{ trans('widget.helpform::helpform.unix groups') }}: {{ implode(', ', $unixgroups) }}
@endif
<?php
}
?>

----
<?php endif; ?>

{{ trans('widget.helpform::helpform.resources') }}: {{ $data['resources'] }}

{{ $data['report'] }}
