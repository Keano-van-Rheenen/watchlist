<x-layouts::app :title="__('Index')">
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/css/index.css'])


    <div class="title">
        <h1>Watch List</h1>
        <p class="watchlist-subtitle">Drag cards to reorder your watchlist.</p>
        <p class="watchlist-reorder-status" data-watchlist-reorder-status aria-live="polite"></p>
    </div>

    <div class="mb-4">
        <form method="GET" class="flex gap-2 items-center">
            <label class="text-sm">Filter:</label>
            <select name="type" onchange="this.form.submit()" class="rounded border p-1 text-sm">
                <option value="both" {{ ($filterType ?? 'both') === 'both' ? 'selected' : '' }}>Both</option>
                <option value="movie" {{ ($filterType ?? '') === 'movie' ? 'selected' : '' }}>Movie</option>
                <option value="series" {{ ($filterType ?? '') === 'series' ? 'selected' : '' }}>Series</option>
            </select>
        </form>
    </div>

    <div class="mb-4 flex gap-2">
        <a href="{{ route('movies.create') }}" class="rounded bg-zinc-900 px-4 py-2 text-white">+ Add Movie</a>
        <a href="{{ route('series.create') }}" class="rounded bg-zinc-900 px-4 py-2 text-white">+ Add Series</a>
    </div>

    @if (session('status'))
        <p class="mb-4 rounded border border-green-300 bg-green-50 p-3 text-green-700">{{ session('status') }}</p>
    @endif

    <div class="watchlist-container" data-watchlist-sortable data-reorder-url="{{ route('watchlist.reorder') }}"
        data-csrf-token="{{ csrf_token() }}">

        @php
            $visible = ($filterType ?? 'both') === 'both' ? $watchables : $watchables->where('type', $filterType);
        @endphp

        @forelse ($visible as $watchable)
            <article class="watchable-card" data-watchable-id="{{ $watchable->id }}">
                <div class="watchable-card-head">
                    <span class="watchable-rank" data-watchable-rank>{{ $watchable->hierarchy_index }}</span>
                    <button type="button" class="drag-handle" data-drag-handle aria-label="Drag {{ $watchable->title }}">
                        Drag
                    </button>
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
            <p class="watchlist-empty">No watchables yet.</p>
        @endforelse
    </div>
</x-layouts::app>