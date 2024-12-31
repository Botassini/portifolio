<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="@yield('meta_description', config('app.description'))">
    <meta name="keywords" content="@yield('meta_keywords', config('app.keywords'))">

    <title>{{ config('app.name') }} @yield('title')</title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('images/icon.png') }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap">
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">

    <!-- Styles -->
    @vite(['resources/css/app.css'])
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    @stack('css')
</head>

<body class="font-sans antialiased">
<div id="app">

    <!-- Page Heading -->
    {{--        @hasSection('header')--}}
    {{--            <header class="bg-white shadow">--}}
    {{--                <div class="max-w-7xl mx-auto">--}}
    {{--                    @yield('header')--}}
    {{--                </div>--}}
    {{--            </header>--}}
    {{--        @endif--}}

    <!-- Main Content -->
    <main>
        @yield('content')
    </main>

    <!-- Footer -->
    @include('layouts.footer')
</div>

<!-- Scripts -->
@vite(['resources/js/app.js'])
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- Error Handling -->
@if($errors->any())
    <script>
        let Swal;
        Swal.fire({
            icon: 'error',
            title: 'Oops...',
            html: `@foreach($errors->all() as $error)
            {{ $error }}<br>
                @endforeach`
        });
    </script>
@endif

<!-- Init Scripts -->
<script>
    $(document).ready(function() {
        // Initialize Select2
        $('.select2').select2();

        // Initialize Tooltips
        $('[data-toggle="tooltip"]').tooltip();

        // Initialize DataTables
        $('.datatable').DataTable();

        // Auto-hide flash messages
        setTimeout(function() {
            $('.alert').fadeOut('slow');
        }, 3000);
    });
</script>

@stack('js')
</body>
</html>
