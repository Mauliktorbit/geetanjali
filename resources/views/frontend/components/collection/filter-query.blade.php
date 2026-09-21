@props(['filters' => []])

@foreach (($filters['type'] ?? []) as $t)
    <input type="hidden" name="type[]" value="{{ $t }}">
@endforeach
@foreach (($filters['metal'] ?? []) as $m)
    <input type="hidden" name="metal[]" value="{{ $m }}">
@endforeach
@foreach (($filters['stone'] ?? []) as $s)
    <input type="hidden" name="stone[]" value="{{ $s }}">
@endforeach
<input type="hidden" name="min_price" value="{{ $filters['min_price'] ?? '' }}">
<input type="hidden" name="max_price" value="{{ $filters['max_price'] ?? '' }}">
@if (!empty($filters['category']))
    <input type="hidden" name="category" value="{{ $filters['category'] }}">
@endif
<input type="hidden" name="view" value="{{ $filters['view'] ?? 'grid' }}">
