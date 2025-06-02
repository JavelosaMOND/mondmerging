<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    const ROLE_FACILITATOR = 'Facilitator';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'cluster_id',
        'is_active'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];
    public function cluster()
    {
        return $this->belongsTo(User::class, 'cluster_id');
    }

    // Alias for parent cluster
    public function parentCluster()
    {
        return $this->belongsTo(User::class, 'cluster_id');
    }

    // Child clusters under this cluster
    public function childClusters()
    {
        return $this->hasMany(User::class, 'cluster_id')->where('role', self::ROLE_FACILITATOR);
    }

    // Only barangays under this cluster
    public function barangays()
    {
        return $this->hasMany(User::class, 'cluster_id')->where('role', 'barangay');
    }

    public function weeklyReports() {
        return $this->hasMany(\App\Models\WeeklyReport::class, 'user_id');
    }
    public function monthlyReports() {
        return $this->hasMany(\App\Models\MonthlyReport::class, 'user_id');
    }
    public function quarterlyReports() {
        return $this->hasMany(\App\Models\QuarterlyReport::class, 'user_id');
    }
    public function annualReports() {
        return $this->hasMany(\App\Models\AnnualReport::class, 'user_id');
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
