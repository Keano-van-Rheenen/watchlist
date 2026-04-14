<?php

namespace App\Http\Controllers;

use App\Models\Watchable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WatchableController extends Controller
{
    private function authorizeOwner(Watchable $watchable): void
    {
        abort_unless((int) $watchable->user_id === (int) Auth::id(), 403);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user_id = Auth::id();
        $watchables = Watchable::where('user_id', $user_id)
            ->orderBy('updated_at', 'desc')
            ->get()
            ->map(function (Watchable $watchable) {
                $watchable->picture_src = null;

                if (! empty($watchable->picture)) {
                    $mimeType = (new \finfo(FILEINFO_MIME_TYPE))->buffer($watchable->picture) ?: 'image/jpeg';

                    if (! str_starts_with($mimeType, 'image/')) {
                        $mimeType = 'image/jpeg';
                    }

                    $watchable->picture_src = 'data:'.$mimeType.';base64,'.base64_encode($watchable->picture);
                }

                return $watchable;
            });

        return view('pages.watchlistPages.index', compact('watchables'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('pages.watchlistPages.new');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'summary' => ['required', 'string'],
            'duration' => ['required', 'regex:/^\d{1,2}:\d{2}(:\d{2})?$/'],
            'picture' => ['required', 'image', 'max:5120'],
        ]);

        $duration = strlen($validated['duration']) === 5
            ? $validated['duration'].':00'
            : $validated['duration'];

        Watchable::create([
            'user_id' => Auth::id(),
            'title' => $validated['title'],
            'summary' => $validated['summary'],
            'duration' => $duration,
            'picture' => file_get_contents($request->file('picture')->getRealPath()),
        ]);

        return redirect()
            ->route('index')
            ->with('status', 'Watchable created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Watchable $watchable)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Watchable $watchable)
    {
        $this->authorizeOwner($watchable);

        return view('pages.watchlistPages.edit', compact('watchable'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Watchable $watchable): RedirectResponse
    {
        $this->authorizeOwner($watchable);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'summary' => ['required', 'string'],
            'duration' => ['required', 'regex:/^\d{1,2}:\d{2}(:\d{2})?$/'],
            'picture' => ['nullable', 'image', 'max:5120'],
        ]);

        $duration = strlen($validated['duration']) === 5
            ? $validated['duration'].':00'
            : $validated['duration'];

        $payload = [
            'title' => $validated['title'],
            'summary' => $validated['summary'],
            'duration' => $duration,
        ];

        if ($request->hasFile('picture')) {
            $payload['picture'] = file_get_contents($request->file('picture')->getRealPath());
        }

        $watchable->update($payload);

        return redirect()
            ->route('index')
            ->with('status', 'Watchable updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Watchable $watchable): RedirectResponse
    {
        $this->authorizeOwner($watchable);

        $watchable->delete();

        return redirect()
            ->route('index')
            ->with('status', 'Watchable deleted successfully.');
    }
}
