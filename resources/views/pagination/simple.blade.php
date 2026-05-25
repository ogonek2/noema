@if ($paginator->hasPages())
    <nav class="flex flex-wrap items-center justify-center gap-2" role="navigation" aria-label="Pagination">
        @if ($paginator->onFirstPage())
            <span class="border border-black-brand/10 px-4 py-2 text-[0.68rem] uppercase tracking-[0.14em] text-black-brand/30">←</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}"
                class="border border-black-brand/15 px-4 py-2 text-[0.68rem] uppercase tracking-[0.14em] transition hover:border-black-brand">←</a>
        @endif

        @foreach ($elements as $element)
            @if (is_string($element))
                <span class="px-2 text-black-brand/30">{{ $element }}</span>
            @endif

            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span
                            class="border border-black-brand bg-black-brand px-4 py-2 text-[0.68rem] uppercase tracking-[0.14em] text-white-brand">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}"
                            class="border border-black-brand/15 px-4 py-2 text-[0.68rem] uppercase tracking-[0.14em] transition hover:border-black-brand">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}"
                class="border border-black-brand/15 px-4 py-2 text-[0.68rem] uppercase tracking-[0.14em] transition hover:border-black-brand">→</a>
        @else
            <span class="border border-black-brand/10 px-4 py-2 text-[0.68rem] uppercase tracking-[0.14em] text-black-brand/30">→</span>
        @endif
    </nav>
@endif
