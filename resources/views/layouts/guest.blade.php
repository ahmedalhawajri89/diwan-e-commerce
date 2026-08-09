<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Furnish') }} - Authentication</title>

        <!-- Google Fonts: Inter -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased text-main bg-soft" style="font-family: 'Inter', sans-serif;">
        <!-- Split Screen Design -->
        <div class="min-h-screen flex flex-col md:flex-row">
            
            <!-- Left Side: Branding / Image -->
            <div class="hidden md:flex md:w-1/2 bg-primary items-center justify-center relative overflow-hidden">
                <!-- Abstract Background Elements -->
                <div class="absolute w-96 h-96 bg-accent rounded-full opacity-20 blur-3xl -top-20 -left-20"></div>
                <div class="absolute w-96 h-96 bg-white rounded-full opacity-10 blur-3xl -bottom-20 -right-20"></div>
                
                <div class="relative z-10 text-center px-12">
                    <a href="/" class="inline-block mb-8">
                        <h1 class="text-4xl font-extrabold text-white tracking-tight">Furnish<span class="text-accent">.</span></h1>
                    </a>
                    <h2 class="text-2xl font-bold text-white mb-4">Elevate your living space.</h2>
                    <p class="text-white/70 text-lg max-w-md mx-auto">
                        Join thousands of members who have discovered the perfect blend of modern design and unparalleled comfort.
                    </p>
                </div>
            </div>

            <!-- Right Side: Auth Form -->
            <div class="w-full md:w-1/2 flex items-center justify-center p-6 sm:p-12">
                <div class="w-full max-w-md">
                    <!-- Mobile Logo -->
                    <div class="md:hidden text-center mb-8">
                        <a href="/">
                            <h1 class="text-3xl font-extrabold text-primary tracking-tight">Furnish<span class="text-accent">.</span></h1>
                        </a>
                    </div>
                    
                    <div class="card-modern p-8 sm:p-10 border-0 shadow-lg">
                        {{ $slot }}
                    </div>
                </div>
            </div>
            
        </div>
    </body>
</html>
