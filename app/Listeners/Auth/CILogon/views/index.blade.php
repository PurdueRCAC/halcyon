<a href="{{ route('login', ['authenticator' => 'cilogon']) }}" class="btn btn-block btn-secondary btn-cilogon">
    <img src="{{ asset('listeners/auth/cilogon/images/logo.png') }}" height="20" alt="" class="my-0 mr-1" />
    {{ trans('listener.auth.cilogon::cilogon.sign in') }}
</a>