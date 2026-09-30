<!DOCTYPE html>
<html lang="vi">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
        <title inertia>{{ config('app.name', 'PickMate') }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @inertiaHead
    </head>
    <body class="bg-stone-100 text-stone-900 antialiased">
        @inertia
    </body>
</html>
