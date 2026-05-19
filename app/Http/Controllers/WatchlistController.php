<?php

namespace App\Http\Controllers;

use App\Models\Movie;
use App\Models\Series;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class WatchlistController extends Controller
{
    /**
     * Display the unified watchlist index.
     */
    public function index(Request $request)
    {
        $user_id = Auth::id();
        $filterType = $request->query('type', 'both');

        $movies = Movie::where('user_id', $user_id)
            ->orderBy('hierarchy_index')
            ->orderBy('updated_at', 'desc')
            ->get()
            ->map(function (Movie $movie) {
                $movie->type = 'movie';
                $movie->picture_src = null;

                if (!empty($movie->picture)) {
                    $mimeType = (new \finfo(FILEINFO_MIME_TYPE))->buffer($movie->picture) ?: 'image/jpeg';

                    if (!str_starts_with($mimeType, 'image/')) {
                        $mimeType = 'image/jpeg';
                    }

                    $movie->picture_src = 'data:' . $mimeType . ';base64,' . base64_encode($movie->picture);
                }

                return $movie;
            });

        $series = Series::where('user_id', $user_id)
            ->orderBy('hierarchy_index')
            ->orderBy('updated_at', 'desc')
            ->get()
            ->map(function (Series $series) {
                $series->type = 'series';
                $series->picture_src = null;

                if (!empty($series->picture)) {
                    $mimeType = (new \finfo(FILEINFO_MIME_TYPE))->buffer($series->picture) ?: 'image/jpeg';

                    if (!str_starts_with($mimeType, 'image/')) {
                        $mimeType = 'image/jpeg';
                    }

                    $series->picture_src = 'data:' . $mimeType . ';base64,' . base64_encode($series->picture);
                }

                return $series;
            });

        $watchables = $movies->merge($series)->sortBy('hierarchy_index')->sortByDesc('updated_at')->values();

        return view('pages.watchlistPages.index', compact('watchables', 'filterType'));
    }

    /**
     * Reorder both movies and series.
     */
    public function reorder(Request $request): JsonResponse
    {
        $userId = (int) Auth::id();
        $orderedIds = $request->validate(['ordered_ids' => ['required', 'array', 'min:1']])['ordered_ids'];
        
        $movieCount = Movie::where('user_id', $userId)->whereIn('id', $orderedIds)->count();
        $seriesCount = Series::where('user_id', $userId)->whereIn('id', $orderedIds)->count();
        $totalOwnedMovies = Movie::where('user_id', $userId)->count();
        $totalOwnedSeries = Series::where('user_id', $userId)->count();
        $totalOwned = $totalOwnedMovies + $totalOwnedSeries;

        abort_unless(
            ($movieCount + $seriesCount) === count($orderedIds) && $totalOwned === count($orderedIds),
            403
        );

        DB::transaction(function () use ($orderedIds, $userId) {
            foreach ($orderedIds as $index => $id) {
                if (Movie::where('id', $id)->where('user_id', $userId)->exists()) {
                    Movie::where('id', $id)
                        ->where('user_id', $userId)
                        ->update(['hierarchy_index' => $index + 1]);
                } else {
                    Series::where('id', $id)
                        ->where('user_id', $userId)
                        ->update(['hierarchy_index' => $index + 1]);
                }
            }
        });

        return response()->json([
            'message' => 'Watchables reordered successfully.',
        ]);
    }
}
