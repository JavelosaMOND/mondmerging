# Gmail SMTP Setup for Email Notifications

This document provides instructions on how to set up Gmail SMTP for sending email notifications from your Laravel application.

## Prerequisites

1. A Gmail account
2. Two-factor authentication enabled on your Gmail account

## Steps to Set Up Gmail SMTP

### 1. Enable Two-Factor Authentication

If you haven't already, enable two-factor authentication for your Gmail account:

1. Go to your Google Account settings: https://myaccount.google.com/
2. Click on "Security" in the left sidebar
3. Under "Signing in to Google," click on "2-Step Verification"
4. Follow the prompts to enable two-factor authentication

### 2. Create an App Password

Once two-factor authentication is enabled, you'll need to create an app password:

1. Go to your Google Account settings: https://myaccount.google.com/
2. Click on "Security" in the left sidebar
3. Under "Signing in to Google," click on "App passwords"
4. Select "Mail" as the app and "Other" as the device
5. Enter a name for the app password (e.g., "Laravel App")
6. Click "Generate"
7. Google will generate a 16-character app password. **Copy this password** as you'll need it for your Laravel application

### 3. Update Your .env File

Update your `.env` file with the following settings:

```
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-gmail-account@gmail.com
MAIL_PASSWORD=your-16-character-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your-gmail-account@gmail.com
MAIL_FROM_NAME="${APP_NAME}"
```

Replace:
- `your-gmail-account@gmail.com` with your actual Gmail address
- `your-16-character-app-password` with the app password you generated in step 2

### 4. Test Your Configuration

To test if your email configuration is working correctly, you can use the following Artisan command:

```bash
php artisan tinker
```

Then, in the Tinker console, run:

```php
Mail::raw('Test email from Laravel', function($message) { $message->to('your-test-email@example.com')->subject('Test Email'); });
```

Replace `your-test-email@example.com` with an email address where you want to receive the test email.

## Troubleshooting

### Common Issues

1. **Connection could not be established with host smtp.gmail.com**
   - Make sure your server allows outgoing connections on port 587
   - Check if your hosting provider blocks SMTP connections

2. **Authentication failed**
   - Double-check your Gmail address and app password
   - Make sure you're using the app password, not your regular Gmail password

3. **Emails not being sent**
   - Check your Laravel logs for any errors
   - Make sure your queue worker is running if you're using queued emails

### Gmail Sending Limits

Be aware that Gmail has sending limits:
- 500 emails per day for regular Gmail accounts
- 2,000 emails per day for Google Workspace accounts

For production applications with high email volume, consider using a dedicated email service like:
- Mailgun
- SendGrid
- Amazon SES

## Security Considerations

1. Never commit your `.env` file to version control
2. Regularly rotate your app passwords
3. Monitor your Gmail account for any suspicious activity

## Additional Resources

- [Laravel Mail Documentation](https://laravel.com/docs/10.x/mail)
- [Google Account Help: Sign in with App Passwords](https://support.google.com/accounts/answer/185833)
