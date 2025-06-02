<?php

namespace App\Http\Controllers;

use App\Models\Cluster;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ClusterController extends Controller
{
    /**
     * Display a listing of the clusters.
     */
    public function index()
    {
        $clusters = Cluster::with(['facilitators', 'barangays'])->get();
        return view('admin.clusters.index', compact('clusters'));
    }

    /**
     * Show the form for creating a new cluster.
     */
    public function create()
    {
        $facilitators = User::where('user_type', 'facilitator')
            ->where('is_active', true)
            ->get();
            
        return view('admin.clusters.create', compact('facilitators'));
    }

    /**
     * Store a newly created cluster in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:clusters,name',
            'description' => 'nullable|string',
            'facilitators' => 'nullable|array',
            'facilitators.*' => 'exists:users,id',
        ]);

        DB::beginTransaction();
        try {
            $cluster = Cluster::create([
                'name' => $request->name,
                'description' => $request->description,
                'is_active' => true,
            ]);

            // Assign facilitators to the cluster
            if ($request->has('facilitators')) {
                foreach ($request->facilitators as $facilitatorId) {
                    DB::table('facilitator_cluster')->insert([
                        'user_id' => $facilitatorId,
                        'cluster_id' => $cluster->id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            DB::commit();
            return redirect()->route('admin.clusters.index')
                ->with('success', 'Cluster created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to create cluster: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified cluster.
     */
    public function show(Cluster $cluster)
    {
        $cluster->load(['facilitators', 'barangays']);
        return view('admin.clusters.show', compact('cluster'));
    }

    /**
     * Show the form for editing the specified cluster.
     */
    public function edit(Cluster $cluster)
    {
        $facilitators = User::where('user_type', 'facilitator')
            ->where('is_active', true)
            ->get();
            
        $assignedFacilitatorIds = $cluster->facilitators->pluck('id')->toArray();
        
        return view('admin.clusters.edit', compact('cluster', 'facilitators', 'assignedFacilitatorIds'));
    }

    /**
     * Update the specified cluster in storage.
     */
    public function update(Request $request, Cluster $cluster)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:clusters,name,' . $cluster->id,
            'description' => 'nullable|string',
            'facilitators' => 'nullable|array',
            'facilitators.*' => 'exists:users,id',
            'is_active' => 'boolean',
        ]);

        DB::beginTransaction();
        try {
            $cluster->update([
                'name' => $request->name,
                'description' => $request->description,
                'is_active' => $request->has('is_active'),
            ]);

            // Update facilitator assignments
            DB::table('facilitator_cluster')->where('cluster_id', $cluster->id)->delete();
            
            if ($request->has('facilitators')) {
                foreach ($request->facilitators as $facilitatorId) {
                    DB::table('facilitator_cluster')->insert([
                        'user_id' => $facilitatorId,
                        'cluster_id' => $cluster->id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            DB::commit();
            return redirect()->route('admin.clusters.index')
                ->with('success', 'Cluster updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to update cluster: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified cluster from storage.
     */
    public function destroy(Cluster $cluster)
    {
        // Check if there are barangays assigned to this cluster
        if ($cluster->barangays()->count() > 0) {
            return back()->with('error', 'Cannot delete cluster with assigned barangays.');
        }

        DB::beginTransaction();
        try {
            // Remove facilitator assignments
            DB::table('facilitator_cluster')->where('cluster_id', $cluster->id)->delete();
            
            // Delete the cluster
            $cluster->delete();

            DB::commit();
            return redirect()->route('admin.clusters.index')
                ->with('success', 'Cluster deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to delete cluster: ' . $e->getMessage());
        }
    }
}
