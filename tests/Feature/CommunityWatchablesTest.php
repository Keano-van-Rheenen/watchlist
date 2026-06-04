<?php

use App\Models\CommunityWatchable;
use App\Models\Movie;
use App\Models\Series;
use App\Models\User;
use Illuminate\Http\UploadedFile;

test('users can upload seen watchables to the community', function () {
    $user = User::factory()->create();
    $movie = Movie::create([
        'user_id' => $user->id,
        'hierarchy_index' => 1,
        'title' => 'Community Test Movie',
        'summary' => 'A test summary.',
        'duration' => '01:45:00',
        'picture' => UploadedFile::fake()->image('poster.jpg')->getContent(),
        'seen' => true,
    ]);

    $response = $this->actingAs($user)->post(route('community.upload'), [
        'watchable_type' => 'movie',
        'watchable_id' => $movie->id,
    ]);

    $response->assertRedirect();

    $this->assertDatabaseHas('community_watchables', [
        'uploader_user_id' => $user->id,
        'kind' => 'movie',
        'title' => 'Community Test Movie',
    ]);
});

test('users can copy a community watchable to the top of their watchlist', function () {
    $user = User::factory()->create();

    Movie::create([
        'user_id' => $user->id,
        'hierarchy_index' => 1,
        'title' => 'Existing Movie',
        'summary' => 'Existing movie summary.',
        'duration' => '00:45:00',
        'picture' => UploadedFile::fake()->image('existing.jpg')->getContent(),
        'seen' => false,
    ]);

    Series::create([
        'user_id' => $user->id,
        'hierarchy_index' => 2,
        'title' => 'Existing Series',
        'summary' => 'Existing series summary.',
        'episodes' => 8,
        'picture' => UploadedFile::fake()->image('existing-series.jpg')->getContent(),
        'seen' => false,
    ]);

    $communityWatchable = CommunityWatchable::create([
        'uploader_user_id' => $user->id,
        'hierarchy_index' => 1,
        'kind' => 'movie',
        'title' => 'Copied Movie',
        'summary' => 'Copied summary.',
        'duration' => '01:20:00',
        'picture' => UploadedFile::fake()->image('community.jpg')->getContent(),
    ]);

    $response = $this->actingAs($user)->post(route('community.copy', $communityWatchable));

    $response->assertRedirect();

    $this->assertDatabaseHas('movies', [
        'user_id' => $user->id,
        'hierarchy_index' => 1,
        'title' => 'Copied Movie',
    ]);

    $this->assertDatabaseHas('movies', [
        'user_id' => $user->id,
        'title' => 'Existing Movie',
        'hierarchy_index' => 2,
    ]);
});

test('users can only upvote a community watchable once and admins can delete it', function () {
    $user = User::factory()->create();
    $admin = User::factory()->admin()->create();

    $communityWatchable = CommunityWatchable::create([
        'uploader_user_id' => $user->id,
        'hierarchy_index' => 1,
        'kind' => 'series',
        'title' => 'Vote Test',
        'summary' => 'Vote summary.',
        'episodes' => 4,
        'picture' => UploadedFile::fake()->image('vote.jpg')->getContent(),
    ]);

    $response = $this->actingAs($user)->patch(route('community.upvote', $communityWatchable));

    $response->assertRedirect();

    $this->actingAs($user)->patch(route('community.upvote', $communityWatchable));

    $this->assertDatabaseCount('community_watchable_votes', 1);

    $this->actingAs($admin)->delete(route('community.destroy', $communityWatchable));

    $this->assertDatabaseMissing('community_watchables', [
        'id' => $communityWatchable->id,
    ]);
});