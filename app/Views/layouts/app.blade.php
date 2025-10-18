<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Nexus Framework')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#eef2ff',
                            100: '#e0e7ff',
                            200: '#c7d2fe',
                            300: '#a5b4fc',
                            400: '#818cf8',
                            500: '#6366f1',
                            600: '#4f46e5',
                            700: '#4338ca',
                            800: '#3730a3',
                            900: '#312e81',
                        },
                        secondary: {
                            50: '#fffbeb',
                            100: '#fef3c7',
                            200: '#fde68a',
                            300: '#fcd34d',
                            400: '#fbbf24',
                            500: '#f59e0b',
                            600: '#d97706',
                            700: '#b45309',
                            800: '#92400e',
                            900: '#78350f',
                        },
                    },
                    fontFamily: {
                        sans: ['Inter', 'system-ui', 'sans-serif'],
                    },
                }
            }
        }
    </script>
    @yield('styles')
</head>
<body class="font-sans bg-gradient-to-br from-white via-primary-500 to-secondary-500 min-h-screen">
    <!-- Navbar -->
    <nav class="bg-white/95 backdrop-blur-lg shadow-lg sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center space-x-2">
                    <span class="text-3xl">⚡</span>
                    <span class="text-2xl font-black bg-gradient-to-r from-primary-600 to-secondary-600 bg-clip-text text-transparent">
                        Nexus
                    </span>
                </div>
                <div class="hidden md:flex items-center space-x-8">
                    <a href="/" class="text-gray-700 hover:text-primary-600 font-medium transition-colors">Home</a>
                    <a href="#features" class="text-gray-700 hover:text-primary-600 font-medium transition-colors">Features</a>
                    <a href="#documentation" class="text-gray-700 hover:text-primary-600 font-medium transition-colors">Docs</a>
                    <a href="https://github.com/nexus-framework/nexus" target="_blank" class="text-gray-700 hover:text-primary-600 font-medium transition-colors">GitHub</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @yield('content')
    </div>

    <!-- Footer -->
    <footer class="text-center py-12 text-white/90">
        <p class="text-lg">&copy; {{ date('Y') }} Nexus Framework. Open Source under MIT License.</p>
        <p class="mt-2">
            Built with <span class="text-red-400">❤️</span> by the
            <a href="https://github.com/nexus-framework" target="_blank" class="font-semibold hover:text-white transition-colors">Nexus Team</a>
        </p>
    </footer>

    @yield('scripts')
</body>
</html>
