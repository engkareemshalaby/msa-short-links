@if ($paginator->hasPages())
    <div class="pagination msa-pagination" role="navigation" aria-label="{{ __('Pagination') }}">
        <p class="pagination-summary">
            {{ __('Showing :from–:to of :total results', [
                'from' => number_format($paginator->firstItem()),
                'to' => number_format($paginator->lastItem()),
                'total' => number_format($paginator->total()),
            ]) }}
        </p>

        <div class="pagination-pages">
            @if ($paginator->onFirstPage())
                <span class="pagination-control disabled" aria-disabled="true" aria-label="{{ __('Previous') }}">‹</span>
            @else
                <a class="pagination-control" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="{{ __('Previous') }}">‹</a>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="pagination-ellipsis">…</span>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="pagination-page active" aria-current="page">{{ $page }}</span>
                        @else
                            <a class="pagination-page" href="{{ $url }}">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <a class="pagination-control" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="{{ __('Next') }}">›</a>
            @else
                <span class="pagination-control disabled" aria-disabled="true" aria-label="{{ __('Next') }}">›</span>
            @endif
        </div>
    </div>
@endif
