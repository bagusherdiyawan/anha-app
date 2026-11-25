@extends('layouts.dashboard')

@section('content')
@php
    $statusLabels = ['submitted' => 'Baru', 'read' => 'Dibaca', 'replied' => 'Dibalas', 'archived' => 'Diarsipkan'];
    $categoryLabels = ['product' => 'Produk', 'service' => 'Layanan', 'delivery' => 'Pengiriman', 'app' => 'Aplikasi', 'other' => 'Lainnya'];
    $message = data_get($feedback, 'content', data_get($feedback, 'message', '-'));
    $photos = data_get($feedback, 'photo_paths', []);
    $replies = data_get($feedback, 'replies', []);
@endphp
<div class="p-4 md:p-6">
    <div class="mx-auto max-w-4xl space-y-4">
        <a href="{{ route('feedbacks.index') }}" class="btn btn-ghost">
            <i data-lucide="arrow-left" class="h-4 w-4"></i>
            Kembali ke kritik & saran
        </a>
        @include('partials.alerts')

        <section class="card p-5">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <h2 class="flex items-center gap-2 text-xl font-semibold">
                        <i data-lucide="message-square-more" class="h-5 w-5 text-primary"></i>
                        {{ data_get($feedback, 'user.name', 'Anonim') }}
                    </h2>
                    <p class="text-sm text-muted-foreground">{{ data_get($feedback, 'user.email', '-') }}</p>
                    <p class="mt-1 text-xs text-muted-foreground">{{ data_get($feedback, 'created_at', '-') }}</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <span class="badge badge-secondary">{{ $categoryLabels[data_get($feedback, 'category')] ?? data_get($feedback, 'category', '-') }}</span>
                    <span class="badge badge-default">{{ $statusLabels[data_get($feedback, 'status')] ?? data_get($feedback, 'status', '-') }}</span>
                </div>
            </div>
            <div class="mt-5 rounded-lg border border-border bg-background p-4">
                <p class="whitespace-pre-line text-sm leading-relaxed">{{ $message }}</p>
                @if (is_array($photos) && count($photos) > 0)
                    <div class="mt-4 flex flex-wrap gap-2">
                        @foreach ($photos as $photo)
                            <a href="{{ $photo }}" target="_blank" rel="noopener noreferrer">
                                <img src="{{ $photo }}" alt="Lampiran feedback" class="h-20 w-20 rounded-md border border-border object-cover">
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>
        </section>

        <section class="card p-5">
            <h3 class="flex items-center gap-2 font-semibold">
                <i data-lucide="messages-square" class="h-4 w-4 text-primary"></i>
                Riwayat Balasan
            </h3>
            <div class="mt-3 space-y-2">
                @forelse ($replies as $reply)
                    <div class="rounded-lg border border-primary/10 bg-primary/5 p-3">
                        <div class="mb-1 flex items-center gap-2 text-xs">
                            <span class="font-semibold text-primary">{{ data_get($reply, 'admin.name', 'Admin') }}</span>
                            <span class="text-muted-foreground">{{ data_get($reply, 'created_at', '-') }}</span>
                        </div>
                        <p class="text-sm">{{ data_get($reply, 'content', '-') }}</p>
                    </div>
                @empty
                    <p class="text-sm text-muted-foreground">Belum ada balasan.</p>
                @endforelse
            </div>
        </section>

        @if (data_get($feedback, 'status') !== 'archived')
            <section class="card p-5">
                <h3 class="flex items-center gap-2 font-semibold">
                    <i data-lucide="message-square-reply" class="h-4 w-4 text-primary"></i>
                    Kirim Balasan
                </h3>
                <form method="post" action="{{ route('feedbacks.reply', data_get($feedback, 'id')) }}" class="mt-3 space-y-3">
                    @csrf
                    <textarea name="content" rows="4" class="form-textarea" placeholder="Tulis balasan untuk pelanggan..."></textarea>
                    <div class="flex justify-end">
                        <button class="btn btn-primary" type="submit">
                            <i data-lucide="send" class="h-4 w-4"></i>
                            Kirim Balasan
                        </button>
                    </div>
                </form>
            </section>
        @endif
    </div>
</div>
@endsection
