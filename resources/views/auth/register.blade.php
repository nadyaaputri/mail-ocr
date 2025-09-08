<!DOCTYPE html>
<html
    lang="id"
    class="light-style customizer-hide"
    dir="ltr"
    data-theme="theme-default"
    data-assets-path="{{ asset('sneat/') }}"
    data-template="vertical-menu-template-free"
>
<head>
    <meta charset="utf-8" />
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0"
    />
    <title>Register | {{ config('app.name') }}</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('logo-black.png') }}" />
    <link rel="stylesheet" href="{{asset('sneat/vendor/css/core.css')}}" class="template-customizer-core-css" />
    <link rel="stylesheet" href="{{asset('sneat/vendor/css/theme-default.css')}}" class="template-customizer-theme-css" />
    <link rel="stylesheet" href="{{asset('sneat/css/demo.css')}}" />
    <link rel="stylesheet" href="{{asset('sneat/vendor/css/pages/page-auth.css')}}" />
</head>

<body>
<div class="container-xxl">
    <div class="authentication-wrapper authentication-basic container-p-y">
        <div class="authentication-inner">
            <div class="card">
                <div class="card-body">
                    <div class="app-brand justify-content-center">
                        <a href="{{ route('home') }}" class="app-brand-link gap-2">
                             <span class="app-brand-logo demo">
                                <img src="{{ asset('logo-black.png') }}" alt="{{ config('app.name') }}" width="35">
                            </span>
                            <span class="app-brand-text text-body fw-bolder">{{ config('app.name') }}</span>
                        </a>
                    </div>
                    <h4 class="mb-2">Selamat Datang! 👋</h4>
                    <p class="mb-4">Silakan buat akun baru Anda</p>

                    <form id="formAuthentication" class="mb-3" action="{{ route('register.perform') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="name" class="form-label">Nama Lengkap</label>
                            <input
                                type="text"
                                class="form-control @error('name') is-invalid @enderror"
                                id="name"
                                name="name"
                                placeholder="Masukkan nama Anda"
                                value="{{ old('name') }}"
                                autofocus
                            />
                            @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="text" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" placeholder="Masukkan email Anda" />
                            @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3 form-password-toggle">
                            <label class="form-label" for="password">Password</label>
                            <div class="input-group input-group-merge">
                                <input
                                    type="password"
                                    id="password"
                                    class="form-control @error('password') is-invalid @enderror"
                                    name="password"
                                    placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
                                    aria-describedby="password"
                                />
                                <span class="input-group-text cursor-pointer"><i class="bx bx-hide"></i></span>
                            </div>
                            @error('password')
                            <div class="text-danger mt-1" style="font-size: 0.875em;">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3 form-password-toggle">
                            <label class="form-label" for="password_confirmation">Konfirmasi Password</label>
                            <div class="input-group input-group-merge">
                                <input
                                    type="password"
                                    id="password_confirmation"
                                    class="form-control"
                                    name="password_confirmation"
                                    placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
                                    aria-describedby="password"
                                />
                                <span class="input-group-text cursor-pointer"><i class="bx bx-hide"></i></span>
                            </div>
                        </div>

                        <button class="btn btn-primary d-grid w-100">
                            Daftar
                        </button>
                    </form>

                    <p class="text-center">
                        <span>Sudah punya akun?</span>
                        <a href="{{ route('login') }}">
                            <span>Login di sini</span>
                        </a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="{{asset('sneat/vendor/js/helpers.js')}}"></script>
<script src="{{asset('sneat/js/config.js')}}"></script>
</body>
</html>
```

---
### Langkah 4: Tambahkan Link di Halaman Login

Terakhir, pastikan pengguna bisa menemukan halaman registrasi Anda. Buka halaman login Anda (kemungkinan di `resources/views/auth/login.blade.php`) dan tambahkan link ke halaman registrasi di bagian bawah.

```html
{{-- Di dalam file login.blade.php --}}

<p class="text-center">
    <span>Pengguna baru?</span>
    <a href="{{ route('register.show') }}">
        <span>Buat sebuah akun</span>
    </a>
</p>
