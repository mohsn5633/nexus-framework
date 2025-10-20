# Pagination

Nexus Framework provides an elegant pagination system that makes it easy to paginate database results with metadata and navigation links.

## Table of Contents

- [Introduction](#introduction)
- [Basic Usage](#basic-usage)
- [Paginator Methods](#paginator-methods)
- [Displaying Results](#displaying-results)
- [Customization](#customization)
- [Examples](#examples)

## Introduction

The pagination system automatically handles calculating offsets, generating page links, and providing metadata about the paginated results.

### Features

- **Automatic Pagination**: Paginate query results with one method call
- **Metadata**: Total count, current page, last page, and more
- **URL Generation**: Automatic next/previous/page URLs
- **HTML Links**: Built-in Bootstrap-compatible pagination HTML
- **JSON API Support**: JSON representation for API responses
- **Customizable**: Adjust items per page and appearance

## Basic Usage

### Paginating Query Results

```php
use Nexus\Database\Database;

class PostController
{
    public function index(Database $db): Response
    {
        // Paginate posts with 15 per page
        $posts = $db->table('posts')
            ->where('status', 'published')
            ->orderBy('created_at', 'DESC')
            ->paginate(15);

        return Response::view('posts.index', ['posts' => $posts]);
    }
}
```

### Paginating Models

```php
use App\Models\Post;

class PostController
{
    public function index(): Response
    {
        // Using model's query builder
        $posts = Post::where('status', 'published')
            ->orderBy('created_at', 'DESC')
            ->paginate(20);

        return Response::view('posts.index', ['posts' => $posts]);
    }
}
```

### Custom Page Number

```php
// Specify page number manually
$page = $_GET['page'] ?? 1;
$posts = $db->table('posts')->paginate(15, $page);

// Page is auto-detected from query string by default
$posts = $db->table('posts')->paginate(15);
```

## Paginator Methods

### Getting Items

```php
// Get items for current page
$items = $paginator->items();

// Iterate over items
foreach ($paginator->items() as $item) {
    echo $item['title'];
}
```

### Pagination Metadata

```php
// Total number of items
$total = $paginator->total();

// Items per page
$perPage = $paginator->perPage();

// Current page number
$currentPage = $paginator->currentPage();

// Last page number
$lastPage = $paginator->lastPage();

// First item number on page
$from = $paginator->firstItem();

// Last item number on page
$to = $paginator->lastItem();
```

### Checking State

```php
// Check if there are more pages
if ($paginator->hasMorePages()) {
    echo "There are more pages";
}

// Check if on first page
if ($paginator->onFirstPage()) {
    echo "This is the first page";
}

// Check if on last page
if ($paginator->onLastPage()) {
    echo "This is the last page";
}
```

### URL Generation

```php
// URL for specific page
$url = $paginator->url(3);

// Next page URL
$nextUrl = $paginator->nextPageUrl();

// Previous page URL
$prevUrl = $paginator->previousPageUrl();
```

## Displaying Results

### In Blade Views

```blade
@foreach($posts->items() as $post)
    <article>
        <h2>{{ $post['title'] }}</h2>
        <p>{{ $post['content'] }}</p>
    </article>
@endforeach

<!-- Pagination links -->
{{ $posts->links() }}

<!-- Show pagination info -->
<p>
    Showing {{ $posts->firstItem() }} to {{ $posts->lastItem() }}
    of {{ $posts->total() }} results
</p>
```

### Pagination Links HTML

```php
// Render pagination links (Bootstrap compatible)
echo $paginator->links();

// Custom number of links on each side
echo $paginator->links(2); // Shows 2 pages on each side of current
```

Output:
```html
<nav>
    <ul class="pagination">
        <li class="page-item"><a class="page-link" href="?page=1">Previous</a></li>
        <li class="page-item"><a class="page-link" href="?page=1">1</a></li>
        <li class="page-item active"><span class="page-link">2</span></li>
        <li class="page-item"><a class="page-link" href="?page=3">3</a></li>
        <li class="page-item"><a class="page-link" href="?page=3">Next</a></li>
    </ul>
</nav>
```

### JSON Response

```php
// For API responses
return Response::json($paginator->toArray());
```

JSON output:
```json
{
    "data": [...],
    "current_page": 2,
    "first_page_url": "/posts?page=1",
    "from": 16,
    "last_page": 10,
    "last_page_url": "/posts?page=10",
    "next_page_url": "/posts?page=3",
    "path": "/posts",
    "per_page": 15,
    "prev_page_url": "/posts?page=1",
    "to": 30,
    "total": 150
}
```

## Customization

### Custom Items Per Page

```php
// 10 items per page
$posts = $db->table('posts')->paginate(10);

// 50 items per page
$posts = $db->table('posts')->paginate(50);

// 100 items per page
$posts = $db->table('posts')->paginate(100);
```

### Custom Path and Query

```php
$posts = $db->table('posts')->paginate(15, null, [
    'path' => '/blog/posts',
    'query' => ['sort' => 'latest', 'filter' => 'published']
]);

// URLs will be: /blog/posts?sort=latest&filter=published&page=2
```

### Simple Pagination

For better performance when you don't need total count:

```php
// Get specific page without total count
$posts = $db->table('posts')
    ->limit(15)
    ->offset(($page - 1) * 15)
    ->get();
```

## Examples

### Blog Post Listing

```php
<?php

namespace App\Controllers;

use Nexus\Http\Request;
use Nexus\Http\Response;
use Nexus\Database\Database;

class BlogController
{
    public function index(Request $request, Database $db): Response
    {
        $perPage = $request->query('per_page', 15);
        $status = $request->query('status', 'published');

        $posts = $db->table('posts')
            ->where('status', $status)
            ->orderBy('created_at', 'DESC')
            ->paginate($perPage);

        return Response::view('blog.index', [
            'posts' => $posts,
            'status' => $status
        ]);
    }
}
```

Blade view:
```blade
<h1>Blog Posts</h1>

@if(count($posts->items()) === 0)
    <p>No posts found.</p>
@else
    @foreach($posts->items() as $post)
        <article>
            <h2>{{ $post['title'] }}</h2>
            <p>{{ $post['excerpt'] }}</p>
            <a href="/posts/{{ $post['id'] }}">Read more</a>
        </article>
    @endforeach

    <!-- Pagination info -->
    <p>
        Showing {{ $posts->firstItem() }} to {{ $posts->lastItem() }}
        of {{ $posts->total() }} posts
    </p>

    <!-- Pagination links -->
    {{ $posts->links() }}
@endif
```

### API Pagination

```php
<?php

namespace App\Controllers\Api;

use Nexus\Http\Request;
use Nexus\Http\Response;
use App\Models\User;

class UserApiController
{
    public function index(Request $request): Response
    {
        $perPage = min((int) $request->query('per_page', 20), 100);

        $users = User::orderBy('created_at', 'DESC')
            ->paginate($perPage);

        return Response::json([
            'success' => true,
            'pagination' => $users->toArray()
        ]);
    }
}
```

### Search Results Pagination

```php
<?php

namespace App\Controllers;

use Nexus\Http\Request;
use Nexus\Http\Response;
use Nexus\Database\Database;

class SearchController
{
    public function search(Request $request, Database $db): Response
    {
        $query = $request->query('q', '');
        $perPage = 10;

        if (empty($query)) {
            return Response::view('search.results', [
                'query' => '',
                'results' => null
            ]);
        }

        $results = $db->table('posts')
            ->where('title', 'LIKE', "%{$query}%")
            ->orWhere('content', 'LIKE', "%{$query}%")
            ->orderBy('created_at', 'DESC')
            ->paginate($perPage, null, [
                'path' => '/search',
                'query' => ['q' => $query]
            ]);

        return Response::view('search.results', [
            'query' => $query,
            'results' => $results
        ]);
    }
}
```

### Admin Dashboard with Filtering

```php
<?php

namespace App\Controllers\Admin;

use Nexus\Http\Request;
use Nexus\Http\Response;
use Nexus\Database\Database;

class OrderController
{
    public function index(Request $request, Database $db): Response
    {
        $status = $request->query('status');
        $perPage = 25;

        $query = $db->table('orders')
            ->orderBy('created_at', 'DESC');

        if ($status) {
            $query->where('status', $status);
        }

        $orders = $query->paginate($perPage, null, [
            'path' => '/admin/orders',
            'query' => array_filter(['status' => $status])
        ]);

        return Response::view('admin.orders.index', [
            'orders' => $orders,
            'currentStatus' => $status,
            'statuses' => ['pending', 'processing', 'completed', 'cancelled']
        ]);
    }
}
```

### Paginated Data Export

```php
<?php

namespace App\Services;

use Nexus\Database\Database;

class ExportService
{
    public function __construct(
        protected Database $db
    ) {}

    public function exportUsers(): void
    {
        $perPage = 1000;
        $page = 1;

        $csv = fopen('users_export.csv', 'w');
        fputcsv($csv, ['ID', 'Name', 'Email', 'Created At']);

        do {
            $users = $this->db->table('users')
                ->orderBy('id', 'ASC')
                ->paginate($perPage, $page);

            foreach ($users->items() as $user) {
                fputcsv($csv, [
                    $user['id'],
                    $user['name'],
                    $user['email'],
                    $user['created_at']
                ]);
            }

            $page++;
        } while ($users->hasMorePages());

        fclose($csv);
    }
}
```

### Cursor-based Pagination (Alternative)

For better performance with large datasets:

```php
<?php

namespace App\Controllers\Api;

use Nexus\Http\Request;
use Nexus\Http\Response;
use Nexus\Database\Database;

class FeedController
{
    public function index(Request $request, Database $db): Response
    {
        $limit = 20;
        $cursor = $request->query('cursor'); // Last item ID

        $query = $db->table('posts')
            ->where('status', 'published')
            ->orderBy('id', 'DESC')
            ->limit($limit + 1);

        if ($cursor) {
            $query->where('id', '<', $cursor);
        }

        $posts = $query->get();

        $hasMore = count($posts) > $limit;
        if ($hasMore) {
            array_pop($posts); // Remove extra item
        }

        $nextCursor = $hasMore ? end($posts)['id'] : null;

        return Response::json([
            'data' => $posts,
            'next_cursor' => $nextCursor,
            'has_more' => $hasMore
        ]);
    }
}
```

## Best Practices

1. **Reasonable Page Size**: Use 10-50 items per page for web, 20-100 for APIs
2. **Index Database Columns**: Index columns used in WHERE and ORDER BY
3. **Cache Counts**: Cache total counts for expensive queries
4. **URL Parameters**: Preserve query parameters in pagination links
5. **Default Sorting**: Always provide a default sort order
6. **Validate Page Number**: Ensure page numbers are valid positive integers
7. **Handle Empty Results**: Show friendly message when no results
8. **Mobile Friendly**: Use responsive pagination designs
9. **API Pagination**: Include metadata in API responses
10. **Performance**: Consider cursor-based pagination for large datasets

## Troubleshooting

### Slow Pagination

If pagination is slow:

1. Add database indexes on filtered/sorted columns
2. Cache the total count
3. Use cursor-based pagination for large datasets
4. Limit maximum page size

### Incorrect Page Count

If page count is wrong:

1. Check WHERE conditions
2. Verify count query matches main query
3. Check for DISTINCT or GROUP BY issues

## Next Steps

- Learn about [Query Builder](query-builder.md)
- Understand [Models](models.md)
- Explore [Views](views.md)
