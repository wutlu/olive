@if ($paginator->hasPages())
    <ul class="pagination d-flex justify-content-end" role="navigation">
        <li aria-current="page" class="mr-auto">
            <a href="#">{{ $paginator->currentPage() }}</a>
        </li>

        {{-- Previous Page Link --}}
        @if ($paginator->onFirstPage())
            <li class="disabled" aria-disabled="true">
            	<a href="#">
                    <i class="material-icons">chevron_left</i>
                </a>
            </li>
        @else
            <li class="waves-effect">
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="@lang('pagination.previous')">
                    <i class="material-icons">chevron_left</i>
                </a>
            </li>
        @endif

        {{-- Next Page Link --}}
        @if ($paginator->hasMorePages())
            <li class="waves-effect">
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="@lang('pagination.previous')">
                    <i class="material-icons">chevron_right</i>
                </a>
            </li>
        @else
            <li class="disabled" aria-disabled="true">
            	<a href="#">
                    <i class="material-icons">chevron_right</i>
                </a>
            </li>
        @endif
    </ul>
@endif
