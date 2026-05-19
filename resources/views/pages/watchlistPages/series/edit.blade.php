<x-layouts::app :title="__('Edit Series')">
	@vite(['resources/css/app.css', 'resources/js/app.js'])

	<div class="mx-auto w-full max-w-2xl p-6">
		<h1 class="mb-6 text-2xl font-semibold">Update Series</h1>

		@if ($errors->any())
			<div class="mb-4 rounded border border-red-300 bg-red-50 p-3 text-red-700">
				<ul class="list-disc ps-5">
					@foreach ($errors->all() as $error)
						<li>{{ $error }}</li>
					@endforeach
				</ul>
			</div>
		@endif

		<form method="POST" action="{{ route('series.update', $series) }}" enctype="multipart/form-data" class="space-y-4">
			@csrf
			@method('PUT')

			<div>
				<label for="title" class="mb-1 block text-sm font-medium">Title</label>
				<input id="title" name="title" type="text" value="{{ old('title', $series->title) }}" required
					class="w-full rounded border border-zinc-300 p-2 text-zinc-900" />
			</div>

			<div>
				<label for="summary" class="mb-1 block text-sm font-medium">Summary</label>
				<textarea id="summary" name="summary" rows="4" required
					class="w-full rounded border border-zinc-300 p-2 text-zinc-900">{{ old('summary', $series->summary) }}</textarea>
			</div>

			<div>
				<label for="episodes" class="mb-1 block text-sm font-medium">Episodes</label>
				<input id="episodes" name="episodes" type="number" value="{{ old('episodes', $series->episodes) }}" min="1" required
					class="w-full rounded border border-zinc-300 p-2 text-zinc-900" />
			</div>

			<div>
				<label for="picture" class="mb-1 block text-sm font-medium">Replace Picture (optional)</label>
				<input id="picture" name="picture" type="file" accept="image/*" class="block w-full text-sm" />
			</div>

			<div class="flex gap-3">
				<button type="submit" class="rounded bg-zinc-900 px-4 py-2 text-white">Save</button>
				<a href="{{ route('watchlist.index') }}" class="rounded border border-zinc-300 px-4 py-2">Cancel</a>
			</div>
		</form>
	</div>
</x-layouts::app>
