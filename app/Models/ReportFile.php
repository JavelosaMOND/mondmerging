<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Storage;

class ReportFile extends Model
{
    use HasFactory;

    const STATUS_SUBMITTED = 'submitted';
    const STATUS_NO_SUBMISSION = 'no submission';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'original_filename',
        'stored_filename',
        'file_path',
        'file_size',
        'mime_type',
        'reportable_id',
        'reportable_type',
        'user_id',
        'status',
    ];

    /**
     * Get the parent reportable model.
     */
    public function reportable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the user that uploaded the file.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the full path to the file.
     */
    public function getFullPathAttribute()
    {
        return $this->file_path . '/' . $this->stored_filename;
    }

    /**
     * Check if the file exists in storage.
     */
    public function fileExists()
    {
        return Storage::exists($this->full_path);
    }

    /**
     * Get the file size in a human-readable format.
     */
    public function getReadableFileSizeAttribute()
    {
        $bytes = $this->file_size;
        
        $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];
        
        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }
}
