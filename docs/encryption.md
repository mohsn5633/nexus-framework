# Encryption

Nexus Framework provides secure encryption and decryption functionality using industry-standard algorithms to protect sensitive data.

## Table of Contents

- [Introduction](#introduction)
- [Configuration](#configuration)
- [Basic Usage](#basic-usage)
- [Password Hashing](#password-hashing)
- [Examples](#examples)
- [Best Practices](#best-practices)

## Introduction

The Encrypter service uses AES-256-CBC encryption with OpenSSL, providing secure two-way encryption for sensitive data.

### Features

- **AES-256-CBC Encryption**: Industry-standard encryption algorithm
- **Automatic MAC Generation**: Prevents tampering with encrypted data
- **Password Hashing**: Bcrypt hashing for passwords
- **Helper Functions**: Convenient helpers for quick access
- **Serialization Support**: Automatically handles arrays and objects

## Configuration

### Generate Encryption Key

Generate a secure encryption key:

```php
use Nexus\Security\Encrypter;

$key = Encrypter::generateKey(); // Returns 32-byte random key
$keyBase64 = base64_encode($key);
```

### Configure in .env

Add your encryption key to `.env`:

```env
APP_KEY=base64:YOUR_GENERATED_KEY_HERE
CIPHER=AES-256-CBC
```

### Service Provider

Register the Encrypter in a service provider:

```php
use Nexus\Security\Encrypter;

public function register(): void
{
    $this->app->singleton(Encrypter::class, function ($app) {
        $key = base64_decode(substr(env('APP_KEY'), 7)); // Remove 'base64:' prefix
        return new Encrypter($key, env('CIPHER', 'AES-256-CBC'));
    });
}
```

## Basic Usage

### Encrypting Data

```php
use Nexus\Security\Encrypter;

class UserController
{
    public function __construct(
        protected Encrypter $encrypter
    ) {}

    public function storeApiKey(Request $request): Response
    {
        $apiKey = $request->input('api_key');

        // Encrypt the API key
        $encrypted = $this->encrypter->encrypt($apiKey);

        // Store encrypted value in database
        $user->update(['api_key' => $encrypted]);

        return Response::json(['success' => true]);
    }
}
```

### Decrypting Data

```php
public function getApiKey(int $userId): string
{
    $user = User::find($userId);

    // Decrypt the API key
    $apiKey = $this->encrypter->decrypt($user->api_key);

    return $apiKey;
}
```

### Using Helper Functions

```php
// Encrypt
$encrypted = encrypt('sensitive data');
$encrypted = encrypt(['username' => 'john', 'password' => 'secret']);

// Decrypt
$decrypted = decrypt($encrypted);
```

### Encrypt Without Serialization

For string-only encryption (no serialization):

```php
// Using service
$encrypted = $encrypter->encryptString('Hello World');
$decrypted = $encrypter->decryptString($encrypted);

// Using helper
$encrypted = encrypt('Hello World', false);
$decrypted = decrypt($encrypted, false);
```

## Password Hashing

### Hash Passwords

```php
use Nexus\Security\Encrypter;

// Using static method
$hashed = Encrypter::hash('password123');

// Using helper
$hashed = bcrypt('password123');

// With custom cost
$hashed = bcrypt('password123', ['cost' => 12]);
```

### Verify Passwords

```php
$hashed = '$2y$10$...'; // Hashed password from database

if (Encrypter::verify('password123', $hashed)) {
    // Password is correct
} else {
    // Password is incorrect
}
```

### Check if Rehash Needed

```php
$hashed = '$2y$10$...';

if (Encrypter::needsRehash($hashed, ['cost' => 12])) {
    // Password was hashed with different options, rehash it
    $newHashed = bcrypt($plainPassword, ['cost' => 12]);
    // Update database with new hash
}
```

## Examples

### Storing Sensitive User Data

```php
<?php

namespace App\Controllers;

use Nexus\Http\Request;
use Nexus\Http\Response;
use Nexus\Security\Encrypter;
use App\Models\User;

class ProfileController
{
    public function __construct(
        protected Encrypter $encrypter
    ) {}

    public function updatePaymentInfo(Request $request): Response
    {
        $user = auth()->user();

        // Validate input
        $validated = validate($request->all(), [
            'card_number' => 'required|string',
            'expiry' => 'required|string',
            'cvv' => 'required|string',
        ]);

        // Encrypt sensitive payment data
        $user->update([
            'card_number' => $this->encrypter->encrypt($validated['card_number']),
            'card_expiry' => $this->encrypter->encrypt($validated['expiry']),
            // Note: CVV should never be stored
        ]);

        return Response::json([
            'success' => true,
            'message' => 'Payment information updated securely'
        ]);
    }

    public function getPaymentInfo(): Response
    {
        $user = auth()->user();

        // Decrypt payment data
        $cardNumber = $this->encrypter->decrypt($user->card_number);

        // Mask for display
        $masked = '****-****-****-' . substr($cardNumber, -4);

        return Response::json([
            'card_number' => $masked,
            'expiry' => $this->encrypter->decrypt($user->card_expiry)
        ]);
    }
}
```

### API Token Management

```php
<?php

namespace App\Services;

use Nexus\Security\Encrypter;
use App\Models\ApiToken;

class ApiTokenService
{
    public function __construct(
        protected Encrypter $encrypter
    ) {}

    public function generateToken(int $userId): array
    {
        // Generate random token
        $token = bin2hex(random_bytes(32));

        // Store hashed version in database
        $hashedToken = hash('sha256', $token);

        ApiToken::create([
            'user_id' => $userId,
            'token' => $hashedToken,
            'expires_at' => now()->addMonths(6)->toDateTimeString(),
        ]);

        // Return plain token to user (only shown once)
        return [
            'token' => $token,
            'expires_at' => now()->addMonths(6)->toDateTimeString()
        ];
    }

    public function validateToken(string $token): ?int
    {
        $hashedToken = hash('sha256', $token);

        $apiToken = ApiToken::where('token', $hashedToken)
            ->where('expires_at', '>', now()->toDateTimeString())
            ->first();

        return $apiToken ? $apiToken['user_id'] : null;
    }
}
```

### Secure Session Data

```php
<?php

namespace App\Services;

use Nexus\Security\Encrypter;

class SecureSessionService
{
    public function __construct(
        protected Encrypter $encrypter
    ) {}

    public function storeSecure(string $key, mixed $value): void
    {
        $encrypted = $this->encrypter->encrypt($value);
        session()->put("secure.{$key}", $encrypted);
    }

    public function getSecure(string $key): mixed
    {
        $encrypted = session()->get("secure.{$key}");

        if (!$encrypted) {
            return null;
        }

        try {
            return $this->encrypter->decrypt($encrypted);
        } catch (\Exception $e) {
            // Decryption failed, remove corrupt data
            session()->forget("secure.{$key}");
            return null;
        }
    }

    public function forgetSecure(string $key): void
    {
        session()->forget("secure.{$key}");
    }
}
```

### User Registration with Encryption

```php
<?php

namespace App\Controllers;

use Nexus\Http\Request;
use Nexus\Http\Response;
use App\Models\User;

class AuthController
{
    public function register(Request $request): Response
    {
        $validated = validate($request->all(), [
            'name' => 'required|string|min:3',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8|confirmed',
            'phone' => 'required|string',
            'ssn' => 'required|string', // Social Security Number
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => bcrypt($validated['password']), // Hash password
            'phone' => encrypt($validated['phone']), // Encrypt phone
            'ssn' => encrypt($validated['ssn']), // Encrypt SSN
        ]);

        return Response::json([
            'success' => true,
            'user_id' => $user->id
        ], 201);
    }

    public function login(Request $request): Response
    {
        $validated = validate($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (!$user) {
            return Response::json(['error' => 'Invalid credentials'], 401);
        }

        // Verify password
        if (!password_verify($validated['password'], $user->password)) {
            return Response::json(['error' => 'Invalid credentials'], 401);
        }

        // Create session
        session(['user_id' => $user->id]);

        return Response::json([
            'success' => true,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ]
        ]);
    }
}
```

### Encrypted Database Field Accessor

```php
<?php

namespace App\Models;

use Nexus\Database\Model;

class User extends Model
{
    protected array $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'ssn'
    ];

    // Automatically decrypt when accessing
    public function getPhoneAttribute(?string $value): ?string
    {
        return $value ? decrypt($value) : null;
    }

    // Automatically encrypt when setting
    public function setPhoneAttribute(string $value): void
    {
        $this->attributes['phone'] = encrypt($value);
    }

    public function getSsnAttribute(?string $value): ?string
    {
        return $value ? decrypt($value) : null;
    }

    public function setSsnAttribute(string $value): void
    {
        $this->attributes['ssn'] = encrypt($value);
    }
}
```

## Best Practices

1. **Never Hardcode Keys**: Always use environment variables
2. **Rotate Keys Regularly**: Change encryption keys periodically
3. **Use HTTPS**: Always transmit encrypted data over HTTPS
4. **Hash Passwords**: Never encrypt passwords, always hash them
5. **Don't Store CVV**: Never store credit card CVV codes
6. **Backup Keys**: Securely backup encryption keys
7. **Error Handling**: Handle decryption errors gracefully
8. **Minimal Storage**: Only encrypt what's necessary
9. **Key Management**: Use key management systems in production
10. **Audit Access**: Log access to encrypted data

## Security Considerations

### Key Management

- Store keys securely outside version control
- Use different keys for development, staging, and production
- Rotate keys when compromised
- Use key management services (AWS KMS, Azure Key Vault) in production

### Data at Rest

```php
// Encrypt before storing
$encrypted = encrypt($sensitiveData);
DB::table('sensitive_table')->insert(['data' => $encrypted]);

// Decrypt when retrieving
$row = DB::table('sensitive_table')->first();
$decrypted = decrypt($row['data']);
```

### Data in Transit

Always use HTTPS when transmitting sensitive data:

```php
// Force HTTPS in middleware
if (!$request->isSecure()) {
    return Response::redirect('https://' . $request->getHost() . $request->getRequestUri());
}
```

## Error Handling

```php
try {
    $decrypted = decrypt($encrypted);
} catch (\RuntimeException $e) {
    // Handle decryption failure
    if (str_contains($e->getMessage(), 'MAC is invalid')) {
        // Data has been tampered with
        logger()->error('Encryption MAC validation failed', ['data' => $encrypted]);
    }

    // Return safe default or error
    return Response::json(['error' => 'Invalid data'], 400);
}
```

## Troubleshooting

### Decryption Fails

Common causes:
- Wrong encryption key
- Data was tampered with
- Encrypted with different cipher
- Corrupt database data

### Key Rotation

When rotating keys:

```php
// 1. Add new key to environment
// 2. Decrypt with old key, re-encrypt with new key
$oldEncrypter = new Encrypter($oldKey);
$newEncrypter = new Encrypter($newKey);

foreach ($users as $user) {
    $ssn = $oldEncrypter->decrypt($user->ssn);
    $user->update(['ssn' => $newEncrypter->encrypt($ssn)]);
}
```

## Next Steps

- Learn about [Security](security.md)
- Understand [Configuration](configuration.md)
- Explore [Middleware](middleware.md)
