<div class="header">

    @include('admin::layouts.topBar')

    @include('docs::layouts.topBar')

    @component('docs::layouts.breadcrumbs')

        @slot('right')

            @yield('breadcrumbsRight')

        @endslot

        @section('breadcrumbs')

            {{ $menu->get('path')->render('breadcrumbs.menu') }}

        @show

    @endcomponent

</div>
