# Email (SMTP with TLS/SSL)

Nexus Framework provides a clean, simple email API powered by SMTP with TLS/SSL encryption support, making it easy to send emails securely from your application.

## Table of Contents

- [Introduction](#introduction)
- [Configuration](#configuration)
- [Sending Mail](#sending-mail)
- [Mailable Classes](#mailable-classes)
- [Attachments](#attachments)
- [Queued Mail](#queued-mail)
- [SMTP Providers](#smtp-providers)
- [Examples](#examples)

## Introduction

The mail system provides a simple, clean API for sending emails using SMTP with support for TLS and SSL encryption.

### Features

- **SMTP Support**: Send emails via SMTP servers
- **TLS/SSL Encryption**: Secure email transmission
- **Mailable Classes**: Object-oriented email templates
- **Queue Integration**: Send emails asynchronously
- **Attachments**: Attach files to emails
- **Multiple Recipients**: CC, BCC support
- **HTML & Plain Text**: Support for both formats
- **View Templates**: Use Blade templates for email body

## Configuration

### Environment Variables

Configure email settings in `.env`:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=hello@example.com
MAIL_FROM_NAME="${APP_NAME}"
```

### TLS vs SSL

```env
# TLS (recommended) - Port 587
MAIL_ENCRYPTION=tls
MAIL_PORT=587

# SSL - Port 465
MAIL_ENCRYPTION=ssl
MAIL_PORT=465

# No encryption (not recommended)
MAIL_ENCRYPTION=null
MAIL_PORT=25
```

### Configuration File

Email configuration is in `config/mail.php`:

```php
return [
    'default' => env('MAIL_MAILER', 'smtp'),

    'mailers' => [
        'smtp' => [
            'driver' => 'smtp',
            'host' => env('MAIL_HOST', 'smtp.mailgun.org'),
            'port' => env('MAIL_PORT', 587),
            'encryption' => env('MAIL_ENCRYPTION', 'tls'),
            'username' => env('MAIL_USERNAME'),
            'password' => env('MAIL_PASSWORD'),
            'from' => [
                'address' => env('MAIL_FROM_ADDRESS'),
                'name' => env('MAIL_FROM_NAME'),
            ],
        ],
    ],
];
```

## Sending Mail

### Quick Send

```php
use Nexus\Mail\MailManager;

class UserController
{
    public function sendWelcome(MailManager $mail)
    {
        $mail->to('user@example.com', 'John Doe')
            ->subject('Welcome!')
            ->html('<h1>Welcome to our platform!</h1>')
            ->send();
    }
}
```

### Using Helper

```php
mail()->to('user@example.com')
    ->subject('Hello')
    ->html('<p>Hello World!</p>')
    ->send();
```

### With View Template

```php
mail()->to('user@example.com')
    ->subject('Welcome Email')
    ->view('emails.welcome', ['name' => 'John'])
    ->send();
```

### Multiple Recipients

```php
mail()->to('user1@example.com')
    ->to('user2@example.com')
    ->cc('manager@example.com')
    ->bcc('admin@example.com')
    ->subject('Team Update')
    ->view('emails.update')
    ->send();
```

### Plain Text Email

```php
mail()->to('user@example.com')
    ->subject('Plain Text Email')
    ->text('This is a plain text email')
    ->send();
```

### HTML with Plain Text Alternative

```php
mail()->to('user@example.com')
    ->subject('Newsletter')
    ->html('<h1>Newsletter</h1><p>Content here</p>')
    ->text('Newsletter - Content here')
    ->send();
```

## Mailable Classes

### Creating Mailables

```bash
php nexus make:mail WelcomeEmail
php nexus make:mail OrderConfirmation
php nexus make:mail PasswordReset
```

### Mailable Structure

```php
<?php

namespace App\Mail;

use Nexus\Mail\Mailable;

class WelcomeEmail extends Mailable
{
    public function __construct(
        protected string $userName
    ) {}

    public function build(): void
    {
        $this->subject('Welcome to Our Platform')
            ->view('emails.welcome', [
                'name' => $this->userName
            ]);
    }
}
```

### Sending Mailables

```php
use App\Mail\WelcomeEmail;

// Send immediately
$mailable = new WelcomeEmail('John Doe');
$mailable->to('john@example.com')->send();

// Or use helper
mail(new WelcomeEmail('John Doe'));
```

### View Template

Create `app/Views/emails/welcome.blade.php`:

```blade
<!DOCTYPE html>
<html>
<head>
    <title>Welcome</title>
</head>
<body>
    <h1>Welcome, {{ $name }}!</h1>
    <p>Thank you for joining our platform.</p>
    <p>
        <a href="{{ config('app.url') }}">Get Started</a>
    </p>
</body>
</html>
```

## Attachments

### Attach Files

```php
mail()->to('user@example.com')
    ->subject('Invoice')
    ->view('emails.invoice')
    ->attach('/path/to/invoice.pdf')
    ->attach('/path/to/receipt.pdf', 'Receipt.pdf')
    ->send();
```

### In Mailable Classes

```php
class InvoiceEmail extends Mailable
{
    public function __construct(
        protected string $invoicePath
    ) {}

    public function build(): void
    {
        $this->subject('Your Invoice')
            ->view('emails.invoice')
            ->attach($this->invoicePath, 'invoice.pdf');
    }
}
```

## Queued Mail

### Queue Mailables

```php
use App\Mail\WelcomeEmail;

// Queue for async sending
$mailable = new WelcomeEmail('John Doe');
$mailable->to('john@example.com')->queue();

// Queue with delay (5 minutes)
$mailable->to('john@example.com')->later(300);

// Queue to specific queue
$mailable->to('john@example.com')->queue('emails');
```

### Using Dispatch

```php
// Dispatch mailable to queue
WelcomeEmail::dispatch('John Doe');

// With delay
WelcomeEmail::dispatchAfter(300, 'John Doe');
```

## SMTP Providers

### Gmail

```env
MAIL_MAILER=gmail
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your-email@gmail.com
MAIL_FROM_NAME="Your Name"
```

**Note**: Use App Password, not your account password. Enable 2-factor authentication and generate an App Password in Google Account settings.

### Office 365

```env
MAIL_MAILER=office365
MAIL_HOST=smtp.office365.com
MAIL_PORT=587
MAIL_USERNAME=your-email@outlook.com
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your-email@outlook.com
MAIL_FROM_NAME="Your Name"
```

### Mailgun

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailgun.org
MAIL_PORT=587
MAIL_USERNAME=postmaster@your-domain.com
MAIL_PASSWORD=your-mailgun-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@your-domain.com
MAIL_FROM_NAME="Your App"
```

### SendGrid

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.sendgrid.net
MAIL_PORT=587
MAIL_USERNAME=apikey
MAIL_PASSWORD=your-sendgrid-api-key
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@your-domain.com
MAIL_FROM_NAME="Your App"
```

### Amazon SES

```env
MAIL_MAILER=ses
MAIL_HOST=email-smtp.us-east-1.amazonaws.com
MAIL_PORT=587
MAIL_USERNAME=your-ses-smtp-username
MAIL_PASSWORD=your-ses-smtp-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=verified@your-domain.com
MAIL_FROM_NAME="Your App"
```

### Mailtrap (Testing)

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your-mailtrap-username
MAIL_PASSWORD=your-mailtrap-password
MAIL_ENCRYPTION=tls
```

## Examples

### Welcome Email

```php
<?php

namespace App\Mail;

use Nexus\Mail\Mailable;

class WelcomeEmail extends Mailable
{
    public function __construct(
        protected array $user
    ) {}

    public function build(): void
    {
        $this->subject('Welcome to ' . config('app.name'))
            ->view('emails.welcome', [
                'userName' => $this->user['name'],
                'loginUrl' => config('app.url') . '/login'
            ]);
    }
}

// Send
$user = User::find($userId);
$welcome = new WelcomeEmail($user);
$welcome->to($user['email'])->send();
```

**View** (`app/Views/emails/welcome.blade.php`):

```blade
<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .button { background: #007bff; color: white; padding: 10px 20px;
                  text-decoration: none; border-radius: 5px; display: inline-block; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Welcome, {{ $userName }}!</h1>
        <p>Thank you for joining our platform. We're excited to have you on board.</p>
        <p>
            <a href="{{ $loginUrl }}" class="button">Get Started</a>
        </p>
        <p>Best regards,<br>The {{ config('app.name') }} Team</p>
    </div>
</body>
</html>
```

### Password Reset Email

```php
<?php

namespace App\Mail;

use Nexus\Mail\Mailable;

class PasswordResetEmail extends Mailable
{
    public function __construct(
        protected string $resetToken,
        protected string $email
    ) {}

    public function build(): void
    {
        $resetUrl = config('app.url') . '/reset-password?token=' . $this->resetToken;

        $this->subject('Reset Your Password')
            ->view('emails.password-reset', [
                'resetUrl' => $resetUrl,
                'expiresIn' => '1 hour'
            ]);
    }
}

// Send
PasswordResetEmail::dispatch($token, $user['email']);
```

### Order Confirmation Email

```php
<?php

namespace App\Mail;

use Nexus\Mail\Mailable;

class OrderConfirmationEmail extends Mailable
{
    public function __construct(
        protected array $order,
        protected string $invoicePath
    ) {}

    public function build(): void
    {
        $this->subject('Order Confirmation #' . $this->order['order_number'])
            ->view('emails.order-confirmation', [
                'order' => $this->order,
                'total' => '$' . number_format($this->order['total'], 2)
            ])
            ->attach($this->invoicePath, 'invoice.pdf');
    }
}

// Send
$order = Order::find($orderId);
$invoicePath = storage_path("invoices/{$order['id']}.pdf");

$email = new OrderConfirmationEmail($order, $invoicePath);
$email->to($order['customer_email'])->queue();
```

### Newsletter Email

```php
<?php

namespace App\Mail;

use Nexus\Mail\Mailable;

class NewsletterEmail extends Mailable
{
    public function __construct(
        protected array $articles
    ) {}

    public function build(): void
    {
        $this->subject('Weekly Newsletter - ' . now()->format('F j, Y'))
            ->view('emails.newsletter', [
                'articles' => $this->articles
            ])
            ->text('emails.newsletter-text', [
                'articles' => $this->articles
            ]);
    }
}

// Send to all subscribers
$subscribers = User::where('subscribed', true)->get();
$articles = Article::latest()->limit(5)->get();

foreach ($subscribers as $subscriber) {
    $newsletter = new NewsletterEmail($articles);
    $newsletter->to($subscriber['email'])->queue('emails');
}
```

### Email Verification

```php
<?php

namespace App\Mail;

use Nexus\Mail\Mailable;

class VerifyEmailMail extends Mailable
{
    public function __construct(
        protected string $verificationUrl
    ) {}

    public function build(): void
    {
        $this->subject('Verify Your Email Address')
            ->view('emails.verify-email', [
                'verificationUrl' => $this->verificationUrl
            ]);
    }
}

// Send
$token = bin2hex(random_bytes(32));
$verificationUrl = config('app.url') . '/verify-email?token=' . $token;

$email = new VerifyEmailMail($verificationUrl);
$email->to($user['email'])->send();
```

### Bulk Email with Queue

```php
<?php

namespace App\Jobs;

use Nexus\Queue\Dispatchable;
use App\Mail\MarketingEmail;
use App\Models\User;

class SendBulkEmailJob
{
    use Dispatchable;

    public function __construct(
        protected array $userIds,
        protected string $campaign
    ) {}

    public function handle(): void
    {
        foreach ($this->userIds as $userId) {
            $user = User::find($userId);

            if ($user && $user['email_verified']) {
                $email = new MarketingEmail($this->campaign, $user);
                $email->to($user['email'])->queue('emails');

                // Add small delay to avoid overwhelming SMTP
                sleep(1);
            }
        }
    }
}

// Dispatch bulk email job
$userIds = User::where('subscribed', true)->pluck('id');
$chunks = array_chunk($userIds, 100);

foreach ($chunks as $chunk) {
    SendBulkEmailJob::dispatch($chunk, 'summer-sale');
}
```

## Best Practices

1. **Use Queues**: Queue emails for better performance
2. **Test First**: Use Mailtrap or similar for testing
3. **Secure Credentials**: Never commit SMTP passwords
4. **Use TLS**: Always use encryption in production
5. **Rate Limiting**: Be aware of SMTP rate limits
6. **Unsubscribe Links**: Include in marketing emails
7. **Error Handling**: Handle sending failures gracefully
8. **Plain Text**: Provide plain text alternatives
9. **Template Design**: Keep emails simple and responsive
10. **Monitor Delivery**: Track email delivery rates

## Testing

### Use Mailtrap for Development

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your-username
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls
```

### Test Email Sending

```php
// Test basic sending
try {
    mail()->to('test@example.com')
        ->subject('Test Email')
        ->text('This is a test')
        ->send();

    echo "Email sent successfully!";
} catch (\Exception $e) {
    echo "Failed: {$e->getMessage()}";
}
```

## Troubleshooting

### Connection Failed

1. Check SMTP credentials
2. Verify host and port
3. Check firewall settings
4. Test connection with telnet

### Authentication Failed

1. Verify username/password
2. Check if 2FA requires app password
3. Enable "less secure apps" if required

### TLS/SSL Errors

1. Verify encryption type (tls/ssl)
2. Check port (587 for TLS, 465 for SSL)
3. Update PHP OpenSSL extension

### Emails Not Sending

1. Check queue worker is running
2. Verify email configuration
3. Check failed_jobs table
4. Review application logs

### Gmail Specific Issues

1. Enable 2-factor authentication
2. Generate App Password
3. Don't use account password
4. Allow less secure apps (not recommended)

## Next Steps

- Learn about [Queues](queues.md)
- Understand [Views](views.md)
- Explore [Configuration](configuration.md)
