<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\VoteRequest;
use App\Models\Report;
use App\Models\Vote;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VoteController extends Controller
{
    public function vote(VoteRequest $request, Report $report): JsonResponse
    {
        $user = $request->user();
        $voteType = $request->vote_type;

        $existingVote = Vote::where('report_id', $report->id)
            ->where('user_id', $user->id)
            ->first();

        if ($existingVote) {
            if ($existingVote->vote_type === $voteType) {
                // Ugyanolyan szavazat -> visszavonás (forceDelete, mert a unique constraint miatt soft-delete után nem lehet újra szavazni)
                $existingVote->forceDelete();
                $message = 'Szavazat visszavonva.';
            } else {
                // Ellentétes szavazat -> módosítás
                $existingVote->update(['vote_type' => $voteType]);
                $message = 'Szavazat módosítva.';
            }
        } else {
            Vote::create([
                'report_id' => $report->id,
                'user_id'   => $user->id,
                'vote_type' => $voteType,
            ]);
            $message = 'Szavazat leadva.';
        }

        $up   = $report->votes()->where('vote_type', 'up')->count();
        $down = $report->votes()->where('vote_type', 'down')->count();

        return response()->json([
            'message'           => $message,
            'upvotes'           => $up,
            'downvotes'         => $down,
            'credibility_score' => $up - $down,
        ]);
    }

    public function credibility(Report $report): JsonResponse
    {
        $up   = $report->votes()->where('vote_type', 'up')->count();
        $down = $report->votes()->where('vote_type', 'down')->count();

        return response()->json([
            'upvotes'           => $up,
            'downvotes'         => $down,
            'credibility_score' => $up - $down,
        ]);
    }
}
