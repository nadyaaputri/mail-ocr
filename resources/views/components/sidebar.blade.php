{{--
    MODERN COLLAPSIBLE SIDEBAR v3 - by Gemini
    Perbaikan: Mengubah ikon utama secara dinamis saat menu aktif/terbuka.
--}}
<aside id="layout-menu" class="modern-sidebar">

    <div class="sidebar-top">
        <a href="{{ route('home') }}" class="app-brand-link">
            <span class="app-brand-logo">
                <img src="{{ asset('logo-black.png') }}" alt="{{ config('app.name') }}" width="32">
            </span>
            <span class="app-brand-text menu-text">{{ config('app.name') }}</span>
        </a>
    </div>

    <ul class="sidebar-menu">
        {{-- Dashboard --}}
        <li class="menu-item {{ \Illuminate\Support\Facades\Route::is('home') ? 'active' : '' }}">
            <a href="{{ route('home') }}" class="menu-link" title="Dashboard">
                <i class='bx bx-home-alt'></i>
                <span class="menu-text">{{ __('menu.home') }}</span>
            </a>
        </li>

        {{-- Transaksi --}}
        <li class="menu-item {{ \Illuminate\Support\Facades\Route::is('transaction.*') ? 'active open' : '' }}">
            {{-- LOGIKA BARU: Pilih ikon berdasarkan status aktif --}}
            @php
                $transaksiIcon = \Illuminate\Support\Facades\Route::is('transaction.*') ? 'bx-folder-open' : 'bx-transfer-alt';
            @endphp
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class='bx {{ $transaksiIcon }}'></i>
                <span class="menu-text">{{ __('menu.transaction.menu') }}</span>
            </a>
            <ul class="menu-sub">
                <li class="menu-item {{ \Illuminate\Support\Facades\Route::is('transaction.incoming.*') ? 'active' : '' }}">
                    <a href="{{ route('transaction.incoming.index') }}" class="menu-link">
                        <span class="menu-text">{{ __('menu.transaction.incoming_letter') }}</span>
                    </a>
                </li>
                <li class="menu-item {{ \Illuminate\Support\Facades\Route::is('transaction.outgoing.*') ? 'active' : '' }}">
                    <a href="{{ route('transaction.outgoing.index') }}" class="menu-link">
                        <span class="menu-text">{{ __('menu.transaction.outgoing_letter') }}</span>
                    </a>
                </li>
            </ul>
        </li>

        {{-- Agenda --}}
        <li class="menu-item {{ \Illuminate\Support\Facades\Route::is('agenda.*') ? 'active open' : '' }}">
            {{-- LOGIKA BARU: Pilih ikon berdasarkan status aktif --}}
            @php
                $agendaIcon = \Illuminate\Support\Facades\Route::is('agenda.*') ? 'bx-book-open' : 'bx-calendar-event';
            @endphp
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class='bx {{ $agendaIcon }}'></i>
                <span class="menu-text">{{ __('menu.agenda.menu') }}</span>
            </a>
            <ul class="menu-sub">
                <li class="menu-item {{ \Illuminate\Support\Facades\Route::is('agenda.incoming') ? 'active' : '' }}">
                    <a href="{{ route('agenda.incoming') }}" class="menu-link">
                        <span class="menu-text">{{ __('menu.agenda.incoming_letter') }}</span>
                    </a>
                </li>
                <li class="menu-item {{ \Illuminate\Support\Facades\Route::is('agenda.outgoing') ? 'active' : '' }}">
                    <a href="{{ route('agenda.outgoing') }}" class="menu-link">
                        <span class="menu-text">{{ __('menu.agenda.outgoing_letter') }}</span>
                    </a>
                </li>
            </ul>
        </li>

        {{-- Galeri --}}
        <li class="menu-item {{ \Illuminate\Support\Facades\Route::is('gallery.*') ? 'active open' : '' }}">
            {{-- LOGIKA BARU: Pilih ikon berdasarkan status aktif --}}
            @php
                $galleryIcon = \Illuminate\Support\Facades\Route::is('gallery.*') ? 'bx-collection' : 'bx-images';
            @endphp
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class='bx {{ $galleryIcon }}'></i>
                <span class="menu-text">{{ __('menu.gallery.menu') }}</span>
            </a>
            <ul class="menu-sub">
                 <li class="menu-item {{ \Illuminate\Support\Facades\Route::is('gallery.incoming') ? 'active' : '' }}">
                    <a href="{{ route('gallery.incoming') }}" class="menu-link">
                        <span class="menu-text">{{ __('menu.gallery.incoming_letter') }}</span>
                    </a>
                </li>
                <li class="menu-item {{ \Illuminate\Support\Facades\Route::is('gallery.outgoing') ? 'active' : '' }}">
                    <a href="{{ route('gallery.outgoing') }}" class="menu-link">
                        <span class="menu-text">{{ __('menu.gallery.outgoing_letter') }}</span>
                    </a>
                </li>
            </ul>
        </li>

        {{-- Menu Khusus Admin --}}
        @if(auth()->user()->role == 'admin')
            {{-- Referensi --}}
            <li class="menu-item {{ \Illuminate\Support\Facades\Route::is('reference.*') ? 'active' : '' }}">
                <a href="{{ route('reference.classification.index') }}" class="menu-link" title="Referensi">
                    <i class='bx bx-data'></i>
                    <span class="menu-text">{{ __('menu.reference.menu') }}</span>
                </a>
            </li>
            {{-- Manajemen Pengguna --}}
            <li class="menu-item {{ \Illuminate\Support\Facades\Route::is('user.*') ? 'active' : '' }}">
                <a href="{{ route('user.index') }}" class="menu-link" title="Pengguna">
                    <i class='bx bx-group'></i>
                    <span class="menu-text">{{ __('menu.users') }}</span>
                </a>
            </li>
        @endif
    </ul>

    <div class="sidebar-bottom">
        <a href="javascript:void(0);" class="menu-link sidebar-toggle" title="Lipat/Buka Menu">
            <i class='bx bx-chevrons-left'></i>
        </a>
    </div>

</aside>
