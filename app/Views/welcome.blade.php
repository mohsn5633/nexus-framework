@extends('layouts.app')

@section('title', 'Nexus Framework - A Modern PHP Framework')

@section('styles')
@endsection

@section('content')
    {{-- Hero Section --}}
    <div class="text-center px-8 py-16 bg-white rounded-3xl shadow-2xl mb-12">
        <div class="inline-block px-4 py-2 bg-gradient-to-r from-primary-500 to-secondary-500 text-white rounded-full text-sm font-semibold mb-4">
            ⚡ Version 1.0.0
        </div>
        <h1 class="text-5xl md:text-6xl font-extrabold bg-gradient-to-r from-primary-600 to-secondary-600 bg-clip-text text-transparent mb-4 leading-tight">
            Nexus Framework
        </h1>
        <p class="text-xl text-gray-600 mb-8 max-w-2xl mx-auto">
            A modern, lightweight PHP framework that combines simplicity with power. Build elegant applications with ease.
        </p>

        <div class="flex gap-4 justify-center flex-wrap mt-8">
            <a href="#quick-start" class="inline-flex items-center gap-2 px-8 py-4 bg-gradient-to-r from-primary-500 to-secondary-500 text-white rounded-xl font-semibold text-base transition-all hover:-translate-y-1 hover:shadow-2xl shadow-lg">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
                Get Started
            </a>
            <a href="https://github.com/mohsn5633/nexus-framework" target="_blank" class="inline-flex items-center gap-2 px-8 py-4 bg-white text-gray-800 border-2 border-primary-500 rounded-xl font-semibold text-base transition-all hover:-translate-y-1 hover:bg-gray-50">
                <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/>
                </svg>
                GitHub
            </a>
            <a href="#documentation" class="inline-flex items-center gap-2 px-8 py-4 bg-white text-gray-800 border-2 border-primary-500 rounded-xl font-semibold text-base transition-all hover:-translate-y-1 hover:bg-gray-50">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
                Documentation
            </a>
        </div>
    </div>

    {{-- Stats Section --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-8 my-12">
        <div class="text-center p-8 bg-white/95 backdrop-blur-lg rounded-2xl">
            <div class="text-5xl font-extrabold bg-gradient-to-r from-primary-600 to-secondary-600 bg-clip-text text-transparent">
                {{ $stats['version'] ?? '1.0' }}
            </div>
            <div class="text-gray-600 font-semibold mt-2">Latest Version</div>
        </div>
        <div class="text-center p-8 bg-white/95 backdrop-blur-lg rounded-2xl">
            <div class="text-5xl font-extrabold bg-gradient-to-r from-primary-600 to-secondary-600 bg-clip-text text-transparent">
                PHP {{ $stats['php_version'] ?? '8.3' }}+
            </div>
            <div class="text-gray-600 font-semibold mt-2">PHP Requirement</div>
        </div>
        <div class="text-center p-8 bg-white/95 backdrop-blur-lg rounded-2xl">
            <div class="text-5xl font-extrabold bg-gradient-to-r from-primary-600 to-secondary-600 bg-clip-text text-transparent">
                {{ $stats['features'] ?? '20+' }}
            </div>
            <div class="text-gray-600 font-semibold mt-2">Features</div>
        </div>
        <div class="text-center p-8 bg-white/95 backdrop-blur-lg rounded-2xl">
            <div class="text-5xl font-extrabold bg-gradient-to-r from-primary-600 to-secondary-600 bg-clip-text text-transparent">
                MIT
            </div>
            <div class="text-gray-600 font-semibold mt-2">Open Source</div>
        </div>
    </div>

    {{-- Features Section --}}
    <div id="features" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 mb-12">
        <div class="bg-white p-8 rounded-2xl shadow-lg transition-all hover:-translate-y-2 hover:shadow-2xl">
            <div class="text-5xl mb-4">🚀</div>
            <h3 class="text-primary-600 text-2xl font-bold mb-3">Lightning Fast</h3>
            <p class="text-gray-600 leading-relaxed">Built with performance in mind. Optimized routing, database queries, and minimal overhead for blazing-fast applications.</p>
        </div>

        <div class="bg-white p-8 rounded-2xl shadow-lg transition-all hover:-translate-y-2 hover:shadow-2xl">
            <div class="text-5xl mb-4">🎨</div>
            <h3 class="text-primary-600 text-2xl font-bold mb-3">Blade Templates</h3>
            <p class="text-gray-600 leading-relaxed">Elegant templating engine with inheritance, components, and directives. Write beautiful, maintainable views.</p>
        </div>

        <div class="bg-white p-8 rounded-2xl shadow-lg transition-all hover:-translate-y-2 hover:shadow-2xl">
            <div class="text-5xl mb-4">🗄️</div>
            <h3 class="text-primary-600 text-2xl font-bold mb-3">Query Builder</h3>
            <p class="text-gray-600 leading-relaxed">Fluent database query builder with support for MySQL, PostgreSQL, and SQLite. Write queries with ease.</p>
        </div>

        <div class="bg-white p-8 rounded-2xl shadow-lg transition-all hover:-translate-y-2 hover:shadow-2xl">
            <div class="text-5xl mb-4">✅</div>
            <h3 class="text-primary-600 text-2xl font-bold mb-3">Validation</h3>
            <p class="text-gray-600 leading-relaxed">Powerful validation system with 20+ built-in rules. Keep your data clean and secure.</p>
        </div>

        <div class="bg-white p-8 rounded-2xl shadow-lg transition-all hover:-translate-y-2 hover:shadow-2xl">
            <div class="text-5xl mb-4">🛣️</div>
            <h3 class="text-primary-600 text-2xl font-bold mb-3">Routing</h3>
            <p class="text-gray-600 leading-relaxed">Attribute-based and file-based routing. RESTful, intuitive, and flexible routing system.</p>
        </div>

        <div class="bg-white p-8 rounded-2xl shadow-lg transition-all hover:-translate-y-2 hover:shadow-2xl">
            <div class="text-5xl mb-4">🔐</div>
            <h3 class="text-primary-600 text-2xl font-bold mb-3">Middleware</h3>
            <p class="text-gray-600 leading-relaxed">Filter HTTP requests entering your application. Authentication, CORS, logging, and more.</p>
        </div>

        <div class="bg-white p-8 rounded-2xl shadow-lg transition-all hover:-translate-y-2 hover:shadow-2xl">
            <div class="text-5xl mb-4">📦</div>
            <h3 class="text-primary-600 text-2xl font-bold mb-3">Service Providers</h3>
            <p class="text-gray-600 leading-relaxed">Organize your application bootstrap logic. Register services and bindings with ease.</p>
        </div>

        <div class="bg-white p-8 rounded-2xl shadow-lg transition-all hover:-translate-y-2 hover:shadow-2xl">
            <div class="text-5xl mb-4">⚙️</div>
            <h3 class="text-primary-600 text-2xl font-bold mb-3">CLI Commands</h3>
            <p class="text-gray-600 leading-relaxed">Powerful command-line interface with code generators. Create controllers, models, and more instantly.</p>
        </div>

        <div class="bg-white p-8 rounded-2xl shadow-lg transition-all hover:-translate-y-2 hover:shadow-2xl">
            <div class="text-5xl mb-4">🐳</div>
            <h3 class="text-primary-600 text-2xl font-bold mb-3">Docker Ready</h3>
            <p class="text-gray-600 leading-relaxed">Comes with Docker configuration out of the box. Deploy anywhere with confidence.</p>
        </div>
    </div>

    {{-- Quick Start Section --}}
    <div id="quick-start" class="bg-white p-12 rounded-3xl shadow-lg mb-12">
        <h2 class="text-4xl font-extrabold text-primary-600 mb-6 text-center">🚀 Quick Start</h2>
        <p class="text-center text-gray-600 mb-8">Get up and running in minutes</p>

        <h3 class="text-gray-800 font-bold text-xl mt-8 mb-3">1. Download Nexus Framework</h3>
        <div class="bg-gray-800 text-gray-200 p-6 rounded-xl overflow-x-auto font-mono my-4">
<code class="text-sm leading-relaxed">git clone https://github.com/mohsn5633/nexus-framework.git
cd nexus
composer install</code>
        </div>

        <h3 class="text-gray-800 font-bold text-xl mt-8 mb-3">2. Configure Environment</h3>
        <div class="bg-gray-800 text-gray-200 p-6 rounded-xl overflow-x-auto font-mono my-4">
<code class="text-sm leading-relaxed">cp .env.example .env
# Edit .env with your database credentials</code>
        </div>

        <h3 class="text-gray-800 font-bold text-xl mt-8 mb-3">3. Start Development Server</h3>
        <div class="bg-gray-800 text-gray-200 p-6 rounded-xl overflow-x-auto font-mono my-4">
<code class="text-sm leading-relaxed">php nexus serve</code>
        </div>

        <h3 class="text-gray-800 font-bold text-xl mt-8 mb-3">4. Create Your First Controller</h3>
        <div class="bg-gray-800 text-gray-200 p-6 rounded-xl overflow-x-auto font-mono my-4">
<code class="text-sm leading-relaxed">php nexus make:controller UserController</code>
        </div>

        <div class="mt-8 p-6 bg-green-50 rounded-xl border-l-4 border-green-500">
            <strong class="text-green-700 font-bold">✓ That's it!</strong>
            <span class="text-gray-700"> Your Nexus application is ready at </span>
            <a href="http://localhost:8000" class="text-primary-600 font-semibold hover:underline">http://localhost:8000</a>
        </div>
    </div>

    {{-- Code Example Section --}}
    <div class="bg-white p-12 rounded-3xl shadow-lg mb-12">
        <h2 class="text-4xl font-extrabold text-primary-600 mb-6 text-center">💻 Code Example</h2>
        <p class="text-center text-gray-600 mb-8">See how easy it is to build with Nexus</p>

        <h3 class="text-gray-800 font-bold text-xl mb-4">Create a RESTful Controller</h3>
        <div class="bg-gray-800 text-gray-200 p-6 rounded-xl overflow-x-auto font-mono my-4">
<code class="text-sm leading-relaxed">&lt;?php

namespace App\Controllers;

use Nexus\Http\{{'{'}}Request, Response, Get, Post{{'}'}};

class UserController
{
    #[Get('/users', 'users.index')]
    public function index(Request $request): Response
    {
        $users = DB::table('users')->get();
        return Response::json($users);
    }

    #[Post('/users', 'users.store')]
    public function store(Request $request): Response
    {
        $validated = validate($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
        ]);

        $userId = DB::table('users')->insert($validated);

        return Response::json([
            'message' => 'User created successfully!',
            'id' => $userId
        ], 201);
    }
}</code>
        </div>
    </div>

    {{-- Documentation Section --}}
    <div id="documentation" class="bg-white p-12 rounded-3xl shadow-lg mb-12">
        <h2 class="text-4xl font-extrabold text-primary-600 mb-6 text-center">📚 Documentation</h2>
        <p class="text-center text-gray-600 mb-8">Explore comprehensive guides and API references</p>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mt-8">
            <a href="https://github.com/mohsn5633/nexus-framework/wiki/Getting-Started" class="block p-6 bg-gray-50 rounded-xl border-2 border-transparent hover:bg-white hover:border-primary-500 hover:-translate-y-1 transition-all" target="_blank">
                <h4 class="text-primary-600 font-bold text-lg mb-2">🚀 Getting Started</h4>
                <p class="text-gray-600 text-sm">Installation, configuration, and first steps</p>
            </a>

            <a href="https://github.com/mohsn5633/nexus-framework/wiki/Routing" class="block p-6 bg-gray-50 rounded-xl border-2 border-transparent hover:bg-white hover:border-primary-500 hover:-translate-y-1 transition-all" target="_blank">
                <h4 class="text-primary-600 font-bold text-lg mb-2">🛣️ Routing</h4>
                <p class="text-gray-600 text-sm">Learn about routes, controllers, and middleware</p>
            </a>

            <a href="https://github.com/mohsn5633/nexus-framework/wiki/Database" class="block p-6 bg-gray-50 rounded-xl border-2 border-transparent hover:bg-white hover:border-primary-500 hover:-translate-y-1 transition-all" target="_blank">
                <h4 class="text-primary-600 font-bold text-lg mb-2">🗄️ Database</h4>
                <p class="text-gray-600 text-sm">Query builder, migrations, and models</p>
            </a>

            <a href="https://github.com/mohsn5633/nexus-framework/wiki/Views" class="block p-6 bg-gray-50 rounded-xl border-2 border-transparent hover:bg-white hover:border-primary-500 hover:-translate-y-1 transition-all" target="_blank">
                <h4 class="text-primary-600 font-bold text-lg mb-2">🎨 Blade Templates</h4>
                <p class="text-gray-600 text-sm">Master the Blade templating engine</p>
            </a>

            <a href="https://github.com/mohsn5633/nexus-framework/wiki/Validation" class="block p-6 bg-gray-50 rounded-xl border-2 border-transparent hover:bg-white hover:border-primary-500 hover:-translate-y-1 transition-all" target="_blank">
                <h4 class="text-primary-600 font-bold text-lg mb-2">✅ Validation</h4>
                <p class="text-gray-600 text-sm">Validate requests with built-in rules</p>
            </a>

            <a href="https://github.com/mohsn5633/nexus-framework/wiki/CLI" class="block p-6 bg-gray-50 rounded-xl border-2 border-transparent hover:bg-white hover:border-primary-500 hover:-translate-y-1 transition-all" target="_blank">
                <h4 class="text-primary-600 font-bold text-lg mb-2">⚡ CLI Commands</h4>
                <p class="text-gray-600 text-sm">Use the command-line interface effectively</p>
            </a>
        </div>
    </div>

    {{-- GitHub Section --}}
    <div class="bg-white p-12 rounded-3xl shadow-lg mb-12">
        <h2 class="text-4xl font-extrabold text-primary-600 mb-6 text-center">🌟 Star on GitHub</h2>
        <p class="text-center text-gray-600 mb-8">Help us grow by starring the repository</p>

        <div class="text-center">
            <a href="https://github.com/mohsn5633/nexus-framework" target="_blank" class="inline-flex items-center gap-3 px-10 py-5 bg-gradient-to-r from-primary-500 to-secondary-500 text-white rounded-xl font-bold text-lg transition-all hover:-translate-y-1 hover:shadow-2xl shadow-lg">
                <svg width="24" height="24" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/>
                </svg>
                Star on GitHub
            </a>
            <p class="mt-4 text-gray-600">
                Join <strong class="text-gray-800">{{ $stats['stars'] ?? '1,000+' }}</strong> developers using Nexus Framework
            </p>
        </div>
    </div>
@endsection
