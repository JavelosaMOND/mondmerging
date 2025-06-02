<?php

namespace App\Repositories;

use App\Models\Report;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class ReportRepository
{
    protected $model;
    
    /**
     * Create a new repository instance.
     */
    public function __construct(Report $model)
    {
        $this->model = $model;
    }
    
    /**
     * Get reports for a specific user with optional caching.
     *
     * @param User $user The user to get reports for
     * @param array $filters Optional filters to apply
     * @param bool $useCache Whether to cache the results
     * @param int $cacheDuration Cache duration in minutes
     * @return Collection
     */
    public function getReportsForUser(User $user, array $filters = [], bool $useCache = true, int $cacheDuration = 60): Collection
    {
        $cacheKey = "user_reports_{$user->id}_" . md5(json_encode($filters));
        
        if ($useCache && Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }
        
        $query = $this->model->query()->with(['reportType', 'user', 'files']);
        
        // Filter by user
        if ($user->isBarangay()) {
            $query->where('user_id', $user->id);
        } elseif ($user->isFacilitator()) {
            // Get all barangay users in the facilitator's clusters
            $barangayUserIds = $user->managedBarangays()->pluck('id');
            $query->whereIn('user_id', $barangayUserIds);
        }
        // Admin can see all reports, so no filtering needed
        
        // Apply additional filters
        if (!empty($filters['frequency'])) {
            $query->where('frequency', $filters['frequency']);
        }
        
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        
        if (!empty($filters['report_type_id'])) {
            $query->where('report_type_id', $filters['report_type_id']);
        }
        
        if (!empty($filters['start_date'])) {
            $query->where('created_at', '>=', Carbon::parse($filters['start_date']));
        }
        
        if (!empty($filters['end_date'])) {
            $query->where('created_at', '<=', Carbon::parse($filters['end_date'])->endOfDay());
        }
        
        // Sort by default or requested column
        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortDir = $filters['sort_dir'] ?? 'desc';
        $query->orderBy($sortBy, $sortDir);
        
        $reports = $query->get();
        
        if ($useCache) {
            Cache::put($cacheKey, $reports, $cacheDuration);
        }
        
        return $reports;
    }
    
    /**
     * Get reports with pagination.
     *
     * @param User $user
     * @param array $filters
     * @param int $perPage
     * @param int $page
     * @return LengthAwarePaginator
     */
    public function getPaginatedReports(User $user, array $filters = [], int $perPage = 10, int $page = 1): LengthAwarePaginator
    {
        $reports = $this->getReportsForUser($user, $filters, false); // Don't cache for pagination
        
        return new LengthAwarePaginator(
            $reports->forPage($page, $perPage),
            $reports->count(),
            $perPage,
            $page,
            [
                'path' => request()->url(),
                'query' => request()->query()
            ]
        );
    }
    
    /**
     * Get overdue reports for a user.
     *
     * @param User $user
     * @return Collection
     */
    public function getOverdueReports(User $user): Collection
    {
        $cacheKey = "overdue_reports_{$user->id}";
        $cacheDuration = 60; // 60 minutes
        
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }
        
        $reports = $this->model->where('user_id', $user->id)
            ->where('deadline', '<', now())
            ->where('status', Report::STATUS_NO_SUBMISSION)
            ->with(['reportType'])
            ->get();
            
        Cache::put($cacheKey, $reports, $cacheDuration);
        
        return $reports;
    }
    
    /**
     * Get dashboard statistics for a user.
     *
     * @param User $user
     * @return array
     */
    public function getDashboardStats(User $user): array
    {
        $cacheKey = "dashboard_stats_{$user->id}";
        $cacheDuration = 60; // 60 minutes
        
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }
        
        $reports = $this->getReportsForUser($user, [], false);
        
        $stats = [
            'totalReports' => $reports->count(),
            'submittedReports' => $reports->where('status', Report::STATUS_SUBMITTED)->count(),
            'noSubmissionReports' => $reports->where('status', Report::STATUS_NO_SUBMISSION)->count(),
            'recentReports' => $reports->sortByDesc('created_at')->take(5),
        ];
        
        Cache::put($cacheKey, $stats, $cacheDuration);
        
        return $stats;
    }
    
    /**
     * Find a report by ID and user.
     *
     * @param int $id
     * @param User $user
     * @return Report|null
     */
    public function findByIdAndUser(int $id, User $user): ?Report
    {
        return $this->model->where('id', $id)
            ->where('user_id', $user->id)
            ->first();
    }
    
    /**
     * Create a new report.
     *
     * @param array $data
     * @return Report
     */
    public function create(array $data): Report
    {
        return $this->model->create($data);
    }
    
    /**
     * Update an existing report.
     *
     * @param Report $report
     * @param array $data
     * @return bool
     */
    public function update(Report $report, array $data): bool
    {
        return $report->update($data);
    }
    
    /**
     * Clear the cache for a user.
     *
     * @param User $user
     * @return void
     */
    public function clearCache(User $user): void
    {
        Cache::forget("user_reports_{$user->id}_" . md5(json_encode([])));
        Cache::forget("overdue_reports_{$user->id}");
        Cache::forget("dashboard_stats_{$user->id}");
        Cache::forget("barangay_dashboard_{$user->id}");
    }
} 