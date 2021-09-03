<div class="title-bar hide-for-medium" data-responsive-toggle="top-bar" data-hide-for="medium">
    <div class="grid-container {{ !empty($full) ? 'fluid' : '' }}">
        <div class="grid-x grid-padding-x align-middle">
            <div class="shrink cell title-bar-left">

                @include('docs::layouts.topBarLeft')

            </div>
            <div class="auto cell title-bar-right">
                <a data-toggle="top-bar">@icon('icon-bars')</a>
            </div>
        </div>
    </div>
</div>
