<?php

namespace App\Http\Controllers;

use App\Http\Requests\SeriesStoreRequest;
use App\Http\Requests\SeriesUpdateRequest;
use App\Models\Series;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class SeriesController extends Controller
{
    private function authorizeOwner(Series $series): void
    {
        abort_unless((int) $series->user_id === (int) Auth::id(), 403);
    }

    /**
     * Show the form for creating a new series.
     */
    public function create()
    {
        return view('pages.watchlistPages.series.new');
    }

    /**
     * Store a newly created series in storage.
     */
    public function store(SeriesStoreRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $nextHierarchyIndex = (Series::where('user_id', Auth::id())->max('hierarchy_index') ?? 0) + 1;

        $payload = [
            'user_id' => Auth::id(),
            'hierarchy_index' => $nextHierarchyIndex,
            'title' => $validated['title'],
            'summary' => $validated['summary'],
            'episodes' => $validated['episodes'] ?? null,
        ];

        if ($request->hasFile('picture')) {
            $payload['picture'] = file_get_contents($request->file('picture')->getRealPath());
        }

        Series::create($payload);

        return redirect()
            ->route('watchlist.index')
            ->with('status', 'Series created successfully.');
    }

    /**
     * Show the form for editing the specified series.
     */
    public function edit(Series $series)
    {
        $this->authorizeOwner($series);
        $series->type = 'series';

        return view('pages.watchlistPages.series.edit', compact('series'));
    }

    /**
     * Update the specified series in storage.
     */
    public function update(SeriesUpdateRequest $request, Series $series): RedirectResponse
    {
        $this->authorizeOwner($series);

        $validated = $request->validated();

        $payload = [
            'title' => $validated['title'],
            'summary' => $validated['summary'],
            'episodes' => $validated['episodes'] ?? null,
        ];

        if ($request->hasFile('picture')) {
            $payload['picture'] = file_get_contents($request->file('picture')->getRealPath());
        }

        $series->update($payload);

        return redirect()
            ->route('watchlist.index')
            ->with('status', 'Series updated successfully.');
    }

    /**
     * Remove the specified series from storage.
     */
    public function destroy(Series $series): RedirectResponse
    {
        $this->authorizeOwner($series);

        $series->delete();

        $this->resequenceHierarchyForUser((int) Auth::id());

        return redirect()
            ->route('watchlist.index')
            ->with('status', 'Series deleted successfully.');
    }

    private function resequenceHierarchyForUser(int $userId): void
    {
        $allSeries = Series::where('user_id', $userId)
            ->orderBy('hierarchy_index')
            ->orderBy('updated_at', 'desc')
            ->orderBy('id')
            ->get(['id']);

        foreach ($allSeries as $index => $series) {
            Series::where('id', $series->id)->update(['hierarchy_index' => $index + 1]);
        }
    }
}
