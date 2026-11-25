@php
    $user = session('auth_user', []);
    $groups = [
        'UTAMA' => [
            // ['href' => route('dashboard'), 'label' => 'Dashboard', 'icon' => 'home', 'match' => ['/']],
            // ['href' => route('analytics'), 'label' => 'Analitik', 'icon' => 'bar-chart-3', 'match' => ['utama/*']],
            ['href' => route('dashboard'), 'label' => 'Dashboard', 'icon' => 'home', 'match' => ['/']],
        ],
        'KATALOG' => [
            [
                'href' => route('products.index'),
                'label' => 'Produk',
                'icon' => 'package',
                'match' => ['katalog/produk*'],
            ],
            [
                'href' => route('categories.index'),
                'label' => 'Kategori',
                'icon' => 'layout-grid',
                'match' => ['katalog/kategori*'],
            ],
            [
                'href' => route('units.index'),
                'label' => 'Satuan',
                'icon' => 'scale',
                'match' => ['katalog/satuan*'],
            ],
        ],
        'TRANSAKSI' => [
            [
                'href' => route('orders.index'),
                'label' => 'Pesanan',
                'icon' => 'shopping-bag',
                'match' => ['transaksi/pesanan*'],
            ],
            // ['href' => route('customers.index'), 'label' => 'Pelanggan', 'icon' => 'users', 'match' => ['transaksi/pelanggan*']],
        ],
        'PROGRAM' => [
            [
                'href' => route('banners.index'),
                'label' => 'Banner',
                'icon' => 'image',
                'match' => ['program/banner*'],
            ],
            [
                'href' => route('notifications.index'),
                'label' => 'Notifikasi',
                'icon' => 'megaphone',
                'match' => ['program/notifikasi*'],
            ],
            [
                'href' => route('rewards.index'),
                'label' => 'Program Reward',
                'icon' => 'circle-dollar-sign',
                'match' => ['program/program-reward*'],
            ],
            [
                'href' => route('redemptions.index'),
                'label' => 'Klaim Hadiah',
                'icon' => 'ticket',
                'match' => ['program/klaim-hadiah*'],
            ],
            [
                'href' => route('feedbacks.index'),
                'label' => 'Kritik & Saran',
                'icon' => 'message-square-more',
                'match' => ['program/kritik-saran*'],
            ],
        ],
        'LAPORAN' => [
            [
                'href' => route('reports.show', 'penjualan'),
                'label' => 'Laporan Penjualan',
                'icon' => 'trending-up',
                'match' => ['laporan/penjualan*'],
            ],
            [
                'href' => route('reports.show', 'pelanggan'),
                'label' => 'Laporan Pelanggan',
                'icon' => 'user-round',
                'match' => ['laporan/pelanggan*'],
            ],
            [
                'href' => route('reports.show', 'poin'),
                'label' => 'Laporan Poin',
                'icon' => 'star',
                'match' => ['laporan/poin*'],
            ],
            // ['href' => route('reports.show', 'stok'), 'label' => 'Laporan Stok', 'icon' => 'box', 'match' => ['laporan/stok*']],
        ],
        'SISTEM' => [
            [
                'href' => route('settings.index'),
                'label' => 'Pengaturan',
                'icon' => 'settings',
                'match' => ['sistem/pengaturan*'],
            ],
            [
                'href' => route('rekenings.index'),
                'label' => 'Rekening Toko',
                'icon' => 'credit-card',
                'match' => ['sistem/rekening*'],
            ],
            [
                'href' => route('users.index'),
                'label' => 'Pengguna',
                'icon' => 'users-2',
                'match' => ['sistem/pengguna*'],
            ],
        ],
    ];
    $initials = collect(explode(' ', $user['name'] ?? 'Admin'))
        ->map(fn($part) => mb_substr($part, 0, 1))
        ->take(2)
        ->join('');
