<x-layouts::app :title="__('Community')">
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/css/index.css'])

    <div class="title">
        <h1>Community</h1>
        <p class="watchlist-subtitle">Discover uploads from other watchlists.</p>
    </div>

    @if (session('status'))
        <p class="mb-4 rounded border border-green-300 bg-green-50 p-3 text-green-700">{{ session('status') }}</p>
    @endif

    <div class="community-watchlist-container">
        @forelse ($watchables as $watchable)
            <article class="watchable-card community-watchable-card" data-watchable-id="{{ $watchable->id }}">
                <div class="watchable-card-head">
                    <span class="watchable-rank" data-watchable-rank>{{ $watchable->hierarchy_index }}</span>
                    <small class="watchable-type uppercase font-medium text-xs">{{ ucfirst($watchable->kind) }}</small>
                </div>

                <div class="watchable-image-wrap">
                    @if ($watchable->picture_src)
                        <img src="{{ $watchable->picture_src }}" alt="{{ $watchable->title }} picture" class="watchable-image">
                    @else
                        <div class="watchable-image-placeholder">No image</div>
                    @endif
                </div>

                <div class="watchable-content">
                    <div class="watchable-actions">
                        <button type="button" class="btn btn-summary"
                            onclick="document.getElementById('summary-{{ $watchable->id }}').showModal()">Summary</button>

                        <form method="POST" action="{{ route('community.copy', $watchable->id) }}">
                            @csrf
                            <button type="submit" class="btn btn-copy">Copy</button>
                        </form>

                        <form method="POST" action="{{ route('community.upvote', $watchable->id) }}">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-vote vote-pill" @disabled($watchable->has_voted)>
                                Upvote <span class="vote-count">{{ $watchable->votes_count }}</span>
                            </button>
                        </form>

                        @if (auth()->user()->isAdmin())
                            <form method="POST" action="{{ route('community.destroy', $watchable->id) }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-delete"
                                    onclick="return confirm('Delete this community watchable?')">Delete</button>
                            </form>
                        @endif
                    </div>

                    <div class="watchable-meta">
                        <h2>{{ $watchable->title }}</h2>
                        <div class="flex items-center gap-2 flex-wrap justify-center">
                            <small class="watchable-uploader">Uploaded by {{ $watchable->uploader?->name ?? 'Unknown' }}</small>
                            <small>
                                @if ($watchable->kind === 'series')
                                    Episodes: {{ $watchable->episodes ?? '—' }}
                                @else
                                    Duration: {{ $watchable->duration ?? '—' }}
                                @endif
                            </small>
                        </div>
                    </div>
                </div>

                <dialog id="summary-{{ $watchable->id }}" class="summary-dialog">
                    <div class="summary-dialog-card">
                        <h3>{{ $watchable->title }} Summary</h3>
                        <p class="summary-text">{{ $watchable->summary }}</p>

                        <form method="dialog" class="summary-close-wrap">
                            <button type="submit" class="btn btn-update">Close</button>
                        </form>
                    </div>
                </dialog>
            </article>
        @empty
            <p class="watchlist-empty">No community watchables yet.</p>
        @endforelse
    </div>
</x-layouts::app>