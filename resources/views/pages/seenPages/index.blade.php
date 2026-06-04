<x-layouts::app :title="__('Seen')">
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/css/index.css'])

    <div class="title">
        <h1>Seen</h1>
        <p class="watchlist-subtitle">Everything you have already watched.</p>
    </div>

    @if (session('status'))
        <p class="mb-4 rounded border border-green-300 bg-green-50 p-3 text-green-700">{{ session('status') }}</p>
    @endif

    <div class="seen-watchlist-container">
        @forelse ($watchables as $watchable)
            <article class="watchable-card seen-watchable-card" data-watchable-id="{{ $watchable->id }}">
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

                        @if ($watchable->type === 'movie')
                            <a href="{{ route('movies.edit', $watchable->id) }}" class="btn btn-update">Update</a>

                            <form method="POST" action="{{ route('movies.destroy', $watchable->id) }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-delete"
                                    onclick="return confirm('Delete this watchable?')">Delete</button>
                            </form>
                        @else
                            <a href="{{ route('series.edit', $watchable->id) }}" class="btn btn-update">Update</a>

                            <form method="POST" action="{{ route('series.destroy', $watchable->id) }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-delete"
                                    onclick="return confirm('Delete this watchable?')">Delete</button>
                            </form>
                        @endif
                    </div>

                    <div class="watchable-meta">
                        <h2>{{ $watchable->title }}</h2>
                        <div class="flex items-center gap-2">
                            <small class="watchable-type uppercase font-medium text-xs">{{ ucfirst($watchable->type) }}</small>
                            @if ($watchable->type === 'series')
                                <small>Episodes: {{ $watchable->episodes ?? '—' }}</small>
                            @else
                                <small>Duration: {{ $watchable->duration ?? '—' }}</small>
                            @endif
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
            <p class="watchlist-empty">No seen watchables yet.</p>
        @endforelse
    </div>
</x-layouts::app>