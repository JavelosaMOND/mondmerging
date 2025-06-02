<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class TestEmail extends Command
{
    protected $signature = 'email:test {email}';
    protected $description = 'Test email functionality';

    public function handle()
    {
        $email = $this->argument('email');
        $this->info("Sending test email to: {$email}");

        // Display current mail configuration
        $this->info("Mail Configuration:");
        $this->info("MAIL_MAILER: " . config('mail.mailer'));
        $this->info("MAIL_HOST: " . config('mail.mailers.smtp.host'));
        $this->info("MAIL_PORT: " . config('mail.mailers.smtp.port'));
        $this->info("MAIL_ENCRYPTION: " . config('mail.mailers.smtp.encryption'));
        $this->info("MAIL_USERNAME: " . config('mail.mailers.smtp.username'));

        try {
            Mail::raw('Test email from Laravel', function($message) use ($email) {
                $message->to($email)
                        ->subject('Test Email from Laravel');
            });

            $this->info('Email sent successfully!');
        } catch (\Exception $e) {
            $this->error('Failed to send email: ' . $e->getMessage());
            $this->error('Error trace: ' . $e->getTraceAsString());

            // Suggest possible solutions
            $this->warn('Possible solutions:');
            $this->warn('1. Check if your Gmail account has 2FA enabled and you\'re using an app password');
            $this->warn('2. Make sure your Gmail account allows less secure apps if you\'re not using an app password');
            $this->warn('3. Check if your Gmail account has reached its sending limit');
            $this->warn('4. Try using "smtp" instead of "smtps" for MAIL_MAILER in your .env file');
            $this->warn('5. Try removing MAIL_ENCRYPTION or setting it to null in your .env file');

            return 1;
        }

        return 0;
    }
}
