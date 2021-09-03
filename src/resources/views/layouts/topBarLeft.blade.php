{!! Route::currentRouteName() !== 'laravelayers.docs.index' ? '<a href="' . route('laravelayers.docs.index', [request()->route('lang')]) . '">'  : '' !!}

<div class="grid-x grid-margin-x align-middle align-center">
    <div class="cell shrink">
        <img style="height: auto;" src="{{ route('laravelayers.docs.images.show', [request()->route('lang'), 'logomark.min.svg']) }}">
    </div>
    <div class="cell shrink">
        <img src="{{ route('laravelayers.docs.images.show', [request()->route('lang'), 'logotype.min.svg']) }}">
    </div>
</div>

{!! Route::currentRouteName() !== 'laravelayers.docs.index' ? '</a>'  : '' !!}
