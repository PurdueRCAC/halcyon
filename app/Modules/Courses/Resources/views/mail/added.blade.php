@component('mail::message')
Hello {{ $user->name }},

Your request for a Class account has been received:

**Resource:** {{ $class->resource->name }}<br/>
**CRN:** {{ $class->crn }} - {{ $class->department }} {{ $class->coursenumber }} - {{ $class->classname }} - {{ $class->semester }}

All registered students plus accounts for these additional users:

@foreach ($accounts as $account)
* {{ $account->user->name }} ({{ $account->user->username }})
@endforeach

If the semester has begun, accounts will be created during overnight processing and will be ready for use tomorrow. Otherwise, student accounts are created one week prior to the beginning of the semester. Instructor, TA, and other additional users are created immediately to allow for course development prior to the semester.

Any students who add or drop the course will be automatically added or removed from {{ $class->resource->name }} within 1 to 2 business days of notifying the registrar.

**All accounts will be removed 2 weeks after the end of semester grades deadline.** Any instructors wishing to retain access between semesters should register their next class before the current semester ends.

The full policy can be reviewed at:

[{{ route('page', ['uri' => 'policies/' . $class->resource->rolename]) }}]({{ route('page', ['uri' => 'policies/' . $class->resource->rolename]) }})

@endcomponent