<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreReportRequest;
use App\Http\Requests\UpdateReportRequest;
use App\Models\Report;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;

class ReportController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Report::with(['user:id,name', 'category:id,name', 'images'])
            ->withCount([
                'votes as upvotes_count'   => fn($q) => $q->where('vote_type', 'up'),
                'votes as downvotes_count' => fn($q) => $q->where('vote_type', 'down'),
            ])
            ->where('status', 'approved');

        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->has('date_from')) {
            $query->whereDate('date', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->whereDate('date', '<=', $request->date_to);
        }

        $sortBy = $request->get('sort_by', 'created_at');
        $sortDir = $request->get('sort_dir', 'desc');

        if ($sortBy === 'credibility') {
            $query->orderByRaw('(SELECT COUNT(*) FROM votes WHERE votes.report_id = reports.id AND votes.vote_type = "up" AND votes.deleted_at IS NULL) - (SELECT COUNT(*) FROM votes WHERE votes.report_id = reports.id AND votes.vote_type = "down" AND votes.deleted_at IS NULL) ' . ($sortDir === 'asc' ? 'ASC' : 'DESC'));
        } else {
            $allowedSorts = ['created_at', 'date', 'title'];
            $query->orderBy(in_array($sortBy, $allowedSorts) ? $sortBy : 'created_at', $sortDir === 'asc' ? 'asc' : 'desc');
        }

        $reports = $query->get();

        return response()->json($reports);
    }

    public function show(Request $request, Report $report): JsonResponse
    {
        if ($report->status !== 'approved') {
            $token = $request->bearerToken();
            $user = $token ? PersonalAccessToken::findToken($token)?->tokenable : null;
            $isOwner = $user && $user->id === $report->user_id;
            $isAdmin = $user && $user->isAdmin();

            if (!$isOwner && !$isAdmin) {
                return response()->json(['message' => 'A bejelentés nem elérhető.'], 404);
            }
        }

        $report->load(['user:id,name', 'category:id,name', 'images']);
        $report->loadCount([
            'votes as upvotes_count'   => fn($q) => $q->where('vote_type', 'up'),
            'votes as downvotes_count' => fn($q) => $q->where('vote_type', 'down'),
        ]);

        return response()->json($report);
    }

    public function store(StoreReportRequest $request): JsonResponse
    {
        $report = Report::create(array_merge(
            $request->validated(),
            ['user_id' => $request->user()->id, 'status' => 'pending']
        ));

        $report->load(['user:id,name', 'category:id,name']);

        return response()->json($report, 201);
    }

    public function update(UpdateReportRequest $request, Report $report): JsonResponse
    {
        $user = $request->user();

        if (!$user->isAdmin() && $report->user_id !== $user->id) {
            return response()->json(['message' => 'Hozzáférés megtagadva.'], 403);
        }

        $report->update($request->validated());
        $report->load(['user:id,name', 'category:id,name', 'images']);

        return response()->json($report);
    }

    public function destroy(Request $request, Report $report): JsonResponse
    {
        $user = $request->user();

        if (!$user->isAdmin() && $report->user_id !== $user->id) {
            return response()->json(['message' => 'Hozzáférés megtagadva.'], 403);
        }

        $report->delete();

        return response()->json(['message' => 'Bejelentés törölve.']);
    }

    public function mapReports(): JsonResponse
    {
        $reports = Report::whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->where('status', 'approved')
            ->with('category:id,name')
            ->get(['id', 'title', 'latitude', 'longitude', 'date', 'category_id', 'status']);

        return response()->json($reports);
    }
}
