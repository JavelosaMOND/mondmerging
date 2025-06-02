<?php

namespace App\Models;

use App\Models\Traits\ReportStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SemestralReport extends Model
{
    use HasFactory, ReportStatus;

    protected $fillable = [
        'user_id',
        'report_type_id',
        'semester',
        'year',
        'file_name',
        'file_path',
        'deadline',
        'status',
        'remarks',
        'sem_number'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function reportType()
    {
        return $this->belongsTo(ReportType::class);
    }
}
