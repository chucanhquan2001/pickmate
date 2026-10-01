<!DOCTYPE html>
<html lang="vi">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
        <meta name="theme-color" content="#0f766e">
        <meta name="mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
        <meta name="apple-mobile-web-app-title" content="Pickmate">
        <link rel="manifest" href="/manifest.webmanifest">
        <link rel="apple-touch-icon" href="/icons/apple-touch-icon.png">
        <title inertia>{{ config('app.name', 'PickMate') }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @inertiaHead
    </head>
    <body class="bg-stone-100 text-stone-900 antialiased">
        @inertia
        @production
            <script>
                if ('serviceWorker' in navigator) {
                    const hadController = navigator.serviceWorker.controller !== null;

                    navigator.serviceWorker.register('/sw.js').then(() => {
                        if (hadController) {
                            return;
                        }

                        navigator.serviceWorker.addEventListener('controllerchange', () => {
                            window.location.reload();
                        });
                    });
                }
            </script>
        @endproduction
    </body>
</html>
