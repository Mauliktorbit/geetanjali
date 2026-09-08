@props(['paginator'])

@if ($paginator instanceof \Illuminate\Contracts\Pagination\Paginator && $paginator->hasPages())
    <div {{ $attributes->merge(['class' => 'd-flex justify-content-center']) }}>
        {{ $paginator->links() }}
    </div>
@endif
