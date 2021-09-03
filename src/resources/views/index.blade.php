@extends('docs::layouts.app', ['class' => 'docs', 'title' => $menu->get('title')])

@include('docs::layouts.head')

@if (Route::currentRouteName() == 'laravelayers.search.docs')

    @section('breadcrumbs')

        @component('navigation::layouts.breadcrumbs.heading')

            @lang('admin::admin.menu.search')

        @endcomponent

    @endsection

@else

    @section('breadcrumbsRight')

        <div class="hide-for-large" data-responsive-toggle="sidebar_left" data-hide-for="large">
            <a data-toggle="sidebar_left">
                @icon('icon-bars')
            </a>
        </div>

    @endsection

@endif

@section('sidebarLeft')

    @if (!Request::get('checklist', Request::old('checklist')))

        @component('foundation::layouts.sidebarLeft')

            @if (!in_array(Route::currentRouteName(), ['laravelayers.docs.index', 'laravelayers.search.docs']))

                <div class="sticky show-for-large" data-sticky data-anchor="main"
                     data-margin-top="{{ Auth::check() && Auth::user()->can('admin.*') ? 4 : 0 }}"
                     data-sticky-on="large" data-stick-to="top"
                     data-top-anchor="main-top-anchors:top" data-btm-anchor="main-bottom-anchors:bottom">

                    {{ $menu->get('menu')->render('accordion.menu') }}

                </div>
                <div class="hide-for-large" id="docs_menu">

                    {{ $menu->get('menu')->render('accordion.drilldown.menu') }}

                </div>

            @endif

        @endcomponent

    @endif

@endsection

@section('content')

    @component('foundation::layouts.content')

        {!! $content !!}

    @endcomponent

@endsection

@section('footer')

    @component('foundation::layouts.footer')

        <div class="cell text-right">
            <small>&#169; Laravelayers</small>
        </div>

    @endcomponent

@endsection

@include('docs::layouts.scripts')
