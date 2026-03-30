<?php

namespace App\Http\Controllers\Api;

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
        // Összesített számok
        $totalReports = Report::where('status', 'approved')->count();
        $totalUsers   = User::count();
        $totalVotes   = Vote::count();

        // Státusz szerinti bontás
        $byStatus = Report::selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        // Kategória szerinti bontás (csak approved)
        $byCategory = Category::withCount(['reports' => fn($q) => $q->where('status', 'approved')])
            ->orderByDesc('reports_count')
            ->get(['id', 'name', 'reports_count']);

        // Top 5 leghitelesebb (approved)
        $topCredible = Report::where('status', 'approved')
            ->withCount([
                'votes as upvotes_count'   => fn($q) => $q->where('vote_type', 'up'),
                'votes as downvotes_count' => fn($q) => $q->where('vote_type', 'down'),
            ])
            ->orderByRaw('(upvotes_count - downvotes_count) DESC')
            ->with('category:id,name')
            ->limit(5)
            ->get()
            ->map(fn($r) => [
                'id'       => $r->id,
                'title'    => $r->title,
                'score'    => $r->upvotes_count - $r->downvotes_count,
                'category' => $r->category,
            ]);

        // Legutóbbi 5 jóváhagyott bejelentés
        $recent = Report::where('status', 'approved')
            ->with('category:id,name')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get(['id', 'title', 'date', 'category_id', 'created_at']);

        return response()->json([
            'total_reports' => $totalReports,
            'total_users'   => $totalUsers,
            'total_votes'   => $totalVotes,
            'by_status'     => $byStatus,
            'by_category'   => $byCategory,
            'top_credible'  => $topCredible,
            'recent'        => $recent,
        ]);
    }
}
