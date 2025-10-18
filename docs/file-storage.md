# File Storage

Nexus Framework provides a unified filesystem API for working with files and directories across different storage systems.

## Configuration

Storage configuration is in `config/filesystems.php`. Configure via `.env`:

```env
FILESYSTEM_DISK=local
UPLOAD_MAX_SIZE=10240
```

### Available Disks

- **local** - Store in `storage/app`
- **public** - Store in `storage/app/public` (publicly accessible)
- **uploads** - Store in `public/uploads` (direct public access)

## Setup

Create symbolic link for public storage:

```bash
php nexus storage:link
```

This links `public/storage` → `storage/app/public`.

## Helper Functions

### storage_path()

Get path to storage directory:

```php
$path = storage_path('app/documents/file.pdf');
// /path/to/project/storage/app/documents/file.pdf
```

### public_path()

Get path to public directory:

```php
$path = public_path('images/logo.png');
// /path/to/project/public/images/logo.png
```

### asset()

Generate asset URL:

```php
echo asset('css/app.css');
// http://yourapp.com/css/app.css

echo asset('images/logo.png');
// http://yourapp.com/images/logo.png
```

### storage()

Get storage disk instance:

```php
$disk = storage();           // Default disk
$disk = storage('public');   // Specific disk
```

## Storing Files

### Store String Content

```php
use Nexus\Storage\Storage;

// Store file
Storage::disk('local')->put('file.txt', 'Hello World');

// Store in subdirectory
Storage::disk('local')->put('documents/file.txt', 'Content');
```

### Store File from Path

```php
// Store existing file
Storage::disk('local')->putFile('documents', '/path/to/file.pdf');
```

### Store Uploaded File

```php
// From $_FILES array
Storage::disk('public')->putFile('avatars', $_FILES['avatar']);

// With custom name
Storage::disk('public')->putFileAs('avatars', $_FILES['avatar'], 'user-123.jpg');
```

## Reading Files

### Get File Contents

```php
// Read file
$contents = Storage::disk('local')->get('file.txt');

// Check existence
if (Storage::disk('local')->exists('file.txt')) {
    $contents = Storage::disk('local')->get('file.txt');
}

// Check if missing
if (Storage::disk('local')->missing('file.txt')) {
    // File doesn't exist
}
```

## File Information

### Get Metadata

```php
// File size (bytes)
$size = Storage::disk('local')->size('file.txt');

// Last modified timestamp
$timestamp = Storage::disk('local')->lastModified('file.txt');

// MIME type
$mimeType = Storage::disk('local')->mimeType('image.jpg');

// File extension
$extension = Storage::disk('local')->extension('document.pdf');
```

## File Operations

### Copy Files

```php
// Copy file
Storage::disk('local')->copy('file.txt', 'file-copy.txt');

// Copy between disks
Storage::disk('local')->copy('file.txt', 'backup/file.txt');
```

### Move Files

```php
// Move/rename file
Storage::disk('local')->move('old-name.txt', 'new-name.txt');
```

### Delete Files

```php
// Delete single file
Storage::disk('local')->delete('file.txt');

// Delete multiple files
Storage::disk('local')->delete(['file1.txt', 'file2.txt', 'file3.txt']);
```

## Directory Operations

### List Files

```php
// Get files in directory
$files = Storage::disk('local')->files('documents');

// Get all files recursively
$allFiles = Storage::disk('local')->allFiles('documents');

// Get subdirectories
$directories = Storage::disk('local')->directories('documents');
```

### Create Directories

```php
// Create directory
Storage::disk('local')->makeDirectory('new-folder');

// Create nested directories
Storage::disk('local')->makeDirectory('parent/child/grandchild');
```

### Delete Directories

```php
// Delete directory and contents
Storage::disk('local')->deleteDirectory('old-folder');
```

## URLs

### Generate Public URLs

```php
// Get URL for public disk
$url = Storage::disk('public')->url('avatars/user-123.jpg');
// http://yourapp.com/storage/avatars/user-123.jpg

// Get URL for uploads disk
$url = Storage::disk('uploads')->url('documents/file.pdf');
// http://yourapp.com/uploads/documents/file.pdf
```

## File Upload Example

### HTML Form

```html
<form action="/upload" method="POST" enctype="multipart/form-data">
    <input type="file" name="avatar" accept="image/*">
    <button type="submit">Upload</button>
</form>
```

### Controller

