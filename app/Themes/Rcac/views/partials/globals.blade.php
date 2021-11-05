<script>
    if (typeof(Halcyon) === 'undefined') {
        var Halcyon = {};
    }
    Halcyon.config = {
        app: {
            url: "{{ config('app.url') }}",
            debug: {{ config('app.debug') ? 'true' : 'false' }},
            locale: "{{ config('app.locale') }}",
            timezone: "{{ config('app.timezone') }}",
            name: "{{ config('app.name') }}"
        },
        user: {
            id: {{ auth()->user() ? auth()->user()->id : 0 }},
            name: "{{ auth()->user() ? auth()->user()->name : '' }}",
            username: "{{ auth()->user() ? auth()->user()->username : '' }}"
        }
    };
</script>
