<?php

namespace App\Http\Controllers;

use App\Concerns\ManagesWatchables;
use App\Models\CommunityWatchable;
use App\Models\CommunityWatchableVote;
use App\Models\Movie;
use App\Models\Series;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommunityController extends Controller
{
    use ManagesWatchables;

    public function index()
    {
        $userId = (int) Auth::id();

        $watchables = CommunityWatchable::query()
            ->with('uploader')
            ->withCount('votes')
            ->orderBy('hierarchy_index')
            ->orderBy('created_at')
            ->get()
            ->map(function (CommunityWatchable $watchable) use ($userId) {
                $this->prepareWatchable($watchable, $watchable->kind);
                $watchable->has_voted = CommunityWatchableVote::query()
                    ->where('community_watchable_id', $watchable->id)
                    ->where('user_id', $userId)
                    ->exists();

                return $watchable;
            });

        return view('pages.communityPages.index', compact('watchables'));
    }

    public function upload(Request $request): RedirectResponse
    {
        $payload = $request->validate([
            'watchable_type' => ['required', 'in:movie,series'],
            'watchable_id' => ['required', 'uuid'],
        ]);

        $watchable = $this->resolveUserWatchable($payload['watchable_type'], $payload['watchable_id']);

        CommunityWatchable::create([
            'uploader_user_id' => Auth::id(),
            'hierarchy_index' => 0,
            'kind' => $payload['watchable_type'],
            'title' => $watchable->title,
            'summary' => $watchable->summary,
            'duration' => $watchable->duration ?? null,
            'episodes' => $watchable->episodes ?? null,
            'picture' => $watchable->picture,
        ]);

        $this->resequenceCommunityHierarchy();

        return redirect()->back()->with('status', 'Watchable uploaded to the community.');
    }

    public function copy(CommunityWatchable $communityWatchable): RedirectResponse
    {
        $userId = (int) Auth::id();

        Movie::query()->where('user_id', $userId)->where('seen', false)->increment('hierarchy_index');
        Series::query()->where('user_id', $userId)->where('seen', false)->increment('hierarchy_index');

        if ($communityWatchable->kind === 'movie') {
            Movie::create([
                'user_id' => $userId,
                'hierarchy_index' => 1,
                'title' => $communityWatchable->title,
                'summary' => $communityWatchable->summary,
                'duration' => $communityWatchable->duration,
                'picture' => $communityWatchable->picture,
                'seen' => false,
            ]);
        } else {
            Series::create([
                'user_id' => $userId,
                'hierarchy_index' => 1,
                'title' => $communityWatchable->title,
                'summary' => $communityWatchable->summary,
                'episodes' => $communityWatchable->episodes,
                'picture' => $communityWatchable->picture,
                'seen' => false,
            ]);
        }

        return redirect()->back()->with('status', 'Watchable copied to your watchlist.');
    }

    public function upvote(CommunityWatchable $communityWatchable): RedirectResponse
    {
        $vote = CommunityWatchableVote::query()->firstOrCreate([
            'community_watchable_id' => $communityWatchable->id,
            'user_id' => Auth::id(),
        ]);

        if ($vote->wasRecentlyCreated) {
            $this->resequenceCommunityHierarchy();
        }

        return redirect()->back()->with('status', $vote->wasRecentlyCreated
            ? 'Your vote was recorded.'
            : 'You already voted for this watchable.');
    }

    public function destroy(CommunityWatchable $communityWatchable): RedirectResponse
    {
        abort_unless(Auth::user()?->isAdmin(), 403);

        $communityWatchable->delete();
        $this->resequenceCommunityHierarchy();

        return redirect()->back()->with('status', 'Community watchable deleted.');
    }

    private function resolveUserWatchable(string $type, string $watchableId)
    {
        $query = $type === 'movie' ? Movie::query() : Series::query();

        return $query
            ->whereKey($watchableId)
            ->where('user_id', Auth::id())
            ->where('seen', true)
            ->firstOrFail();
    }

    private function resequenceCommunityHierarchy(): void
    {
        $communityWatchables = CommunityWatchable::query()
            ->withCount('votes')
            ->orderByDesc('votes_count')
            ->orderBy('created_at')
            ->orderBy('id')
            ->get();

        foreach ($communityWatchables as $index => $communityWatchable) {
            $communityWatchable->update([
                'hierarchy_index' => $index + 1,
            ]);
        }
    }
}