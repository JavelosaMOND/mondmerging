<?php

namespace App\Models\Traits;

trait ReportStatus
{
    const STATUS_SUBMITTED = 'submitted';
    const STATUS_NO_SUBMISSION = 'no submission';

    public function isSubmitted(): bool
    {
        return $this->status === self::STATUS_SUBMITTED;
    }

    public function isNoSubmission(): bool
    {
        return $this->status === self::STATUS_NO_SUBMISSION;
    }
}
