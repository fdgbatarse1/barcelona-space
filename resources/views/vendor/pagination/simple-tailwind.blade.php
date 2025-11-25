@if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}" class="flex justify-between">
        {{-- Previous Page Link --}}
        @if ($paginator->onFirstPage())
            <span class="inline-flex items-center rounded-md border border-gray-300 px-3 py-1.5 text-gray-300 cursor-default">
                {!! __('pagination.previous') !!}
            </span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev"
                class="inline-flex items-center rounded-md border border-gray-500 px-3 py-1.5 text-gray-500 hover:bg-gray-50 hover:border-gray-700">
                {!! __('pagination.previous') !!}
            </a>
        @endif

        {{-- Next Page Link --}}
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next"
                class="inline-flex items-center rounded-md border border-gray-500 px-3 py-1.5 text-gray-500 hover:bg-gray-50 hover:border-gray-700">
                {!! __('pagination.next') !!}
            </a>
        @else
            <span class="inline-flex items-center rounded-md border border-gray-300 px-3 py-1.5 text-gray-300 cursor-default">
                {!! __('pagination.next') !!}
            </span>
        @endif
    </nav>
@endif