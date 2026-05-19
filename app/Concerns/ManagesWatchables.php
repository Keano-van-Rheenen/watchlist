<?php

namespace App\Concerns;

use App\Models\Movie;
use App\Models\Series;
use Illuminate\Database\Eloquent\Collection;

trait ManagesWatchables
{
    private function getAllWatchablesForUser(int $userId): Collection
    {
        $movies = Movie::where('user_id', $userId)
            ->orderBy('hierarchy_index')
            ->orderBy('updated_at', 'desc')
            ->get()
            ->map(fn ($m) => [...$m->toArray(), 'type' => 'movie']);

        $series = Series::where('user_id', $userId)
            ->orderBy('hierarchy_index')
            ->orderBy('updated_at', 'desc')
            ->get()
            ->map(fn ($s) => [...$s->toArray(), 'type' => 'series']);

        return collect(array_merge($movies->toArray(), $series->toArray()))
            ->sortBy('hierarchy_index')
            ->sortByDesc('updated_at')
            ->values();
    }

    private function resequenceHierarchyForUser(int $userId): void
    {
        $movies = Movie::where('user_id', $userId)
            ->orderBy('hierarchy_index')
            ->orderBy('updated_at', 'desc')
            ->orderBy('id')
            ->get(['id']);

        $series = Series::where('user_id', $userId)
            ->orderBy('hierarchy_index')
            ->orderBy('updated_at', 'desc')
            ->orderBy('id')
            ->get(['id']);

        $allWatchables = $movies->merge($series)
            ->sortBy('updated_at', descending: true)
            ->values();

        foreach ($allWatchables as $index => $watchable) {
            $modelClass = Movie::find($watchable->id) ? Movie::class : Series::class;
            $modelClass::where('id', $watchable->id)->update([
                'hierarchy_index' => $index + 1,
            ]);
        }
    }

    private function findWatchable(string $id)
    {
        return Movie::find($id) ?? Series::find($id);
    }
}
