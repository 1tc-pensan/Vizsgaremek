<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateReportRequest;
use App\Models\Report;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Report::with(['user:id,name,email', 'category:id,name', 'images'])
            ->withCount([
                'votes as upvotes_count'   => fn($q) => $q->where('vote_type', 'up'),
                'votes as downvotes_count' => fn($q) => $q->where('vote_type', 'down'),
            ]);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $reports = $query->orderBy('created_at', 'desc')->get();

        return response()->json($reports);
    }

    public function destroy(Report $report): JsonResponse
    {
        $report->delete();
        return response()->json(['message' => 'Bejelentés törölve.']);
    }

    public function approve(Report $report): JsonResponse
    {
        $report->update(['status' => 'approved']);
        return response()->json(['message' => 'Bejelentés jóváhagyva.', 'report' => $report]);
    }

    public function reject(Report $report): JsonResponse
    {
        $report->update(['status' => 'rejected']);
        return response()->json(['message' => 'Bejelentés elutasítva.', 'report' => $report]);
    }
}
