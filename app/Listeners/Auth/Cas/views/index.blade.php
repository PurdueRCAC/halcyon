<a href="{{ route('login', ['authenticator' => 'cas']) }}" class="btn btn-block btn-secondary btn-cas">
    {{ trans('listener.auth.cas::cas.sign in', ['name' => config('listener.cas.name', 'CAS')]) }}
</a>