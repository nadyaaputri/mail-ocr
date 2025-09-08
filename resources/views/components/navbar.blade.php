{{--
  NAVBAR BARU YANG FOKUS PADA PENCARIAN - Diperbaiki oleh Gemini
--}}
<nav class="layout-navbar container-xxl navbar navbar-expand-xl navbar-detached align-items-center bg-navbar-theme" id="layout-navbar">
    <div class="layout-menu-toggle navbar-nav align-items-xl-center me-3 me-xl-0 d-xl-none">
        <a class="nav-item nav-link px-0 me-xl-4" href="javascript:void(0)">
            <i class="bx bx-menu bx-sm"></i>
        </a>
    </div>

    <div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">
        {{-- ====================================================== --}}
        {{-- BAGIAN YANG DIPERBAIKI: Input dibungkus dengan form --}}
        {{-- ====================================================== --}}
        <form method="GET" action="{{ route('search.global') }}" class="navbar-nav align-items-center">
            <div class="nav-item d-flex align-items-center">
                <i class="bx bx-search fs-4 lh-0"></i>
                <input
                    type="text"
                    name="query"
                    class="form-control border-0 shadow-none"
                    placeholder="Cari surat..."
                    aria-label="Cari surat..."
                    value="{{ request('query') }}" {{-- Menampilkan kembali kata kunci yang dicari --}}
                />
            </div>
        </form>
        {{-- ====================================================== --}}
        {{-- AKHIR BAGIAN YANG DIPERBAIKI --}}
        {{-- ====================================================== --}}

        <ul class="navbar-nav flex-row align-items-center ms-auto">
            <li class="nav-item navbar-dropdown dropdown-user dropdown">
                <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown">
                    <div class="avatar avatar-online">
                        {{-- Ganti dengan path gambar profil Anda jika ada, atau gunakan inisial --}}
                        <img src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name) }}&background=696cff&color=fff" alt class="w-px-40 h-auto rounded-circle" />
                    </div>
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                        <a class="dropdown-item" href="#">
                            <div class="d-flex">
                                <div class="flex-shrink-0 me-3">
                                    <div class="avatar avatar-online">
                                        <img src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name) }}&background=696cff&color=fff" alt class="w-px-40 h-auto rounded-circle" />
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <span class="fw-semibold d-block">{{ auth()->user()->name }}</span>
                                    <small class="text-muted">{{ auth()->user()->role }}</small>
                                </div>
                            </div>
                        </a>
                    </li>
                    <li>
                        <div class="dropdown-divider"></div>
                    </li>
                    <li>
                        <a class="dropdown-item" href="javascript:void(0);" onclick="document.getElementById('logout-form').submit()">
                            <i class="bx bx-power-off me-2"></i>
                            <span class="align-middle">Log Out</span>
                        </a>
                        <form action="{{ route('logout') }}" method="post" id="logout-form">
                            @csrf
                        </form>
                    </li>
                </ul>
            </li>
        </ul>
    </div>
</nav>
