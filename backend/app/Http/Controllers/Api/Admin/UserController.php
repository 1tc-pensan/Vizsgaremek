<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = User::withCount('reports');

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->orderBy('created_at', 'desc')->get();

        return response()->json($users);
    }

    public function ban(User $user): JsonResponse
    {
        if ($user->isAdmin()) {
            return response()->json(['message' => 'Admin felhasználó nem tiltható.'], 422);
        }

        $user->update(['is_banned' => true]);

        return response()->json(['message' => 'Felhasználó tiltva.', 'user' => $user]);
    }

    public function unban(User $user): JsonResponse
    {
        $user->update(['is_banned' => false]);

        return response()->json(['message' => 'Felhasználó tiltása feloldva.', 'user' => $user]);
    }
}
