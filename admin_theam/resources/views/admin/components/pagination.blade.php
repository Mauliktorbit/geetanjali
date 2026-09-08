@php
    /**
     * Laravel paginator view — used via: $items->links('admin.components.pagination')
     * Receives $paginator from Illuminate\Pagination.
     */
@endphp
@if ($paginator->hasPages())
    <nav class="pagination" role="navigation" aria-label="Pagination">
        @if ($paginator->onFirstPage())
            <span class="page-item disabled"><span class="page-link">Prev</span></span>
        @else
            <a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev">Prev</a>
        @endif

        @if ($paginator instanceof \Illuminate\Pagination\LengthAwarePaginator)
            @foreach ($paginator->getUrlRange(max($paginator->currentPage() - 2, 1), min($paginator->currentPage() + 2, $paginator->lastPage())) as $page => $url)
                @if ($page == $paginator->currentPage())
                    <span class="page-item active"><span class="page-link">{{ $page }}</span></span>
                @else
                    <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                @endif
            @endforeach
        @else
            <span class="page-item active"><span class="page-link">{{ $paginator->currentPage() }}</span></span>
        @endif

        @if ($paginator->hasMorePages())
            <a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next">Next</a>
        @else
            <span class="page-item disabled"><span class="page-link">Next</span></span>
        @endif
    </nav>
@endif
