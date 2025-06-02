<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cluster extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'is_active',
    ];

    /**
     * Get the barangays that belong to this cluster.
     */
    public function barangays()
    {
        return $this->hasMany(User::class, 'cluster_id')
            ->where('user_type', 'barangay');
    }

    /**
     * Get the facilitators assigned to this cluster.
     */
    public function facilitators()
    {
        return $this->belongsToMany(User::class, 'facilitator_cluster')
            ->where('user_type', 'facilitator');
    }
}
