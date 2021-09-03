@include('docs::layouts.titleBar')

<div class="top-bar" id="top-bar">
    <div class="grid-container {{ !empty($full) ? 'fluid' : '' }}">
        <div class="grid-x grid-padding-x grid-padding-y align-middle">
            <div class="medium-shrink cell text-center hide-for-small-only top-bar-left">
                <div>

                    @include('docs::layouts.topBarLeft')

                </div>
            </div>
            <div class="medium-auto cell top-bar-center">
                <div class="search">
                    <form action="{{ route('laravelayers.search.docs', [session('locale', Request::route('lang'))]) }}">
                        <div class="form-search-wrapper">
                            <div class="input-group">
                                <input class="input-group-field" type="search" name="text"
                                       data-form-search data-toggle="search_pane_for_docs" autocomplete="off">
                                <div class="input-group-button">
                                    <button type="submit" class="button">@icon('icon-search')</button>
                                </div>
                            </div>
                            <div class="dropdown-pane" id="search_pane_for_docs"
                                 data-close-on-click="true" data-dropdown data-auto-focus="true">
                            </div>
                        </div>
                    </form>
                </div>
            </div>


            @if (Request::is(['*laravelayers/docs', '*laravelayers/docs/*']))

                <div class="medium-shrink cell top-bar-right" id="top-bar-right">
                    <div>
                        {{ $langs->setSelectedItems(App::getLocale(), 'id')->render('menu') }}
                    </div>
                </div>

            @endif

        </div>
    </div>
</div>
