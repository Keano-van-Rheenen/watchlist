<?php

namespace App\Http\Controllers;

use App\Concerns\ManagesWatchables;
use App\Http\Requests\MovieStoreRequest;
use App\Http\Requests\MovieUpdateRequest;
use App\Models\Movie;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class MovieController extends Controller
{
    use ManagesWatchables;

    private function authorizeOwner(Movie $movie): void
    {
        abort_unless((int) $movie->user_id === (int) Auth::id(), 403);
    }

    /**
     * Show the form for creating a new movie.
     */
    public function create()
    {
        return view('pages.watchlistPages.movies.new');
    }

    /**
     * Store a newly created movie in storage.
     */
    public function store(MovieStoreRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $nextHierarchyIndex = $this->getNextHierarchyIndexForUser((int) Auth::id(), false);

        $payload = [
            'user_id' => Auth::id(),
            'hierarchy_index' => $nextHierarchyIndex,
            'title' => $validated['title'],
            'summary' => $validated['summary'],
            'duration' => $validated['duration'] ?? null,
            'seen' => false,
        ];

        if ($request->hasFile('picture')) {
            $payload['picture'] = file_get_contents($request->file('picture')->getRealPath());
        }

        Movie::create($payload);

        return redirect()
            ->route('watchlist.index')
            ->with('status', 'Movie created successfully.');
    }

    /**
     * Show the form for editing the specified movie.
     */
    public function edit(Movie $movie)
    {
        $this->authorizeOwner($movie);
        $movie->type = 'movie';

        return view('pages.watchlistPages.movies.edit', compact('movie'));
    }

    /**
     * Update the specified movie in storage.
     */
    public function update(MovieUpdateRequest $request, Movie $movie): RedirectResponse
    {
        $this->authorizeOwner($movie);

        $validated = $request->validated();

        $payload = [
            'title' => $validated['title'],
            'summary' => $validated['summary'],
            'duration' => $validated['duration'] ?? null,
        ];

        if ($request->hasFile('picture')) {
            $payload['picture'] = file_get_contents($request->file('picture')->getRealPath());
        }

        $movie->update($payload);

        return redirect()
            ->route('watchlist.index')
            ->with('status', 'Movie updated successfully.');
    }

    /**
     * Remove the specified movie from storage.
     */
    public function destroy(Movie $movie): RedirectResponse
    {
        $this->authorizeOwner($movie);
        $wasSeen = (bool) $movie->seen;

        $movie->delete();

        if (! $wasSeen) {
            $this->resequenceHierarchyForUser((int) Auth::id(), false);
        }

        return redirect()->back()->with('status', 'Movie deleted successfully.');
    }

    /**
     * Mark the specified movie as seen.
     */
    public function seen(Movie $movie): RedirectResponse
    {
        $this->authorizeOwner($movie);

        $movie->update(['seen' => true]);
        $this->resequenceHierarchyForUser((int) Auth::id(), false);

        return redirect()->back()->with('status', 'Movie moved to Seen.');
    }
}
