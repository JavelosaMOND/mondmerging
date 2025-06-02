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
        'user_type',
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
    /**
     * Get the cluster that the barangay belongs to.
     */
    public function cluster()
    {
        return $this->belongsTo(Cluster::class, 'cluster_id');
    }

    /**
     * Get the clusters that the facilitator is assigned to.
     */
    public function assignedClusters()
    {
        return $this->belongsToMany(Cluster::class, 'facilitator_cluster', 'user_id', 'cluster_id');
    }

    /**
     * Get the barangays that belong to the facilitator's clusters.
     */
    public function managedBarangays()
    {
        return User::whereIn('cluster_id', $this->assignedClusters()->pluck('clusters.id'))
            ->where('user_type', 'barangay');
    }

    /**
     * Check if the user is a facilitator.
     */
    public function isFacilitator()
    {
        return $this->user_type === 'facilitator';
    }

    /**
     * Check if the user is a barangay.
     */
    public function isBarangay()
    {
        return $this->user_type === 'barangay';
    }

    /**
     * Check if the user is an admin.
     */
    public function isAdmin()
    {
        return $this->user_type === 'admin';
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
