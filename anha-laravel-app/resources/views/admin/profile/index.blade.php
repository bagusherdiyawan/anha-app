@extends('layouts.dashboard')

@section('content')
<div class="p-4 md:p-6">
    <div class="mx-auto max-w-3xl space-y-4">
        <div>
            <h2 class="flex items-center gap-2 text-2xl font-bold tracking-tight">
                <i data-lucide="user-circle" class="h-6 w-6 text-primary"></i>
                Profil
            </h2>
            <p class="text-sm text-muted-foreground">Perbarui data akun admin yang sedang digunakan.</p>
        </div>

        @include('partials.alerts')

        <section class="card">
            <form method="post" action="{{ route('profile.update') }}" class="space-y-4 p-6" data-loading-label="Menyimpan...">
                @csrf
                @method('PUT')
                <div class="grid gap-4 md:grid-cols-2">
                    <div class="space-y-1.5">
                        <label class="text-sm font-medium" for="name">Nama</label>
                        <input id="name" name="name" class="form-input" value="{{ old('name', data_get($profile, 'name')) }}">
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-sm font-medium" for="email">Email</label>
                        <input id="email" name="email" type="email" class="form-input" value="{{ old('email', data_get($profile, 'email')) }}">
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-sm font-medium" for="phone">Telepon</label>
                        <input id="phone" name="phone" class="form-input" value="{{ old('phone', data_get($profile, 'phone')) }}">
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-sm font-medium" for="password">Password Baru</label>
                        <input id="password" name="password" type="password" class="form-input" placeholder="Kosongkan jika tidak diganti">
                    </div>
                    <div class="space-y-1.5 md:col-span-2">
                        <label class="text-sm font-medium" for="address">Alamat</label>
                        <textarea id="address" name="address" rows="4" class="form-textarea">{{ old('address', data_get($profile, 'address')) }}</textarea>
                    </div>
                </div>
                <div class="flex justify-end">
                    <button class="btn btn-primary" type="submit">
                        <i data-lucide="save" class="h-4 w-4"></i>
                        Simpan Profil
                    </button>
                </div>
            </form>
        </section>
    </div>
</div>
@endsection