```php
<?php

namespace App\Controllers;

use Nexus\Http\Request;
use Nexus\Http\Response;
use Nexus\Storage\Storage;
use Nexus\Http\Route\Post;

class UploadController
{
    #[Post('/upload', 'upload')]
    public function upload(Request $request): Response
    {
        $files = $request->files();

        if (!isset($files['avatar'])) {
            return Response::json(['error' => 'No file uploaded'], 400);
        }

        $file = $files['avatar'];

        // Validate upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return Response::json(['error' => 'Upload failed'], 400);
        }

        // Validate file size (5MB max)
        if ($file['size'] > 5 * 1024 * 1024) {
            return Response::json(['error' => 'File too large'], 400);
        }

        // Validate file type
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($file['type'], $allowedTypes)) {
            return Response::json(['error' => 'Invalid file type'], 400);
        }

        // Generate unique filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '.' . $extension;

        // Store file
        $path = Storage::disk('public')->putFileAs('avatars', $file, $filename);

        if (!$path) {
            return Response::json(['error' => 'Failed to save file'], 500);
        }

        // Get public URL
        $url = Storage::disk('public')->url($path);

        return Response::json([
            'success' => true,
            'path' => $path,
            'url' => $url
        ]);
    }
}
```

## Complete Upload Example with Validation

```php
<?php

namespace App\Controllers;

use Nexus\Http\Request;
use Nexus\Http\Response;
use Nexus\Storage\Storage;
use Nexus\Http\Route\Post;

class ProfileController
{
    #[Post('/profile/avatar', 'profile.avatar')]
    public function updateAvatar(Request $request): Response
    {
        $files = $request->files();

        // Validate file presence
        if (!isset($files['avatar'])) {
            return Response::json([
                'error' => 'Avatar is required'
            ], 400);
        }

        $file = $files['avatar'];

        // Validate upload
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return Response::json([
                'error' => 'File upload failed'
            ], 400);
        }

        // Validate size (2MB max)
        $maxSize = 2 * 1024 * 1024;
        if ($file['size'] > $maxSize) {
            return Response::json([
                'error' => 'File size must not exceed 2MB'
            ], 400);
        }

        // Validate type
        $allowedMimeTypes = [
            'image/jpeg',
            'image/png',
            'image/gif',
            'image/webp'
        ];

        if (!in_array($file['type'], $allowedMimeTypes)) {
            return Response::json([
                'error' => 'Only JPG, PNG, GIF, and WEBP images are allowed'
            ], 400);
        }

        // Validate extension
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!in_array($extension, $allowedExtensions)) {
            return Response::json([
                'error' => 'Invalid file extension'
            ], 400);
        }

        // Get current user
        $userId = auth()->id();

        // Delete old avatar if exists
        $user = User::find($userId);
        if ($user->avatar_path) {
            Storage::disk('public')->delete($user->avatar_path);
        }

        // Generate secure filename
        $filename = $userId . '_' . time() . '.' . $extension;

        // Store new avatar
        $path = Storage::disk('public')->putFileAs('avatars', $file, $filename);

        if (!$path) {
            return Response::json([
                'error' => 'Failed to save avatar'
            ], 500);
        }

        // Update user record
        $user->update([
            'avatar_path' => $path,
            'avatar_url' => Storage::disk('public')->url($path)
        ]);

        return Response::json([
            'success' => true,
            'message' => 'Avatar updated successfully',
            'avatar_url' => $user->avatar_url
        ]);
    }
}
```

## Using in Views

```php
// In controller
$avatarUrl = Storage::disk('public')->url('avatars/user-123.jpg');

return Response::view('profile', [
    'avatarUrl' => $avatarUrl
]);
```

```blade
<!-- In Blade view -->
<img src="{{ $avatarUrl }}" alt="User Avatar">

<!-- Using asset helper -->
<link rel="stylesheet" href="{{ asset('css/app.css') }}">
<script src="{{ asset('js/app.js') }}"></script>
```

## Custom Disk Configuration

Add custom disks in `config/filesystems.php`:

```php
'disks' => [
    'invoices' => [
        'driver' => 'local',
        'root' => storage_path('app/invoices'),
        'visibility' => 'private',
    ],

    'temp' => [
        'driver' => 'local',
        'root' => storage_path('app/temp'),
    ],

    'backups' => [
        'driver' => 'local',
        'root' => storage_path('backups'),
    ],
],
```

Use custom disks:

```php
Storage::disk('invoices')->put('invoice-123.pdf', $pdfContent);
Storage::disk('temp')->delete('old-file.tmp');
Storage::disk('backups')->putFile('database', 'backup.sql');
```

## Security Best Practices

1. **Validate File Types**: Check MIME type and extension
2. **Limit File Size**: Set reasonable upload limits
3. **Unique Filenames**: Prevent filename collisions
4. **Private Storage**: Use `local` disk for sensitive files
5. **Sanitize Filenames**: Remove special characters
6. **Scan for Malware**: Consider virus scanning
7. **Access Control**: Restrict file access appropriately
8. **HTTPS**: Use HTTPS for file uploads

## Next Steps

- Learn about [Validation](validation.md)
- Understand [Configuration](configuration.md)
- Explore [Controllers](controllers.md)
