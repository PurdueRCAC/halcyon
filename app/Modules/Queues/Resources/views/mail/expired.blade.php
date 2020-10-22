@component('mail::message')
Hello {{ $user->name }},

This is an automated message notifying you that one or more of your {{ config('app.name') }} Reasearcher's accounts has expired. This may be due to the person(s) graduating or otherwise departing from the University. Following are the persons with expired accounts.

@foreach ($people as $person)
* {{ $person->name }}
@endforeach

If the person(s) no longer requires access to your resources, please go to the [Manage Users page](https://www.rcac.purdue.edu/account/user/) and remove access by unchecking their boxes. You will see a separate table for disabled accounts.

If the person(s) will be continuing collaboration with your research group, a Request for Privileges (R4P) will need to be filed to reinstate the person's Career Account. This is typically done by the Business Office of your department. For further instructions on how to file a R4P please visit the following URL.

http://www.purdue.edu/hr/pdf/r4pRequestorInstructions.pdf

Once this process is complete you do not need to do anything further to restore access to {{ config('app.name') }} resources.
@endcomponent