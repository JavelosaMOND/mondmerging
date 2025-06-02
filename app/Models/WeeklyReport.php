<?php

namespace App\Models;

use App\Models\Traits\ReportStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\ReportType;

class WeeklyReport extends Model
{
    use HasFactory, ReportStatus;
    protected $fillable = [
        'user_id',
        'report_type_id',
        'month',
        'week_number',
        'num_of_clean_up_sites',
        'num_of_participants',
        'num_of_barangays',
        'total_volume',
        'deadline',
        'status',
        'file_name',
        'file_path',
        'remarks',
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }
    public function reportType(){
        return $this->belongsTo(ReportType::class);
    }
}
