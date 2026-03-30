<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->loadCount('reports');
        return response()->json($user);
    }

    public function update(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name'         => ['sometimes', 'string', 'max:255'],
            'email'        => ['sometimes', 'email', 'unique:users,email,' . $user->id],
            'password'     => ['sometimes', 'string', 'min:8', 'confirmed'],
        ]);

        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        }

        $user->update($validated);

        return response()->json($user);
    }

    public function userReports(Request $request, int $userId): JsonResponse
    {
        $reports = \App\Models\Report::where('user_id', $userId)
            ->with(['category:id,name'])
            ->withCount([
                'votes as upvotes_count'   => fn($q) => $q->where('vote_type', 'up'),
                'votes as downvotes_count' => fn($q) => $q->where('vote_type', 'down'),
            ])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($reports);
    }
}
