<?php

namespace App\Models;

use App\Models\Traits\ReportStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Testing\Fluent\Concerns\Has;
use SebastianBergmann\CodeCoverage\Report\Xml\Report;

class MonthlyReport extends Model
{
    use HasFactory, ReportStatus;

    protected $fillable = [
        'user_id',
        'report_type_id',
        'month',
        'year',
        'file_name',
        'file_path',
        'deadline',
        'status',
        'remarks'
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

