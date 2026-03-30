<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Report;
use App\Models\ReportImage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ImageController extends Controller
{
    public function index(Report $report): JsonResponse
    {
        return response()->json($report->images);
    }

    public function store(Request $request, Report $report): JsonResponse
    {
        $user = $request->user();

        if (!$user->isAdmin() && $report->user_id !== $user->id) {
            return response()->json(['message' => 'Hozzáférés megtagadva.'], 403);
        }

        $request->validate([
            'images'   => ['required', 'array', 'max:10'],
            'images.*' => ['required', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:5120'],
        ], [
            'images.required'      => 'Legalább egy képet fel kell tölteni.',
            'images.array'         => 'Érvénytelen képformátum.',
            'images.max'           => 'Egyszerre legfeljebb 10 képet lehet feltölteni.',
            'images.*.image'       => 'Csak képfájlok tölthetők fel.',
            'images.*.mimes'       => 'Csak jpeg, png, jpg, gif és webp formátum engedélyezett.',
            'images.*.max'         => 'Egy kép mérete legfeljebb 5 MB lehet.',
        ]);

        $uploaded = [];

        foreach ($request->file('images') as $file) {
            $path = $file->store('report_images', 'public');

            $uploaded[] = ReportImage::create([
                'report_id'  => $report->id,
                'image_path' => $path,
            ]);
        }

        return response()->json($uploaded, 201);
    }

    public function destroy(Request $request, ReportImage $image): JsonResponse
    {
        $user = $request->user();
        $report = $image->report;

        if (!$user->isAdmin() && $report->user_id !== $user->id) {
            return response()->json(['message' => 'Hozzáférés megtagadva.'], 403);
        }

        Storage::disk('public')->delete($image->image_path);
        $image->delete();

        return response()->json(['message' => 'Kép törölve.']);
    }
}
