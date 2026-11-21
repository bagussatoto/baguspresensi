@if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}" class="admin-pagination-nav">
        <div class="admin-pagination-info">
            @if ($paginator->firstItem())
                Menampilkan <strong>{{ number_format($paginator->firstItem(), 0, ',', '.') }}</strong>
                s/d <strong>{{ number_format($paginator->lastItem(), 0, ',', '.') }}</strong>
                dari total <strong>{{ number_format($paginator->total(), 0, ',', '.') }}</strong> data
            @else
                Menampilkan <strong>{{ number_format($paginator->count(), 0, ',', '.') }}</strong> data
            @endif
        </div>

        <div class="admin-pagination-controls">
            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <span class="admin-page-btn disabled" aria-disabled="true" aria-label="Halaman Sebelumnya">
                    <svg width="16" height="16" viewBox="0 0 20 20" fill="currentColor" style="width: 16px; height: 16px; flex-shrink: 0;">
                        <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                    </svg>
                    <span>Sebelumnya</span>
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="admin-page-btn" aria-label="Halaman Sebelumnya">
                    <svg width="16" height="16" viewBox="0 0 20 20" fill="currentColor" style="width: 16px; height: 16px; flex-shrink: 0;">
                        <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                    </svg>
                    <span>Sebelumnya</span>
                </a>
            @endif

            {{-- Pagination Elements --}}
            <div class="admin-page-numbers">
                @foreach ($elements as $element)
                    {{-- "Three Dots" Separator --}}
                    @if (is_string($element))
                        <span class="admin-page-btn dots" aria-disabled="true">{{ $element }}</span>
                    @endif

                    {{-- Array Of Links --}}
                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <span class="admin-page-btn active" aria-current="page">{{ $page }}</span>
                            @else
                                <a href="{{ $url }}" class="admin-page-btn" aria-label="Ke halaman {{ $page }}">{{ $page }}</a>
                            @endif
                        @endforeach
                    @endif
                @endforeach
            </div>

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="admin-page-btn" aria-label="Halaman Berikutnya">
                    <span>Berikutnya</span>
                    <svg width="16" height="16" viewBox="0 0 20 20" fill="currentColor" style="width: 16px; height: 16px; flex-shrink: 0;">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                    </svg>
                </a>
            @else
                <span class="admin-page-btn disabled" aria-disabled="true" aria-label="Halaman Berikutnya">
                    <span>Berikutnya</span>
                    <svg width="16" height="16" viewBox="0 0 20 20" fill="currentColor" style="width: 16px; height: 16px; flex-shrink: 0;">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                    </svg>
                </span>
            @endif
        </div>
    </nav>
@endif
