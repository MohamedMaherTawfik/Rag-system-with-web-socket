<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'RAG System')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.socket.io/4.7.5/socket.io.min.js"></script>
</head>

<body class="bg-gray-100 text-gray-800 min-h-screen flex flex-col">

    @yield('nav')

    <main class="flex-1 container mx-auto p-4">
        @yield('content')
    </main>

    <footer class="bg-white shadow p-4 text-center">
        &copy; {{ date('Y') }} RAG System. All rights reserved.
    </footer>

    @yield('scripts')
</body>

</html>
