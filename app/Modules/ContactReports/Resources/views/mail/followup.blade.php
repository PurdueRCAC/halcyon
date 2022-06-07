@component('mail::message')
{{ $user->user->name }},

Thank you from coming to {{ config('app.name') }}'s Coffee Hours! We hope we were able to help you with any questions you had.

We would now like to ask if you could _help us_. If you would answer just a few brief questions about your experience, we will be able to use your help to improve our services and support. We know your time is precious and greatly appreciate any feedback you can provide. Thank you!

[**Yes, I'll help!**](https://purdue.ca1.qualtrics.com/jfe/form/SV_5thH4WXsrXh6Jtc)
@endcomponent