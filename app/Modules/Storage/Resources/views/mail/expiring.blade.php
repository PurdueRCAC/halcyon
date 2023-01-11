@component('mail::message')
Hello {{ $user->name }},

The following storage spaces for {{ $group->name }} have loans or purchases that will be expiring soon:

@foreach ($directories as $directory)
* `{{ $directory->storageResource->path . '/' . $directory->path }}`
@endforeach

You may review these storage allocations at the following URL:

[{{ route('site.users.account.section.show.subsection', ['section' => 'groups', 'id' => $group->id, 'subsection' => 'storage']) }}]({{ route('site.users.account.section.show.subsection', ['section' => 'groups', 'id' => $group->id, 'subsection' => 'storage']) }})

@endcomponent