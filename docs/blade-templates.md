# Blade Templates

Blade is Nexus Framework's powerful templating engine inspired by Laravel. It provides clean, elegant syntax while allowing you to use plain PHP code in your views.

## Table of Contents

- [Introduction](#introduction)
- [Displaying Data](#displaying-data)
- [Blade Directives](#blade-directives)
- [Control Structures](#control-structures)
- [Template Inheritance](#template-inheritance)
- [Including Subviews](#including-subviews)
- [Components](#components)
- [Stacks](#stacks)

## Introduction

Blade template files use the `.blade.php` extension and are stored in the `app/Views` directory. They are compiled to plain PHP and cached for performance.

### Creating a Blade View

```bash
# Create directory structure
mkdir -p app/Views/layouts
mkdir -p app/Views/components

# Create a Blade template
touch app/Views/welcome.blade.php
```

### Rendering a Blade View

```php
// In controller
return Response::view('welcome', ['name' => 'John']);

// Or using helper
return view('welcome', ['name' => 'John']);
```

## Displaying Data

### Basic Syntax

```blade
{{-- Echo data (escaped) --}}
<h1>Hello, {{ $name }}</h1>

{{-- Echo unescaped data --}}
<div>{!! $htmlContent !!}</div>

{{-- Default values --}}
<p>{{ $username ?? 'Guest' }}</p>
```

### Examples

```blade
{{-- Variables --}}
<p>Welcome, {{ $user->name }}</p>
<p>Age: {{ $user->age }}</p>

{{-- Arrays --}}
<p>First item: {{ $items[0] }}</p>

{{-- Objects --}}
<p>Title: {{ $post->title }}</p>
<p>Author: {{ $post->author->name }}</p>

{{-- Functions --}}
<p>Today is {{ date('Y-m-d') }}</p>
<p>Uppercase: {{ strtoupper($name) }}</p>

{{-- Expressions --}}
<p>Total: {{ $price * $quantity }}</p>
<p>Discount: {{ $total > 100 ? '10%' : '5%' }}</p>
```

## Blade Directives

### @php

Execute PHP code within your Blade template:

```blade
@php
    $greeting = 'Hello World';
    $items = range(1, 10);
@endphp

<h1>{{ $greeting }}</h1>
```

### Comments

```blade
{{-- This comment will not be in the rendered HTML --}}

<!-- This HTML comment will be visible in source -->
```

## Control Structures

### @if, @elseif, @else

```blade
@if($user->isAdmin())
    <p>Welcome, Admin!</p>
@elseif($user->isModerator())
    <p>Welcome, Moderator!</p>
@else
    <p>Welcome, User!</p>
@endif

{{-- Short syntax --}}
@if($isLoggedIn)
    <p>Welcome back!</p>
@endif
```

### @unless

```blade
@unless($user->isSubscribed())
    <p>Please subscribe to access premium content.</p>
@endunless
```

### @isset, @empty

```blade
@isset($records)
    <p>Records found: {{ count($records) }}</p>
@endisset

@empty($cart)
    <p>Your cart is empty.</p>
@endempty
```

### @auth, @guest

```blade
@auth
    <p>Welcome, {{ auth()->user()->name }}</p>
@endauth

@guest
    <p>Please login to continue.</p>
@endguest
```

### @for Loop

```blade
@for($i = 0; $i < 10; $i++)
    <p>Item {{ $i }}</p>
@endfor
```

### @foreach Loop

```blade
@foreach($users as $user)
    <div class="user">
        <h3>{{ $user->name }}</h3>
        <p>{{ $user->email }}</p>
    </div>
@endforeach

{{-- With else --}}
@forelse($posts as $post)
    <article>
        <h2>{{ $post->title }}</h2>
        <p>{{ $post->excerpt }}</p>
    </article>
@empty
    <p>No posts found.</p>
@endforelse
```

### @while Loop

```blade
@while($count < 10)
    <p>Count: {{ $count++ }}</p>
@endwhile
```

### @switch

```blade
@switch($role)
    @case('admin')
        <p>Administrator Panel</p>
        @break

    @case('moderator')
        <p>Moderator Panel</p>
        @break

    @default
        <p>User Panel</p>
@endswitch
```

### @continue, @break

```blade
@foreach($users as $user)
    @if($user->type === 'admin')
        @continue
    @endif

    <li>{{ $user->name }}</li>

    @if($loop->index >= 10)
        @break
    @endif
@endforeach
```

## Template Inheritance

### Defining a Layout

Create `app/Views/layouts/app.blade.php`:

```blade
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Nexus Framework')</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    @yield('styles')
</head>
<body>
    <nav>
        <ul>
            <li><a href="/">Home</a></li>
            <li><a href="/about">About</a></li>
            <li><a href="/contact">Contact</a></li>
        </ul>
    </nav>

    <main>
        @yield('content')
    </main>

    <footer>
        <p>&copy; {{ date('Y') }} Nexus Framework</p>
    </footer>

    <script src="{{ asset('js/app.js') }}"></script>
    @yield('scripts')
</body>
</html>
```

### Extending a Layout

Create `app/Views/home.blade.php`:

```blade
@extends('layouts.app')

@section('title', 'Home Page')

@section('styles')
    <style>
        .hero { background: linear-gradient(to right, #667eea, #764ba2); }
    </style>
@endsection

@section('content')
    <div class="hero">
        <h1>Welcome to Nexus Framework</h1>
        <p>Build amazing applications with ease.</p>
    </div>

    <div class="features">
        @foreach($features as $feature)
            <div class="feature-card">
                <h3>{{ $feature->title }}</h3>
                <p>{{ $feature->description }}</p>
            </div>
        @endforeach
    </div>
@endsection

@section('scripts')
    <script>
        console.log('Home page loaded');
    </script>
@endsection
```

### @section and @show

Use `@show` instead of `@endsection` to immediately display the section:

```blade
@section('sidebar')
    <div class="sidebar">
        <p>This is the default sidebar.</p>
    </div>
@show
```

Then override it in child views:

```blade
@extends('layouts.app')

@section('sidebar')
    @parent {{-- Include parent content --}}

    <div class="custom-widget">
        <p>Additional sidebar content</p>
    </div>
@endsection
```

## Including Subviews

### @include

```blade
{{-- Include a view --}}
@include('partials.header')

{{-- Include with data --}}
@include('partials.user-card', ['user' => $user])

{{-- Include if exists --}}
@includeIf('partials.sidebar')

{{-- Include when condition is true --}}
@includeWhen($isAdmin, 'partials.admin-panel')

{{-- Include unless condition is true --}}
@includeUnless($isGuest, 'partials.user-menu')
```

### Creating Partials

Create `app/Views/partials/user-card.blade.php`:

```blade
<div class="user-card">
    <img src="{{ $user->avatar }}" alt="{{ $user->name }}">
    <h3>{{ $user->name }}</h3>
    <p>{{ $user->email }}</p>
</div>
```

Use it:

```blade
@foreach($users as $user)
    @include('partials.user-card', ['user' => $user])
@endforeach
```

## Components

### @component

Create `app/Views/components/alert.blade.php`:

```blade
<div class="alert alert-{{ $type ?? 'info' }}">
    <strong>{{ $title }}</strong>
    <p>{{ $slot }}</p>
</div>
```

Use it:

```blade
@component('components.alert', ['type' => 'success', 'title' => 'Success!'])
    Your profile has been updated successfully.
@endcomponent
```

## Stacks

Stacks allow you to push content from child views to named stacks in parent layouts.

### In Layout

```blade
<!DOCTYPE html>
<html>
<head>
    <title>@yield('title')</title>
    @stack('styles')
</head>
<body>
    @yield('content')

    @stack('scripts')
</body>
</html>
```

### In Child View

```blade
@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/datepicker.css') }}">
@endpush

@section('content')
    <input type="date" id="datepicker">
@endsection

@push('scripts')
    <script src="{{ asset('js/datepicker.js') }}"></script>
    <script>
        new DatePicker('#datepicker');
    </script>
@endpush
```

## Escaping & Raw Output

```blade
{{-- Escaped output (safe) --}}
{{ $userInput }}

{{-- Unescaped output (dangerous - use carefully) --}}
{!! $trustedHtmlContent !!}

{{-- Blade syntax as literal text --}}
@{{ This will appear as {{ in HTML }}

{{-- Verbatim (don't parse Blade) --}}
@verbatim
    <div>
        {{ This won't be processed by Blade }}
    </div>
@endverbatim
```

## Loop Variable

The `$loop` variable is available within `@foreach` and `@forelse` loops:

```blade
@foreach($users as $user)
    <div class="user-item">
        {{-- Current iteration (1-based) --}}
        <span>{{ $loop->iteration }}</span>

        {{-- Current iteration (0-based) --}}
        <span>{{ $loop->index }}</span>

        {{-- Remaining iterations --}}
        <span>{{ $loop->remaining }}</span>

        {{-- Total iterations --}}
        <span>{{ $loop->count }}</span>

        {{-- First iteration? --}}
        @if($loop->first)
            <strong>First!</strong>
        @endif

        {{-- Last iteration? --}}
        @if($loop->last)
            <strong>Last!</strong>
        @endif

        {{-- Even iteration? --}}
        @if($loop->even)
            <div class="bg-gray">{{ $user->name }}</div>
        @endif

        {{-- Odd iteration? --}}
        @if($loop->odd)
            <div class="bg-white">{{ $user->name }}</div>
        @endif

        {{-- Nesting depth --}}
        <span>Depth: {{ $loop->depth }}</span>

        {{-- Parent loop (when nested) --}}
        @if($loop->parent)
            <span>Parent index: {{ $loop->parent->index }}</span>
        @endif
    </div>
@endforeach
```

## Complete Example

### Layout: app/Views/layouts/app.blade.php

```blade
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - Nexus App</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    @stack('styles')
</head>
<body>
    @include('partials.navigation')

    <main class="container">
        @if(session('success'))
            @include('partials.alert', ['type' => 'success', 'message' => session('success')])
        @endif

        @yield('content')
    </main>

    @include('partials.footer')

    <script src="{{ asset('js/app.js') }}"></script>
    @stack('scripts')
</body>
</html>
```

### Page: app/Views/posts/index.blade.php

```blade
@extends('layouts.app')

@section('title', 'Blog Posts')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/blog.css') }}">
@endpush

@section('content')
    <h1>Recent Blog Posts</h1>

    @forelse($posts as $post)
        <article class="post @if($loop->first) featured @endif">
            <h2>{{ $post->title }}</h2>
            <p class="meta">
                Posted on {{ date('F j, Y', strtotime($post->created_at)) }}
                by {{ $post->author->name }}
            </p>
            <div class="content">
                {!! $post->excerpt !!}
            </div>
            <a href="/posts/{{ $post->id }}" class="read-more">Read More</a>
        </article>
    @empty
        <p>No posts available.</p>
    @endforelse

    @if(count($posts) > 0)
        <div class="pagination">
            {{-- Pagination links --}}
        </div>
    @endif
@endsection

@push('scripts')
    <script>
        console.log('Blog page loaded');
    </script>
@endpush
```

## Clearing Compiled Views

Clear compiled Blade views:

```bash
php nexus view:clear
```

## Best Practices

1. **Escape User Input**: Always use `{{ }}` for user-generated content
2. **Organize Views**: Use subdirectories for better organization
3. **Reusable Partials**: Extract common UI elements into partials
4. **Layout Inheritance**: Use layouts for consistent page structure
5. **Component-Based**: Create reusable components for complex UI elements
6. **Keep Logic Simple**: Move complex logic to controllers/services
7. **Descriptive Names**: Use clear, descriptive file names
8. **Documentation**: Add comments for complex template logic

## Next Steps

- Learn about [Views](views.md)
- Understand [Assets](assets.md)
- Explore [Controllers](controllers.md)
- Work with [Validation](validation.md)
