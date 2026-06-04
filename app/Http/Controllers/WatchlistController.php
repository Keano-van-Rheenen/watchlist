<?php

namespace App\Http\Controllers;

use App\Concerns\ManagesWatchables;
use App\Models\Movie;
use App\Models\Series;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class WatchlistController extends Controller
{
    use ManagesWatchables;

    /**
     * Display the unified watchlist index.
     */
    public function index(Request $request)
    {
        $user_id = Auth::id();
        $filterType = $request->query('type', 'both');
        $watchables = $this->getAllWatchablesForUser($user_id, false);

        return view('pages.watchlistPages.index', compact('watchables', 'filterType'));
    }

    /**
     * Reorder both movies and series.
     */
    public function reorder(Request $request): JsonResponse
    {
        $userId = (int) Auth::id();
        $orderedIds = $request->validate(['ordered_ids' => ['required', 'array', 'min:1']])['ordered_ids'];
        
        $movieCount = Movie::where('user_id', $userId)->where('seen', false)->whereIn('id', $orderedIds)->count();
        $seriesCount = Series::where('user_id', $userId)->where('seen', false)->whereIn('id', $orderedIds)->count();
        $totalUnseenMovies = Movie::where('user_id', $userId)->where('seen', false)->count();
        $totalUnseenSeries = Series::where('user_id', $userId)->where('seen', false)->count();
        $totalUnseen = $totalUnseenMovies + $totalUnseenSeries;

        abort_unless(
            ($movieCount + $seriesCount) === count($orderedIds) && $totalUnseen === count($orderedIds),
            403
        );

        DB::transaction(function () use ($orderedIds, $userId) {
            foreach ($orderedIds as $index => $id) {
                if (Movie::where('id', $id)->where('user_id', $userId)->where('seen', false)->exists()) {
                    Movie::where('id', $id)
                        ->where('user_id', $userId)
                        ->where('seen', false)
                        ->update(['hierarchy_index' => $index + 1]);
                } else {
                    Series::where('id', $id)
                        ->where('user_id', $userId)
                        ->where('seen', false)
                        ->update(['hierarchy_index' => $index + 1]);
                }
            }
        });

        return response()->json([
            'message' => 'Watchables reordered successfully.',
        ]);
    }
}
