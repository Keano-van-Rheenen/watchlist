<x-layouts::app :title="__('Index')">
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/css/index.css'])


    <div class="title">
        <h1>Watch List</h1>
    </div>

    @if (session('status'))
        <p class="mb-4 rounded border border-green-300 bg-green-50 p-3 text-green-700">{{ session('status') }}</p>
    @endif

    <div class="watchlist-container">
        @forelse ($watchables as $watchable)
            <article class="watchable-card">
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

                        <a href="{{ route('watchables.edit', $watchable) }}" class="btn btn-update">Update</a>

                        <form method="POST" action="{{ route('watchables.destroy', $watchable) }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-delete"
                                onclick="return confirm('Delete this watchable?')">Delete</button>
                        </form>
                    </div>

                    <div class="watchable-meta">
                        <h2>{{ $watchable->title }}</h2>
                        <small>Duration: {{ $watchable->duration }}</small>
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