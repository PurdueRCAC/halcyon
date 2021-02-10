@component('mail::message', ['alert' => 'danger'])
Hello {{ $user->name }},

Your request to remove a Class has been received:

**Resource:** {{ $class->resource->name }}<br/>
**CRN:** {{ $class->crn }} - {{ $class->department }} {{ $class->coursenumber }} - {{ $class->classname }} - {{ $class->semester }}

All registered students plus accounts for these additional users will be removed during overnight processing:

@foreach ($accounts as $account)
* {{ $account->user->name }} ({{ $account->user->username }})
@endforeach

@endcomponent