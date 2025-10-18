# Views

Views contain the HTML of your application and separate your controller/application logic from your presentation logic. Views are stored in the `app/Views` directory.

## Table of Contents

- [Creating Views](#creating-views)
- [Rendering Views](#rendering-views)
- [Passing Data to Views](#passing-data-to-views)
- [View Helpers](#view-helpers)
- [Organizing Views](#organizing-views)

## Creating Views

### Basic View

Create a file in `app/Views/`:

```php
<!-- app/Views/welcome.blade.php -->
<!DOCTYPE html>
<html>
<head>
    <title>Welcome</title>
</head>
<body>
    <h1>Welcome to Nexus Framework</h1>
</body>
</html>
```

### Using Blade Syntax

```blade
<!-- app/Views/users/profile.blade.php -->
<!DOCTYPE html>
<html>
<head>
    <title>User Profile</title>
</head>
<body>
    <h1>{{ $user->name }}</h1>
    <p>Email: {{ $user->email }}</p>
</body>
</html>
```

## Rendering Views

### From Controllers

```php
use Nexus\Http\Response;

// Basic view
return Response::view('welcome');

// View with data
return Response::view('users.profile', [
    'user' => $user
]);

// View with status code
return Response::view('errors.404', [], 404);
```

### Using the view() Helper

```php
// Render view
return view('welcome');

// With data
return view('users.profile', [
    'user' => $user,
    'posts' => $posts
]);
```

### Dot Notation

Use dot notation to reference nested views:

```php
// Renders app/Views/users/profile.blade.php
return view('users.profile');

// Renders app/Views/admin/dashboard/index.blade.php
return view('admin.dashboard.index');
```

## Passing Data to Views

### Array of Data

```php
return view('users.profile', [
    'user' => $user,
    'posts' => $posts,
    'followers' => $followers
]);
```

### In View Template

```blade
<h1>{{ $user->name }}</h1>

<h2>Posts</h2>
@foreach($posts as $post)
    <article>
        <h3>{{ $post->title }}</h3>
        <p>{{ $post->excerpt }}</p>
    </article>
@endforeach

<p>Followers: {{ count($followers) }}</p>
```

### Compact Helper

```php
$user = User::find(1);
$posts = $user->posts();

// Pass multiple variables
return view('users.profile', compact('user', 'posts'));
```

## View Composition

### Sharing Data with All Views

Share data across all views using a service provider:

```php
// In a service provider
public function boot(): void
{
    // Share with all views
    View::share('siteName', config('app.name'));
    View::share('currentYear', date('Y'));
}
```

Access in any view:

```blade
<footer>
    &copy; {{ $currentYear }} {{ $siteName }}
</footer>
```

## View Helpers

### e() - Escape HTML

```blade
<!-- Escape HTML entities -->
<p>{{ e($userInput) }}</p>

<!-- Or use {{ }} which auto-escapes -->
<p>{{ $userInput }}</p>
```

### asset() - Asset URLs

```blade
<!-- Link to assets -->
<link rel="stylesheet" href="{{ asset('css/app.css') }}">
<script src="{{ asset('js/app.js') }}"></script>
<img src="{{ asset('images/logo.png') }}" alt="Logo">
```

### old() - Old Input

```blade
<!-- Repopulate form after validation error -->
<input type="text" name="name" value="{{ old('name') }}">
<input type="email" name="email" value="{{ old('email', $user->email) }}">
```

### csrf_token() - CSRF Token

```blade
<form method="POST" action="/users">
    <input type="hidden" name="_token" value="{{ csrf_token() }}">
    <!-- Form fields -->
</form>
```

## Organizing Views

### Directory Structure

```
app/Views/
├── layouts/
│   ├── app.blade.php        # Main layout
│   └── admin.blade.php      # Admin layout
├── partials/
│   ├── header.blade.php
│   ├── footer.blade.php
│   └── navigation.blade.php
├── components/
│   ├── alert.blade.php
│   ├── card.blade.php
│   └── button.blade.php
├── users/
│   ├── index.blade.php
│   ├── profile.blade.php
│   └── edit.blade.php
├── posts/
│   ├── index.blade.php
│   ├── show.blade.php
│   └── create.blade.php
└── errors/
    ├── 404.blade.php
    ├── 500.blade.php
    └── 503.blade.php
```

### Layouts

Create a master layout:

```blade
<!-- app/Views/layouts/app.blade.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Nexus App')</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    @yield('styles')
</head>
<body>
    @include('partials.navigation')

    <main>
        @yield('content')
    </main>

    @include('partials.footer')

    <script src="{{ asset('js/app.js') }}"></script>
    @yield('scripts')
</body>
</html>
```

Extend the layout:

```blade
<!-- app/Views/users/profile.blade.php -->
@extends('layouts.app')

@section('title', 'User Profile')

@section('content')
    <h1>{{ $user->name }}</h1>
    <p>{{ $user->bio }}</p>
@endsection
```

### Partials

Create reusable partials:

```blade
<!-- app/Views/partials/navigation.blade.php -->
<nav>
    <ul>
        <li><a href="/">Home</a></li>
        <li><a href="/about">About</a></li>
        <li><a href="/contact">Contact</a></li>
    </ul>
</nav>
```

Include partials:

```blade
@include('partials.navigation')
@include('partials.header')
```

### Components

Create reusable components:

```blade
<!-- app/Views/components/alert.blade.php -->
<div class="alert alert-{{ $type ?? 'info' }}">
    <strong>{{ $title }}</strong>
    <p>{{ $slot }}</p>
</div>
```

Use components:

```blade
@component('components.alert', ['type' => 'success', 'title' => 'Success!'])
    Your profile has been updated.
@endcomponent
```

## Complete View Example

### Layout

```blade
<!-- app/Views/layouts/app.blade.php -->
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
    <header>
        @include('partials.navigation')
    </header>

    <main class="container">
        @if(session('success'))
            @include('components.alert', [
                'type' => 'success',
                'message' => session('success')
            ])
        @endif

        @yield('content')
    </main>

    <footer>
        @include('partials.footer')
    </footer>

    <script src="{{ asset('js/app.js') }}"></script>
    @stack('scripts')
</body>
</html>
```

### Page View

```blade
<!-- app/Views/posts/index.blade.php -->
@extends('layouts.app')

@section('title', 'Blog Posts')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/blog.css') }}">
@endpush

@section('content')
    <h1>Recent Blog Posts</h1>

    @forelse($posts as $post)
        <article class="post">
            <h2>{{ $post->title }}</h2>
            <p class="meta">
                By {{ $post->author->name }} on {{ date('F j, Y', strtotime($post->created_at)) }}
            </p>
            <div class="content">
                {{ $post->excerpt }}
            </div>
            <a href="/posts/{{ $post->id }}" class="btn">Read More</a>
        </article>
    @empty
        <p>No posts available.</p>
    @endforelse

    @if($posts)
        <div class="pagination">
            <!-- Pagination links -->
        </div>
    @endif
@endsection

@push('scripts')
    <script src="{{ asset('js/blog.js') }}"></script>
@endpush
```

### Controller

```php
<?php

namespace App\Controllers;

use App\Models\Post;
use Nexus\Http\Request;
use Nexus\Http\Response;
use Nexus\Http\Route\Get;

class PostController
{
    #[Get('/posts', 'posts.index')]
    public function index(Request $request): Response
    {
        $posts = Post::orderBy('created_at', 'DESC')
            ->limit(10)
            ->get();

        return view('posts.index', [
            'posts' => $posts
        ]);
    }

    #[Get('/posts/{id}', 'posts.show')]
    public function show(Request $request, int $id): Response
    {
        $post = Post::find($id);

        if (!$post) {
            return view('errors.404', [], 404);
        }

        return view('posts.show', [
            'post' => $post
        ]);
    }
}
```

## Error Views

### 404 Not Found

```blade
<!-- app/Views/errors/404.blade.php -->
@extends('layouts.app')

@section('title', '404 - Page Not Found')

@section('content')
    <div class="error-page">
        <h1>404</h1>
        <h2>Page Not Found</h2>
        <p>The page you are looking for could not be found.</p>
        <a href="/" class="btn">Go Home</a>
    </div>
@endsection
```

### 500 Server Error

```blade
<!-- app/Views/errors/500.blade.php -->
@extends('layouts.app')

@section('title', '500 - Server Error')

@section('content')
    <div class="error-page">
        <h1>500</h1>
        <h2>Server Error</h2>
        <p>Something went wrong on our end.</p>
        <a href="/" class="btn">Go Home</a>
    </div>
@endsection
```

## Best Practices

1. **Use Layouts**: Create reusable layouts for consistency
2. **Organize by Feature**: Group related views together
3. **Partials for Reuse**: Extract common elements
4. **Escape Output**: Always use {{ }} for user input
5. **Keep Views Simple**: Move logic to controllers/services
6. **Descriptive Names**: Use clear, descriptive file names
7. **Consistent Structure**: Follow a consistent directory structure
8. **Documentation**: Add comments for complex templates

## Next Steps

- Learn about [Blade Templates](blade-templates.md)
- Understand [Assets](assets.md)
- Explore [Controllers](controllers.md)
