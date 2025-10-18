# Validation

Nexus Framework provides a powerful validation system to ensure data integrity and security in your application.

## Table of Contents

- [Basic Usage](#basic-usage)
- [Available Rules](#available-rules)
- [Custom Error Messages](#custom-error-messages)
- [Validation in Controllers](#validation-in-controllers)
- [Creating Validation Classes](#creating-validation-classes)
- [Custom Validation Rules](#custom-validation-rules)

## Basic Usage

### Using the validate() Helper

```php
$validated = validate($data, [
    'name' => 'required|string|max:255',
    'email' => 'required|email|unique:users',
    'age' => 'required|integer|min:18'
]);
```

### Using the Validator Class

```php
use Nexus\Validation\Validator;

$validator = new Validator($data, [
    'name' => 'required|string',
    'email' => 'required|email'
]);

if ($validator->fails()) {
    $errors = $validator->errors();
} else {
    $validated = $validator->validated();
}
```

## Available Rules

### required

Field must be present and not empty:

```php
'name' => 'required'
```

### string

Field must be a string:

```php
'name' => 'string'
```

### email

Field must be a valid email address:

```php
'email' => 'email'
```

### numeric

Field must be numeric:

```php
'price' => 'numeric'
```

### integer

Field must be an integer:

```php
'age' => 'integer'
```

### boolean

Field must be boolean (true, false, 1, 0, "1", "0"):

```php
'is_active' => 'boolean'
```

### array

Field must be an array:

```php
'tags' => 'array'
```

### min

Field must be at least the given value:

```php
'age' => 'min:18'          // Numeric: >= 18
'name' => 'string|min:3'   // String: >= 3 characters
'tags' => 'array|min:2'    // Array: >= 2 items
```

### max

Field must not exceed the given value:

```php
'age' => 'max:100'         // Numeric: <= 100
'name' => 'string|max:255' // String: <= 255 characters
'tags' => 'array|max:10'   // Array: <= 10 items
```

### in

Field must be one of the given values:

```php
'role' => 'in:admin,user,moderator'
'status' => 'in:active,inactive,pending'
```

### url

Field must be a valid URL:

```php
'website' => 'url'
```

### confirmed

Field must match the `{field}_confirmation` field:

```php
'password' => 'required|confirmed' // Requires password_confirmation field
```

### unique

Field must be unique in the database table:

```php
'email' => 'unique:users'           // Check users table
'email' => 'unique:users,email'     // Specify column
'email' => 'unique:users,email,5'   // Ignore ID 5 (for updates)
```

### exists

Field must exist in the database table:

```php
'user_id' => 'exists:users'         // Check users table
'user_id' => 'exists:users,id'      // Specify column
```

### regex

Field must match the given regular expression:

```php
'phone' => 'regex:/^[0-9]{10}$/'
'username' => 'regex:/^[a-zA-Z0-9_]+$/'
```

### alpha

Field must contain only alphabetic characters:

```php
'name' => 'alpha'
```

### alpha_num

Field must contain only alphanumeric characters:

```php
'username' => 'alpha_num'
```

### date

Field must be a valid date:

```php
'birthday' => 'date'
```

## Custom Error Messages

### Global Custom Messages

```php
$validated = validate($data, [
    'name' => 'required|string|max:255',
    'email' => 'required|email'
], [
    'name.required' => 'Please enter your name',
    'name.max' => 'Name cannot exceed 255 characters',
    'email.required' => 'Email address is required',
    'email.email' => 'Please enter a valid email address'
]);
```

### Rule-Level Messages

```php
$validated = validate($data, [
    'age' => 'required|integer|min:18'
], [
    'age.required' => 'Age is required',
    'age.integer' => 'Age must be a number',
    'age.min' => 'You must be at least 18 years old'
]);
```

## Validation in Controllers

### Simple Validation

```php
<?php

namespace App\Controllers;

use Nexus\Http\Request;
use Nexus\Http\Response;
use Nexus\Http\Route\Post;

class UserController
{
    #[Post('/users', 'users.store')]
    public function store(Request $request): Response
    {
        try {
            $validated = validate($request->all(), [
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users',
                'password' => 'required|string|min:8|confirmed',
                'age' => 'required|integer|min:18',
                'role' => 'required|in:user,admin,moderator'
            ]);

            // Create user with validated data
            $user = User::create($validated);

            return Response::json([
                'success' => true,
                'user' => $user
            ], 201);

        } catch (\Nexus\Validation\ValidationException $e) {
            return Response::json([
                'success' => false,
                'errors' => $e->errors()
            ], 422);
        }
    }
}
```

### With Custom Messages

```php
#[Post('/posts', 'posts.store')]
public function store(Request $request): Response
{
    try {
        $validated = validate($request->all(), [
            'title' => 'required|string|max:200',
            'content' => 'required|string',
            'category_id' => 'required|exists:categories,id',
            'tags' => 'array|max:5'
        ], [
            'title.required' => 'Post title is mandatory',
            'title.max' => 'Title is too long (max 200 characters)',
            'content.required' => 'Post content cannot be empty',
            'category_id.required' => 'Please select a category',
            'category_id.exists' => 'Selected category does not exist',
            'tags.max' => 'You can add maximum 5 tags'
        ]);

        $post = Post::create($validated);

        return Response::json(['post' => $post], 201);

    } catch (\Nexus\Validation\ValidationException $e) {
        return Response::json(['errors' => $e->errors()], 422);
    }
}
```

## Creating Validation Classes

Generate a validation class:

```bash
php nexus make:validation UserStoreValidation
```

### Generated Validation Class

`app/Validations/UserStoreValidation.php`:

```php
<?php

namespace App\Validations;

use Nexus\Validation\Validator;

class UserStoreValidation extends Validator
{
    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'age' => 'required|integer|min:18'
        ];
    }

    protected function messages(): array
    {
        return [
            'name.required' => 'Name is required',
            'email.required' => 'Email address is required',
            'email.email' => 'Please provide a valid email',
            'email.unique' => 'This email is already registered',
            'password.required' => 'Password is required',
            'password.min' => 'Password must be at least 8 characters',
            'password.confirmed' => 'Password confirmation does not match',
            'age.required' => 'Age is required',
            'age.min' => 'You must be at least 18 years old'
        ];
    }
}
```

### Using Validation Classes

```php
<?php

namespace App\Controllers;

use App\Validations\UserStoreValidation;
use Nexus\Http\Request;
use Nexus\Http\Response;

class UserController
{
    #[Post('/users', 'users.store')]
    public function store(Request $request): Response
    {
        try {
            $validation = new UserStoreValidation($request->all());
            $validated = $validation->validate();

            $user = User::create($validated);

            return Response::json(['user' => $user], 201);

        } catch (\Nexus\Validation\ValidationException $e) {
            return Response::json(['errors' => $e->errors()], 422);
        }
    }
}
```

## Advanced Examples

### File Upload Validation

```php
$files = $request->files();

validate($files, [
    'avatar' => 'required',
    'avatar.size' => 'max:2048',      // Max 2MB
    'avatar.type' => 'in:image/jpeg,image/png,image/jpg'
], [
    'avatar.required' => 'Please upload an avatar',
    'avatar.size.max' => 'Avatar size must not exceed 2MB',
    'avatar.type.in' => 'Avatar must be JPEG or PNG'
]);
```

### Nested Array Validation

```php
$validated = validate($request->all(), [
    'products' => 'required|array|min:1',
    'products.*.name' => 'required|string',
    'products.*.price' => 'required|numeric|min:0',
    'products.*.quantity' => 'required|integer|min:1'
]);
```

### Conditional Validation

```php
$rules = [
    'name' => 'required|string',
    'email' => 'required|email'
];

if ($request->input('type') === 'company') {
    $rules['company_name'] = 'required|string';
    $rules['tax_id'] = 'required|string';
}

$validated = validate($request->all(), $rules);
```

### Update Validation (Ignore Current Record)

```php
#[Put('/users/{id}', 'users.update')]
public function update(Request $request, int $id): Response
{
    $validated = validate($request->all(), [
        'name' => 'required|string|max:255',
        'email' => "required|email|unique:users,email,{$id}"  // Ignore current user
    ]);

    $user = User::find($id);
    $user->update($validated);

    return Response::json(['user' => $user]);
}
```

## Handling Validation Errors

### In Controllers

```php
try {
    $validated = validate($data, $rules);
} catch (\Nexus\Validation\ValidationException $e) {
    // Get all errors
    $errors = $e->errors();

    // Get specific field errors
    $nameErrors = $e->errors()['name'] ?? [];

    // Return JSON response
    return Response::json([
        'message' => 'Validation failed',
        'errors' => $errors
    ], 422);
}
```

### Error Response Format

```json
{
  "message": "Validation failed",
  "errors": {
    "name": ["The name field is required."],
    "email": [
      "The email field is required.",
      "The email must be a valid email address."
    ],
    "age": ["The age must be at least 18."]
  }
}
```

### In Views (with Sessions)

```blade
@if($errors)
    <div class="alert alert-danger">
        <ul>
            @foreach($errors as $field => $messages)
                @foreach($messages as $message)
                    <li>{{ $message }}</li>
                @endforeach
            @endforeach
        </ul>
    </div>
@endif

<form method="POST">
    <input type="text" name="name" value="{{ old('name') }}">
    @if(isset($errors['name']))
        <span class="error">{{ $errors['name'][0] }}</span>
    @endif
</form>
```

## Best Practices

1. **Validate Early**: Always validate user input before processing
2. **Use Validation Classes**: For complex validation logic
3. **Custom Messages**: Provide clear, user-friendly error messages
4. **Type Hints**: Use appropriate rules for data types
5. **Security**: Never trust user input
6. **Consistent Format**: Maintain consistent error response format
7. **Frontend Validation**: Combine with client-side validation
8. **Sanitize Data**: Clean data before validation when needed

## Next Steps

- Learn about [Controllers](controllers.md)
- Understand [Request & Response](request-response.md)
- Explore [Database](database.md)
- Work with [Models](models.md)
