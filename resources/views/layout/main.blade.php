<!DOCTYPE html>
<html
    lang="id"
    class="light-style layout-menu-fixed"
    dir="ltr"
    data-theme="theme-default"
    data-assets-path="{{ asset('sneat/') }}"
    data-template="vertical-menu-template-free"
>
<head>
    <meta charset="utf-g"/>
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0"
    />

    <title>{{ config('app.name') }}</title>

    <meta name="description" content=""/>

    <link rel="icon" type="image/x-icon" href="{{ asset('logo-black.png') }}"/>

    <link rel="preconnect" href="https://fonts.googleapis.com"/>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
    <link
        href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap"
        rel="stylesheet"
    />

    <link rel="stylesheet" href="{{asset('sneat/vendor/fonts/boxicons.css')}}"/>

    <link rel="stylesheet" class="template-customizer-core-css" href="{{asset('sneat/vendor/css/core.css')}}"/>
    <link rel="stylesheet" class="template-customizer-theme-css"
          href="{{asset('sneat/vendor/css/theme-default.css')}}"/>
    <link rel="stylesheet" href="{{asset('sneat/css/demo.css')}}"/>

    <link rel="stylesheet" href="{{asset('sneat/vendor/libs/perfect-scrollbar/perfect-scrollbar.css')}}"/>
    <link rel="stylesheet" href="{{asset('sneat/vendor/libs/sweetalert2/sweetalert2.min.css')}}"/>

    <link rel="stylesheet" href="{{ asset('assets/css/custom-sidebar.css?v=1.8') }}" />

    @stack('style')

    <script src="{{ asset('sneat/vendor/js/helpers.js') }}"></script>

    <script src="{{ asset('sneat/js/config.js') }}"></script>
</head>

<body>
<div class="layout-wrapper layout-content-navbar">
    <div class="layout-container">
        @include('components.sidebar')
        <div class="layout-page">
            @include('components.navbar')
            <div class="content-wrapper">
                <div class="container-xxl flex-grow-1 container-p-y">
                    @yield('content')
                </div>
                @include('components.footer')
                <div class="content-backdrop fade"></div>
            </div>
        </div>
    </div>

    <div class="layout-overlay layout-menu-toggle"></div>
</div>
<script src="{{ asset('sneat/vendor/libs/jquery/jquery.js')}}"></script>
<script src="{{ asset('sneat/vendor/libs/popper/popper.js')}}"></script>
<script src="{{ asset('sneat/vendor/js/bootstrap.js')}}"></script>
<script src="{{ asset('sneat/vendor/libs/perfect-scrollbar/perfect-scrollbar.js')}}"></script>

<script src="{{ asset('sneat/vendor/js/menu.js')}}"></script>
<script src="{{ asset('sneat/vendor/libs/masonry/masonry.js')}}"></script>
<script src="{{ asset('sneat/vendor/libs/sweetalert2/sweetalert2.all.min.js')}}"></script>

<script src="{{ asset('sneat/js/main.js')}}"></script>

<script>
    // Skrip untuk expand/collapse seluruh sidebar
    document.addEventListener('DOMContentLoaded', function() {
        const sidebar = document.querySelector('.modern-sidebar');
        const toggleButton = document.querySelector('.sidebar-toggle');

        if (sidebar && toggleButton) {
            toggleButton.addEventListener('click', function() {
                sidebar.classList.toggle('expanded');
            });
        }
    });
</script>
<script>
    // Skrip untuk dropdown menu items (accordion)
    document.addEventListener('DOMContentLoaded', function() {
        const sidebar = document.querySelector('.modern-sidebar');
        const menuToggles = document.querySelectorAll('.menu-toggle');

        menuToggles.forEach(toggle => {
            toggle.addEventListener('click', function(e) {
                e.preventDefault();

                // Hanya fungsikan dropdown jika sidebar expanded
                if (sidebar.classList.contains('expanded')) {
                    const parentMenuItem = this.closest('.menu-item');
                    parentMenuItem.classList.toggle('open');
                } else {
                    // Jika sidebar collapsed, expand dulu
                    sidebar.classList.add('expanded');
                }
            });
        });
    });
</script>

<script>
    $(document).on('click', '.btn-delete', function (req) {
        Swal.fire({
            title: '{{ __('menu.general.delete_confirm') }}',
            text: "{{ __('menu.general.delete_warning') }}",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#696cff',
            confirmButtonText: '{{ __('menu.general.delete') }}',
            cancelButtonText: '{{ __('menu.general.cancel') }}'
        }).then((result) => {
            if (result.isConfirmed) {
                $(this).parent('form').submit();
            }
        })
    });
</script>

@stack('script')

@if(session('success'))
    <script>
        Toast.fire({
            icon: 'success',
            title: '{{ session('success') }}'
        })
    </script>
@elseif(session('error'))
    <script>
        Toast.fire({
            icon: 'error',
            title: '{{ session('error') }}'
        })
    </script>
@elseif(session('info'))
    <script>
        Toast.fire({
            icon: 'info',
            title: '{{ session('info') }}'
        })
    </script>
@endif

<script async defer src="https://buttons.github.io/buttons.js"></script>
</body>
</html>
