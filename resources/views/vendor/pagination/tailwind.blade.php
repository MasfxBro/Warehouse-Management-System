@if ($paginator->hasPages())
<nav class="flex items-center justify-between gap-4 text-xs">

    {{-- Info --}}
    <p class="text-slate-400 shrink-0">
        {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} dari {{ $paginator->total() }} data
    </p>

    {{-- Tombol --}}
    <div class="flex items-center gap-1">

        {{-- Previous --}}
        @if ($paginator->onFirstPage())
            <span class="px-3 py-1.5 rounded-lg border border-slate-200 text-slate-300 cursor-not-allowed select-none">
                <i class="fa-solid fa-chevron-left text-[10px]"></i>
            </span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}"
               class="px-3 py-1.5 rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50 hover:border-slate-300 transition-colors">
                <i class="fa-solid fa-chevron-left text-[10px]"></i>
            </a>
        @endif

        {{-- Nomor halaman --}}
        @foreach ($elements as $element)
            @if (is_string($element))
                <span class="px-2 py-1.5 text-slate-400 select-none">…</span>
            @endif
            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span class="px-3 py-1.5 rounded-lg bg-secondary text-white font-bold border border-secondary">
                            {{ $page }}
                        </span>
                    @else
                        <a href="{{ $url }}"
                           class="px-3 py-1.5 rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50 hover:border-slate-300 transition-colors">
                            {{ $page }}
                        </a>
                    @endif
                @endforeach
            @endif
        @endforeach

        {{-- Next --}}
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}"
               class="px-3 py-1.5 rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50 hover:border-slate-300 transition-colors">
                <i class="fa-solid fa-chevron-right text-[10px]"></i>
            </a>
        @else
            <span class="px-3 py-1.5 rounded-lg border border-slate-200 text-slate-300 cursor-not-allowed select-none">
                <i class="fa-solid fa-chevron-right text-[10px]"></i>
            </span>
        @endif

    </div>
</nav>
@endif
