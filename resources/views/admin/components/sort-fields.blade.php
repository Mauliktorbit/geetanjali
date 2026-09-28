@if (request()->filled('sort'))
    <input type="hidden" name="sort" value="{{ request('sort') }}">
@endif
@if (request()->filled('direction'))
    <input type="hidden" name="direction" value="{{ request('direction') }}">
@endif
