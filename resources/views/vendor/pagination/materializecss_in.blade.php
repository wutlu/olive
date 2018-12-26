@if ($paginator->hasPages())
    <ul class="pagination pagination-in" role="navigation">

        {{-- Pagination Elements --}}
        @foreach ($elements as $element)
            {{-- "Three Dots" Separator --}}
            @if (is_string($element))
            <li class="disabled" aria-disabled="true">
                <a href="#">
                    {{ $element }}
                </a>
            </li>
            @endif

            {{-- Array Of Links --}}
            @if (is_array($element))
                @foreach ($element as $page => $url)
                    <li class="waves-effect">
                        <a href="{{ $paginator->url($page) }}">
                            {{ $page }}
                        </a>
                    </li>
                @endforeach
            @endif
        @endforeach
    </ul>
@endif
