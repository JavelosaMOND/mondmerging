<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        User::create([
            'name' => 'Admin',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('adminpassword'),
            'role' => 'admin',
        ]);

        $cluster = User::create([
            'name' => 'Sandra (Cluster 4)',
            'email' => 'sandra.cluster4@gmail.com',
            'password' => Hash::make('password'),
            'role' => 'cluster',
            'cluster_id' => null, // Clusters don’t belong to another cluster
            'is_active' => true,
        ]);

        // List of barangays
        $barangayNames = [
            '19', '27', '33', 'Pahanocoy', 'Singcang','Tangub',
            'Pta. Taytay', 'Cabug', 'Sum-ag', 'Felisa', 'Handumanan', 'Alijis', 'Alangilan'
            , 'Villamonte'
        ];

        // Create barangays under this cluster
        foreach ($barangayNames as $barangay) {
            User::create([
                'name' => "Barangay $barangay",
                'email' => strtolower("barangay$barangay@gmail.com"),
                'password' => Hash::make('password'),
                'role' => 'barangay',
                'cluster_id' => $cluster->id, // Assign to Cluster 1
                'is_active' => true,
            ]);
        }
    }
}