@endphp
<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ ($title ?? 'Dashboard') . ' - ANHA Elektrik' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body>
    <div class="flex min-h-dvh w-full">
        <div class="fixed inset-0 z-30 hidden bg-black/30 lg:hidden" data-sidebar-backdrop></div>
        <aside data-sidebar
            class="fixed inset-y-0 left-0 z-40 h-dvh w-72 shrink-0 -translate-x-full border-r border-sidebar-border bg-sidebar text-sidebar-foreground transition-transform lg:sticky lg:top-0 lg:translate-x-0">
            <div class="flex h-dvh min-h-0 flex-col">
                <div class="flex items-center justify-center border-b border-sidebar-border px-4 py-5">
                    <a href="{{ route('dashboard') }}"
                        class="flex items-center gap-2 text-lg font-bold tracking-wider text-primary">
                        <i data-lucide="zap" class="h-5 w-5 fill-primary/20"></i>
                        <span>ANHA ELEKTRIK</span>
                    </a>
                </div>
                <nav class="flex-1 space-y-4 overflow-y-auto px-3 py-4">
                    @foreach ($groups as $group => $items)
                        <div>
                            <p
                                class="px-3 pb-2 text-xs font-semibold uppercase tracking-wide text-sidebar-foreground/60">
                                {{ $group }}</p>
                            <div class="space-y-1">
                                @foreach ($items as $item)
                                    @php $active = collect($item['match'])->contains(fn ($pattern) => request()->is($pattern) || (request()->path() === '/' && $pattern === '/')); @endphp
                                    <a href="{{ $item['href'] }}"
                                        class="flex items-center gap-2 rounded-md px-3 py-2 text-sm font-medium transition {{ $active ? 'bg-sidebar-accent text-sidebar-accent-foreground' : 'hover:bg-sidebar-accent hover:text-sidebar-accent-foreground' }}">
                                        <i data-lucide="{{ $item['icon'] }}" class="h-4 w-4 shrink-0"></i>
                                        <span>{{ $item['label'] }}</span>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </nav>
                <div class="border-t border-sidebar-border p-3">
                    <a href="{{ route('profile.index') }}"
                        class="mb-2 flex items-center gap-2 rounded-md border border-sidebar-border px-2 py-2 hover:bg-sidebar-accent">
                        <span
                            class="flex h-8 w-8 items-center justify-center rounded-full bg-primary/20 text-xs font-semibold text-primary">{{ $initials ?: 'A' }}</span>
                        <span class="min-w-0">
                            <span class="block truncate text-sm font-medium">{{ $user['name'] ?? 'Admin' }}</span>
                            <span
                                class="block truncate text-xs text-sidebar-foreground/70">{{ strtoupper($user['role'] ?? 'admin') }}</span>
                        </span>
                    </a>
                    <form method="post" action="{{ route('logout') }}">
                        @csrf
                        <button class="btn btn-ghost w-full justify-start cursor-pointer" type="submit">
                            <i data-lucide="log-out" class="h-4 w-4"></i>
                            Logout
                        </button>
                    </form>
                </div>
            </div>
        </aside>
        <div class="flex min-w-0 flex-1 flex-col">
            <header
                class="sticky top-0 z-20 flex h-12 shrink-0 items-center gap-2 border-b border-border bg-background/95 px-3 backdrop-blur supports-backdrop-filter:bg-background/80 lg:hidden">
                <button type="button" class="btn btn-ghost h-9 w-9 shrink-0 px-0" data-sidebar-toggle
                    aria-label="Buka menu navigasi">
                    <i data-lucide="menu" class="h-5 w-5"></i>
                </button>
            </header>
            <main class="flex-1 overflow-auto">
                @yield('content')
            </main>
        </div>
    </div>

    <div class="fixed inset-0 z-50 hidden items-center justify-center bg-black/45 p-4" data-confirm-modal
        aria-hidden="true">
        <section class="w-full max-w-md rounded-xl border border-border bg-card p-5 text-card-foreground shadow-xl"
            role="dialog" aria-modal="true" aria-labelledby="confirm-modal-title"
            aria-describedby="confirm-modal-message">
            <div class="flex items-start gap-3">
                <span
                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-destructive/10 text-destructive">
                    <i data-lucide="alert-triangle" class="h-5 w-5"></i>
                </span>
                <div class="min-w-0 flex-1">
                    <h2 id="confirm-modal-title" class="text-lg font-semibold" data-confirm-title>Konfirmasi tindakan
                    </h2>
                    <p id="confirm-modal-message" class="mt-1 text-sm text-muted-foreground" data-confirm-message>
                        Lanjutkan tindakan ini?
                    </p>
                </div>
            </div>
            <div class="mt-5 flex justify-end gap-2">
                <button type="button" class="btn btn-outline" data-confirm-cancel>Batal</button>
                <button type="button" class="btn btn-danger" data-confirm-accept>Ya, lanjutkan</button>
            </div>
        </section>
    </div>
</body>

</html>
