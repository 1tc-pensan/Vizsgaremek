<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Report;
use App\Models\User;
use App\Models\Vote;
use Illuminate\Http\JsonResponse;

class StatisticsController extends Controller
{
    public function index(): JsonResponse
    {
        $totalReports   = Report::count();
        $pendingReports = Report::where('status', 'pending')->count();
        $approvedReports = Report::where('status', 'approved')->count();
        $rejectedReports = Report::where('status', 'rejected')->count();
        $totalUsers     = User::count();
        $bannedUsers    = User::where('is_banned', true)->count();
        $totalVotes     = Vote::count();
        $totalCategories = Category::count();

        $topReports = Report::withCount([
            'votes as upvotes_count'   => fn($q) => $q->where('vote_type', 'up'),
            'votes as downvotes_count' => fn($q) => $q->where('vote_type', 'down'),
        ])
        ->orderByRaw('upvotes_count - downvotes_count DESC')
        ->with('category:id,name', 'user:id,name')
        ->limit(5)
        ->get();

        $reportsByCategory = Category::withCount('reports')
            ->orderBy('reports_count', 'desc')
            ->get(['id', 'name', 'reports_count']);

        return response()->json([
            'reports' => [
                'total'    => $totalReports,
                'pending'  => $pendingReports,
                'approved' => $approvedReports,
                'rejected' => $rejectedReports,
            ],
            'users' => [
                'total'  => $totalUsers,
                'banned' => $bannedUsers,
            ],
            'total_votes'       => $totalVotes,
            'total_categories'  => $totalCategories,
            'top_reports'       => $topReports,
            'reports_by_category' => $reportsByCategory,
        ]);
    }
}
