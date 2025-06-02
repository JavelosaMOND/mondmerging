<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, create a backup of the current relationships
        $barangayClusterRelationships = DB::table('users as b')
            ->join('users as c', 'b.cluster_id', '=', 'c.id')
            ->where('b.role', 'barangay')
            ->select('b.id as barangay_id', 'c.id as old_cluster_id', 'c.name as old_cluster_name')
            ->get();
            
        // Create the new clusters based on existing cluster users
        $clusterMap = [];
        $existingClusters = DB::table('users')
            ->where('role', 'cluster')
            ->select('id', 'name')
            ->get();
            
        foreach ($existingClusters as $cluster) {
            $newClusterId = DB::table('clusters')->insertGetId([
                'name' => $cluster->name,
                'description' => 'Migrated from user ID ' . $cluster->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            $clusterMap[$cluster->id] = $newClusterId;
        }
        
        // Modify the users table
        Schema::table('users', function (Blueprint $table) {
            // Add new cluster_id column referencing the clusters table
            $table->foreignId('cluster_id')->nullable()->change();
            
            // Add a new column to track the role more specifically
            $table->enum('user_type', ['admin', 'facilitator', 'barangay'])->nullable()->after('role');
        });
        
        // Update existing users
        // Set facilitators
        DB::table('users')
            ->where('role', 'facilitator')
            ->update(['user_type' => 'facilitator']);
            
        // Set barangays
        DB::table('users')
            ->where('role', 'barangay')
            ->update(['user_type' => 'barangay']);
            
        // Set admins
        DB::table('users')
            ->where('role', 'admin')
            ->update(['user_type' => 'admin']);
            
        // Update barangay users to point to the new cluster IDs
        foreach ($barangayClusterRelationships as $relationship) {
            if (isset($clusterMap[$relationship->old_cluster_id])) {
                DB::table('users')
                    ->where('id', $relationship->barangay_id)
                    ->update(['cluster_id' => $clusterMap[$relationship->old_cluster_id]]);
            }
        }
        
        // Create facilitator-cluster relationships for existing facilitators
        $facilitators = DB::table('users')
            ->where('user_type', 'facilitator')
            ->select('id')
            ->get();
            
        foreach ($facilitators as $facilitator) {
            // For now, assign each facilitator to all clusters
            // This can be refined later through the UI
            foreach ($clusterMap as $newClusterId) {
                DB::table('facilitator_cluster')->insert([
                    'user_id' => $facilitator->id,
                    'cluster_id' => $newClusterId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This is a complex migration to reverse
        // It's recommended to have a backup before running this migration
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('user_type');
            // Note: We don't revert the cluster_id change as it would be complex
        });
    }
};
