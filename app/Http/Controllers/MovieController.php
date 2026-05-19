<?php

namespace App\Http\Controllers;

use App\Http\Requests\MovieStoreRequest;
use App\Http\Requests\MovieUpdateRequest;
use App\Models\Movie;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class MovieController extends Controller
{
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
        $nextHierarchyIndex = (Movie::where('user_id', Auth::id())->max('hierarchy_index') ?? 0) + 1;

        $payload = [
            'user_id' => Auth::id(),
            'hierarchy_index' => $nextHierarchyIndex,
            'title' => $validated['title'],
            'summary' => $validated['summary'],
            'duration' => $validated['duration'] ?? null,
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

        $movie->delete();

        $this->resequenceHierarchyForUser((int) Auth::id());

        return redirect()
            ->route('watchlist.index')
            ->with('status', 'Movie deleted successfully.');
    }

    private function resequenceHierarchyForUser(int $userId): void
    {
        $allMovies = Movie::where('user_id', $userId)
            ->orderBy('hierarchy_index')
            ->orderBy('updated_at', 'desc')
            ->orderBy('id')
            ->get(['id']);

        foreach ($allMovies as $index => $movie) {
            Movie::where('id', $movie->id)->update(['hierarchy_index' => $index + 1]);
        }
    }
}
