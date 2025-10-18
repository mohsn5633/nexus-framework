# Assets

Assets are static files like CSS, JavaScript, images, and fonts that your application uses. Properly managing assets is crucial for performance and maintainability.

## Table of Contents

- [Asset Location](#asset-location)
- [Linking to Assets](#linking-to-assets)
- [Asset Organization](#asset-organization)
- [Best Practices](#best-practices)

## Asset Location

Assets are stored in the `public` directory, which is the web server's document root.

### Directory Structure

```
public/
├── css/
│   ├── app.css
│   ├── admin.css
│   └── components/
│       ├── buttons.css
│       └── forms.css
├── js/
│   ├── app.js
│   ├── admin.js
│   └── components/
│       ├── modal.js
│       └── dropdown.js
├── images/
│   ├── logo.png
│   ├── hero.jpg
│   └── icons/
│       ├── user.svg
│       └── settings.svg
├── fonts/
│   ├── Inter-Regular.woff2
│   └── Inter-Bold.woff2
└── uploads/
    ├── avatars/
    └── documents/
```

## Linking to Assets

### Using the asset() Helper

The `asset()` helper generates URLs for assets:

```blade
<!-- CSS -->
<link rel="stylesheet" href="{{ asset('css/app.css') }}">
<link rel="stylesheet" href="{{ asset('css/components/buttons.css') }}">

<!-- JavaScript -->
<script src="{{ asset('js/app.js') }}"></script>
<script src="{{ asset('js/components/modal.js') }}"></script>

<!-- Images -->
<img src="{{ asset('images/logo.png') }}" alt="Logo">
<img src="{{ asset('images/icons/user.svg') }}" alt="User">

<!-- Fonts -->
<style>
@font-face {
    font-family: 'Inter';
    src: url('{{ asset('fonts/Inter-Regular.woff2') }}') format('woff2');
}
</style>
```

### Direct Paths

For simple cases, you can use direct paths:

```html
<link rel="stylesheet" href="/css/app.css">
<script src="/js/app.js"></script>
<img src="/images/logo.png" alt="Logo">
```

## Asset Organization

### CSS Structure

```
public/css/
├── app.css              # Main application styles
├── admin.css            # Admin panel styles
├── components/
│   ├── buttons.css      # Button styles
│   ├── forms.css        # Form styles
│   ├── cards.css        # Card styles
│   └── modals.css       # Modal styles
├── layouts/
│   ├── header.css       # Header styles
│   ├── footer.css       # Footer styles
│   └── sidebar.css      # Sidebar styles
└── pages/
    ├── home.css         # Home page styles
    ├── dashboard.css    # Dashboard styles
    └── profile.css      # Profile page styles
```

### JavaScript Structure

```
public/js/
├── app.js               # Main application JS
├── admin.js             # Admin panel JS
├── components/
│   ├── modal.js         # Modal component
│   ├── dropdown.js      # Dropdown component
│   ├── tabs.js          # Tabs component
│   └── tooltip.js       # Tooltip component
├── utils/
│   ├── http.js          # HTTP utilities
│   ├── validation.js    # Validation helpers
│   └── formatting.js    # Formatting helpers
└── pages/
    ├── dashboard.js     # Dashboard logic
    └── profile.js       # Profile page logic
```

### Images Organization

```
public/images/
├── logo.png             # Site logo
├── hero.jpg             # Hero image
├── icons/               # Icon files
│   ├── user.svg
│   ├── settings.svg
│   └── logout.svg
├── backgrounds/         # Background images
│   ├── pattern.png
│   └── gradient.jpg
└── illustrations/       # Illustrations
    ├── welcome.svg
    └── error.svg
```

## Complete Example

### Layout with Assets

```blade
<!-- app/Views/layouts/app.blade.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - My App</title>

    <!-- Favicon -->
    <link rel="icon" href="{{ asset('images/favicon.ico') }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">

    <!-- Global CSS -->
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">

    <!-- Page-specific CSS -->
    @stack('styles')
</head>
<body>
    <header>
        <img src="{{ asset('images/logo.png') }}" alt="Logo" class="logo">
        @include('partials.navigation')
    </header>

    <main>
        @yield('content')
    </main>

    <footer>
        <p>&copy; {{ date('Y') }} My Application</p>
    </footer>

    <!-- Global JS -->
    <script src="{{ asset('js/app.js') }}"></script>

    <!-- Page-specific JS -->
    @stack('scripts')
</body>
</html>
```

### Page with Custom Assets

```blade
<!-- app/Views/dashboard.blade.php -->
@extends('layouts.app')

@section('title', 'Dashboard')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/pages/dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('css/components/charts.css') }}">
@endpush

@section('content')
    <div class="dashboard">
        <h1>Dashboard</h1>

        <div class="stats">
            <div class="stat-card">
                <img src="{{ asset('images/icons/users.svg') }}" alt="Users">
                <span>{{ $userCount }} Users</span>
            </div>

            <div class="stat-card">
                <img src="{{ asset('images/icons/posts.svg') }}" alt="Posts">
                <span>{{ $postCount }} Posts</span>
            </div>
        </div>

        <div class="charts">
            <canvas id="analyticsChart"></canvas>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/components/charts.js') }}"></script>
    <script src="{{ asset('js/pages/dashboard.js') }}"></script>
@endpush
```

## CDN Assets

### Using CDN for Libraries

```blade
<!-- Tailwind CSS -->
<script src="https://cdn.tailwindcss.com"></script>

<!-- Alpine.js -->
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

<!-- Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
```

## Asset Versioning

### Cache Busting

Add version parameter to force cache refresh:

```blade
<link rel="stylesheet" href="{{ asset('css/app.css') }}?v=1.2.3">
<script src="{{ asset('js/app.js') }}?v=1.2.3"></script>
```

### Using Timestamps

```php
// In helper or config
function assetWithVersion($path) {
    $file = public_path($path);
    $version = file_exists($file) ? filemtime($file) : time();
    return asset($path) . '?v=' . $version;
}
```

```blade
<link rel="stylesheet" href="{{ assetWithVersion('css/app.css') }}">
```

## Best Practices

### Performance

1. **Minify Assets**: Minify CSS and JavaScript for production
2. **Combine Files**: Reduce HTTP requests by combining files
3. **Use CDN**: Serve static assets from a CDN
4. **Enable Gzip**: Compress assets with gzip
5. **Optimize Images**: Compress and resize images
6. **Lazy Loading**: Load images on demand
7. **Cache Assets**: Set proper cache headers

### Organization

1. **Logical Structure**: Group related assets together
2. **Naming Convention**: Use clear, consistent names
3. **Versioning**: Version assets for cache control
4. **Documentation**: Document asset dependencies
5. **Separate Concerns**: Keep CSS, JS, and images separate

### Security

1. **Validate Uploads**: Check file types and sizes
2. **Sanitize Filenames**: Remove special characters
3. **Serve from Public**: Only public assets in public/
4. **Set Permissions**: Proper file permissions
5. **HTTPS**: Always use HTTPS in production

## Example CSS File

```css
/* public/css/app.css */
:root {
    --primary-color: #6366f1;
    --secondary-color: #f59e0b;
    --text-color: #1f2937;
    --bg-color: #ffffff;
    --border-radius: 8px;
}

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Inter', system-ui, sans-serif;
    color: var(--text-color);
    background: var(--bg-color);
    line-height: 1.6;
}

.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 1rem;
}

.btn {
    display: inline-block;
    padding: 0.75rem 1.5rem;
    background: var(--primary-color);
    color: white;
    text-decoration: none;
    border-radius: var(--border-radius);
    transition: all 0.3s;
}

.btn:hover {
    opacity: 0.9;
    transform: translateY(-2px);
}
```

## Example JavaScript File

```javascript
// public/js/app.js

// Global app configuration
const App = {
    baseUrl: window.location.origin,
    csrfToken: document.querySelector('meta[name="csrf-token"]')?.content,

    // HTTP request helper
    async request(url, options = {}) {
        const defaults = {
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': this.csrfToken
            }
        };

        const config = { ...defaults, ...options };
        const response = await fetch(url, config);

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        return response.json();
    },

    // Show notification
    notify(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.textContent = message;
        document.body.appendChild(notification);

        setTimeout(() => {
            notification.remove();
        }, 3000);
    }
};

// Initialize app
document.addEventListener('DOMContentLoaded', () => {
    console.log('App initialized');
});

// Export for use in other files
window.App = App;
```

## Next Steps

- Learn about [Views](views.md)
- Understand [Blade Templates](blade-templates.md)
- Explore [File Storage](file-storage.md)
