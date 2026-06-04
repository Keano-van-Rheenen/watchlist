<?php

namespace App\Concerns;

use App\Models\Movie;
use App\Models\Series;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

trait ManagesWatchables
{
    protected function applySeenFilter($query, ?bool $seen)
    {
        return match ($seen) {
            true => $query->where('seen', true),
            false => $query->where(function ($query) {
                $query->where('seen', false)
                    ->orWhereNull('seen');
            }),
            default => $query,
        };
    }

    protected function getNextHierarchyIndexForUser(int $userId, ?bool $seen = null): int
    {
        $movieMax = $this->applySeenFilter(
            Movie::query()->where('user_id', $userId),
            $seen
        )
            ->max('hierarchy_index') ?? 0;

        $seriesMax = $this->applySeenFilter(
            Series::query()->where('user_id', $userId),
            $seen
        )
            ->max('hierarchy_index') ?? 0;

        return max($movieMax, $seriesMax) + 1;
    }

    protected function getAllWatchablesForUser(int $userId, ?bool $seen = null): Collection
    {
        $movies = $this->applySeenFilter(
            Movie::query()->where('user_id', $userId),
            $seen
        )
            ->orderBy('hierarchy_index')
            ->orderBy('updated_at', 'desc')
            ->get()
            ->map(fn (Movie $movie) => $this->prepareWatchable($movie, 'movie'));

        $series = $this->applySeenFilter(
            Series::query()->where('user_id', $userId),
            $seen
        )
            ->orderBy('hierarchy_index')
            ->orderBy('updated_at', 'desc')
            ->get()
            ->map(fn (Series $series) => $this->prepareWatchable($series, 'series'));

        return $movies->merge($series)
            ->sortBy('hierarchy_index')
            ->values();
    }

    protected function resequenceHierarchyForUser(int $userId, ?bool $seen = null): void
    {
        $movies = $this->applySeenFilter(
            Movie::query()->where('user_id', $userId),
            $seen
        )
            ->orderBy('hierarchy_index')
            ->orderBy('updated_at', 'desc')
            ->orderBy('id')
            ->get(['id']);

        $series = $this->applySeenFilter(
            Series::query()->where('user_id', $userId),
            $seen
        )
            ->orderBy('hierarchy_index')
            ->orderBy('updated_at', 'desc')
            ->orderBy('id')
            ->get(['id']);

        $allWatchables = $movies->merge($series)
            ->sortBy('hierarchy_index')
            ->values();

        foreach ($allWatchables as $index => $watchable) {
            $modelClass = Movie::find($watchable->id) ? Movie::class : Series::class;
            $modelClass::where('id', $watchable->id)->update([
                'hierarchy_index' => $index + 1,
            ]);
        }
    }

    protected function prepareWatchable(Model $watchable, string $type): Model
    {
        $watchable->type = $type;
        $watchable->picture_src = null;

        if (! empty($watchable->picture)) {
            $mimeType = (new \finfo(FILEINFO_MIME_TYPE))->buffer($watchable->picture) ?: 'image/jpeg';

            if (! str_starts_with($mimeType, 'image/')) {
                $mimeType = 'image/jpeg';
            }

            $watchable->picture_src = 'data:' . $mimeType . ';base64,' . base64_encode($watchable->picture);
        }

        return $watchable;
    }
}
