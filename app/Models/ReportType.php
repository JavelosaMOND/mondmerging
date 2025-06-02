<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReportType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'frequency',
        'deadline',
        'allowed_file_types'
    ];

    protected $casts = [
        'allowed_file_types' => 'array',
        'deadline' => 'date'
    ];

    public static function frequencies()
    {
        return [
            'weekly' => 'Weekly',
            'monthly' => 'Monthly',
            'quarterly' => 'Quarterly',
            'semestral' => 'Semestral',
            'annual' => 'Annual'
        ];
    }

    public static function availableFileTypes()
    {
        return [
            'pdf' => 'PDF Document',
            'doc' => 'Word Document',
            'docx' => 'Word Document',
            'xls' => 'Excel Spreadsheet',
            'xlsx' => 'Excel Spreadsheet',
            'jpg' => 'JPEG Image',
            'jpeg' => 'JPEG Image',
            'png' => 'PNG Image',
            'zip' => 'ZIP Archive',
            'rar' => 'RAR Archive'
        ];
    }

    public function getFormattedNameAttribute()
    {
        return ucwords(str_replace('_', ' ', $this->name));
    }
}
