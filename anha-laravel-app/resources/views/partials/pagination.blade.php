@php
    $current = max(1, (int) data_get($meta ?? [], 'current_page', request('page', 1)));
    $last = max(1, (int) data_get($meta ?? [], 'last_page', 1));
    $total = (int) data_get($meta ?? [], 'total', 0);
@endphp

<div class="flex flex-col gap-3 text-sm text-muted-foreground sm:flex-row sm:items-center sm:justify-between">
    <p>Total {{ number_format($total, 0, ',', '.') }} data</p>
    <div class="flex items-center gap-2">
        @php $prev = request()->fullUrlWithQuery(['page' => max(1, $current - 1)]); @endphp
        @php $next = request()->fullUrlWithQuery(['page' => min($last, $current + 1)]); @endphp
        <a class="btn btn-outline {{ $current <= 1 ? 'pointer-events-none opacity-50' : '' }}" href="{{ $prev }}">
            <i data-lucide="chevron-left" class="h-4 w-4"></i>
            Sebelumnya
        </a>
        <span>Halaman {{ $current }} / {{ $last }}</span>
        <a class="btn btn-outline {{ $current >= $last ? 'pointer-events-none opacity-50' : '' }}" href="{{ $next }}">
            Berikutnya
            <i data-lucide="chevron-right" class="h-4 w-4"></i>
        </a>
    </div>
</div>
