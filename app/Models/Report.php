<?php

namespace App\Models;

use App\Models\Traits\ReportStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Report extends Model
{
    use HasFactory, ReportStatus;
    
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'report_type_id',
        'frequency',
        'period_data', // JSON field to store period-specific data (month, week, quarter, etc.)
        'deadline',
        'status',
        'file_name',
        'file_path',
        'remarks',
        'num_of_clean_up_sites',
        'num_of_participants',
        'num_of_barangays',
        'total_volume',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'period_data' => 'array',
        'deadline' => 'datetime',
    ];

    /**
     * Get the user that owns the report.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the report type associated with the report.
     */
    public function reportType(): BelongsTo
    {
        return $this->belongsTo(ReportType::class);
    }

    /**
     * Get all files associated with this report.
     */
    public function files(): MorphMany
    {
        return $this->morphMany(ReportFile::class, 'reportable');
    }

    /**
     * Scope a query to only include reports of a specific frequency.
     */
    public function scopeOfFrequency($query, string $frequency)
    {
        return $query->where('frequency', $frequency);
    }

    /**
     * Scope a query to only include reports for a specific user.
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope a query to only include reports due before a specific date.
     */
    public function scopeDueBefore($query, $date)
    {
        return $query->where('deadline', '<', $date);
    }

    /**
     * Get the period display name based on frequency and period data.
     */
    public function getPeriodDisplayAttribute(): string
    {
        switch ($this->frequency) {
            case 'weekly':
                return 'Week ' . ($this->period_data['week_number'] ?? '?') . 
                       ' of ' . ($this->period_data['month'] ?? '?');
            case 'monthly':
                return $this->period_data['month'] ?? '?';
            case 'quarterly':
                return 'Q' . ($this->period_data['quarter'] ?? '?') . 
                       ' ' . ($this->period_data['year'] ?? date('Y'));
            case 'semestral':
                return 'Semester ' . ($this->period_data['semester'] ?? '?') . 
                       ' ' . ($this->period_data['year'] ?? date('Y'));
            case 'annual':
                return 'Annual ' . ($this->period_data['year'] ?? date('Y'));
            default:
                return 'Unknown period';
        }
    }
} 