<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Masuk - ANHA Elektrik</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <main class="flex min-h-screen flex-col items-center justify-center gap-4 p-4">
        <section class="card w-full max-w-md">
            <div class="space-y-2 border-b border-border p-6">
                <h1 class="flex items-center gap-2 text-2xl font-bold">
                    <i data-lucide="zap" class="h-6 w-6 text-primary"></i>
                    Masuk
                </h1>
                <p class="text-sm text-muted-foreground">Gunakan akun Anda untuk mengakses dashboard.</p>
                @if ($forbidden)
                    <div class="rounded-md border border-destructive/30 bg-destructive/10 px-3 py-2 text-sm text-destructive">
                        Akses ditolak. Hanya akun dengan peran admin yang dapat membuka halaman tersebut.
                    </div>
                @endif
                @include('partials.alerts')
            </div>
            <form method="post" action="{{ route('login.store') }}" class="space-y-4 p-6" data-loading-label="Memproses...">
                @csrf
                <input type="hidden" name="redirect" value="{{ old('redirect', $redirect) }}">
                <div class="space-y-1.5">
                    <label for="email" class="text-sm font-medium">Email</label>
                    <div class="relative">
                        <i data-lucide="mail" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground"></i>
                        <input id="email" name="email" type="email" class="form-input pl-9" placeholder="you@example.com" value="{{ old('email') }}" autofocus>
                    </div>
                </div>
                <div class="space-y-1.5">
                    <label for="password" class="text-sm font-medium">Password</label>
                    <div class="relative">
                        <i data-lucide="lock-keyhole" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground"></i>
                        <input id="password" name="password" type="password" class="form-input pl-9" placeholder="Masukkan password">
                    </div>
                </div>
                <button type="submit" class="btn btn-primary w-full">
                    <i data-lucide="log-in" class="h-4 w-4"></i>
                    Login
                </button>
            </form>
        </section>
    </main>
</body>
</html>
