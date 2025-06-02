<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReportRemarksNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    protected $report;
    protected $remarks;
    protected $reportType;
    protected $adminName;

    public function __construct($report, $remarks, $reportType, $adminName)
    {
        $this->report = $report;
        $this->remarks = $remarks;
        $this->reportType = $reportType;
        $this->adminName = $adminName;

        // Log notification creation
        \Illuminate\Support\Facades\Log::info('ReportRemarksNotification created', [
            'report_id' => $report->id,
            'report_type' => $reportType,
            'report_name' => $report->reportType->name,
            'remarks' => $remarks,
            'admin_name' => $adminName
        ]);
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $reportTypeName = ucfirst($this->reportType);
        $viewUrl = url('/barangay/submissions');

        // Log email being sent
        \Illuminate\Support\Facades\Log::info('Sending email notification', [
            'to' => $notifiable->email,
            'name' => $notifiable->name,
            'report_type' => $this->reportType,
            'report_name' => $this->report->reportType->name,
            'view_url' => $viewUrl
        ]);

        return (new MailMessage)
                    ->subject("New Remarks on Your {$reportTypeName} Report")
                    ->greeting("Hello {$notifiable->name},")
                    ->line("Admin {$this->adminName} has added remarks to your {$this->reportType} report.")
                    ->line("Report: {$this->report->reportType->name}")
                    ->line("Remarks: {$this->remarks}")
                    ->action('View Report', $viewUrl)
                    ->line('Please log in to your account to view the complete details.')
                    ->line('Thank you for your attention to this matter.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'report_id' => $this->report->id,
            'report_type' => $this->reportType,
            'report_name' => $this->report->reportType->name,
            'remarks' => $this->remarks,
            'admin_name' => $this->adminName,
            'timestamp' => now()->toIso8601String()
        ];
    }
}
