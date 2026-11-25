@if (session('success'))
    <div class="flex items-start gap-2 rounded-md border border-emerald-500/30 bg-emerald-500/10 px-3 py-2 text-sm text-emerald-700" role="alert">
        <i data-lucide="check-circle-2" class="mt-0.5 h-4 w-4 shrink-0"></i>
        {{ session('success') }}
    </div>
@endif

@if (session('error'))
    <div class="flex items-start gap-2 rounded-md border border-destructive/30 bg-destructive/10 px-3 py-2 text-sm text-destructive" role="alert">
        <i data-lucide="alert-circle" class="mt-0.5 h-4 w-4 shrink-0"></i>
        {{ session('error') }}
    </div>
@endif

@if (($apiError ?? null))
    <div class="flex items-start gap-2 rounded-md border border-destructive/30 bg-destructive/10 px-3 py-2 text-sm text-destructive" role="alert">
        <i data-lucide="alert-circle" class="mt-0.5 h-4 w-4 shrink-0"></i>
        {{ $apiError }}
    </div>
@endif

@if ($errors->any())
    <div class="flex items-start gap-2 rounded-md border border-destructive/30 bg-destructive/10 px-3 py-2 text-sm text-destructive" role="alert">
        <i data-lucide="alert-triangle" class="mt-0.5 h-4 w-4 shrink-0"></i>
        <div>
            <p class="font-medium">Periksa kembali input Anda.</p>
            <ul class="mt-1 list-disc space-y-0.5 pl-4">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
@endif
