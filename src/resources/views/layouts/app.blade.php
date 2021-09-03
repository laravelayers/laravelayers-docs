<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    {{-- CSRF Token --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? config('app.name', 'Laravel') }}</title>
    <link rel="icon" href="../../favicon.ico">

    {{-- Styles --}}
    @can('admin.*')

        <link href="{{ mix('/css/app.admin.css') }}" rel="stylesheet">

    @else

        <link href="{{ mix('/css/app.css') }}" rel="stylesheet">

    @endcan

    @stack('head')

    {{-- Scripts --}}

    <script>
        window.Laravel = {!! json_encode([
            'csrfToken' => csrf_token(),
            'country' => 'RU',
        ]) !!};
    </script>
</head>
<body>
    <div class="docs {{ !empty($class) ? $class : '' }}" id="app">

        @section('adminMenuBar')

            @empty($clear)

                @include('admin::layouts.menuBar')

            @endempty

        @show

        <div class="off-canvas-content" data-off-canvas-content>

            @section('header')

                @empty($clear)

                    @include('docs::layouts.header')

                @endempty

            @show

            @section('main')

                @component('foundation::layouts.main', ['full' => $full ?? ''])

                    @yield('sidebarLeft')

                    @yield('content')

                    @yield('sidebarRight')

                @endcomponent

            @show

            @section('footer')

                @empty($clear)

                    @empty($simple)

                        @include('foundation::layouts.footer')

                    @endif

                @endempty

            @show

        </div>
    </div>

    {{-- Scripts --}}
    @can('admin.*')

        <script src="{{ mix('/js/app.admin.js') }}"></script>

    @else

        <script src="{{ mix('/js/app.js') }}"></script>

    @endcan

    <script>
        $(document).foundation();
    </script>

    @stack('scripts')

</body>
</html>