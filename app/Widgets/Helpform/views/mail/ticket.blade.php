
{{ trans('widget.helpform::helpform.email header') }}

---

<?php if ($data['user']): ?>
{{ trans('widget.helpform::helpform.groups') }}:

<?php
// Owner groups
$memberships = $data['user']->groups()
    ->where('groupid', '>', 0)
    //->whereIsManager()
    ->get();

$groups = array();
$q = array();
foreach ($memberships as $membership)
{
    $group = $membership->group;

    if (in_array($group->id, $groups))
    {
      continue;
    }

    $groups[] = $membership->groupid;

    $queues = array();
    foreach ($group->queues as $queue)
    {
      $userids = $queue->users()->withTrashed()
        ->whereIsActive()
        ->get()
        ->pluck('userid')
        ->toArray();

      if (!in_array($data['user']->id, $userids))
      {
        continue;
      }

      $queues[] = $queue;
      $q[] = $queue->id;
    }

    $unixgroups = array();
    foreach ($group->unixGroups as $unixgroup)
    {
      $userids = $unixgroup->members()->withTrashed()
        ->whereIsActive()
        ->get()
        ->pluck('userid')
        ->toArray();

      if (!in_array($data['user']->id, $userids))
      {
        continue;
      }

      $unixgroups[] = $unixgroup->longname;
    }

    //$unixgroups = $group->unixGroups->pluck('longname')->toArray();
?>
* {{ $group->name }} ({{ $membership->type->name }})
@foreach ($queues as $queue)
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

$gs = array();
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
    if (!$queue->subresource)
    {
      continue;
    }

    $group = $queue->group;

    if (!$group || !$group->id || in_array($group->id, $groups))
    {
        continue;
    }

    $groups[] = $group->id;

    if (!isset($gs[$group->name]))
    {
      $gs[$group->name] = array('qu' => $qu, 'queues' => array(), 'unixgroups' => array());
    }
    $gs[$group->name]['queues'][] = $queue;

    //$unixgroups = array();
    foreach ($group->unixGroups as $unixgroup)
    {
      $userids = $unixgroup->members()->withTrashed()
        ->whereIsActive()
        ->get()
        ->pluck('userid')
        ->toArray();

      if (!in_array($data['user']->id, $userids))
      {
        continue;
      }

       $gs[$group->name]['unixgroups'][] = $unixgroup->longname;
    }
}

foreach ($gs as $groupname => $gdata)
{
    //$unixgroups = $group->unixGroups->pluck('longname')->toArray();
?>
* {{ $groupname }} ({{ $gdata['qu']->type->name }})
@foreach ($gdata['queues'] as $queue)
  * {{ trans('widget.helpform::helpform.queue') }}: {{ $queue->name }} ({{ $queue->subresource->name }})
@endforeach
@if (!empty($gdata['unixgroups']))
  * {{ trans('widget.helpform::helpform.unix groups') }}: {{ implode(', ', $gdata['unixgroups']) }}
@endif
<?php
}

// Get cases where the user is only apart of a unix group
$unixgroups = \App\Modules\Groups\Models\UnixGroupMember::query()
    ->withTrashed()
    ->whereIsActive()
    ->where('userid', '=', $data['user']->id)
    ->get();

$gs = array();
foreach ($unixgroups as $ug)
{
    $unixgroup = $ug->unixgroup;

    if (!$unixgroup || $unixgroup->isTrashed())
    {
        continue;
    }

    if (!$unixgroup->group || $unixgroup->group->id)
    {
        continue;
    }

    $group = $unixgroup->group;

    if (in_array($group->id, $groups))
    {
        continue;
    }

    $groups[] = $group->id;

    if (!isset($gs[$group->name]))
    {
      $gs[$group->name] = array();
    }
    $gs[$group->name][] = $unixgroup->name;
}

foreach ($gs as $groupname => $unixgroups)
{
?>
* {{ $groupname }}
  * {{ trans('widget.helpform::helpform.unix groups') }}: {{ implode(', ', $unixgroups) }}
<?php
}
?>

----
<?php endif; ?>

{{ trans('widget.helpform::helpform.resources') }}: {{ $data['resources'] }}

{{ $data['report'] }}
