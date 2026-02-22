<?php

namespace Tests\Feature;

use App\Models\EventNight;
use App\Models\Participant;
use App\Models\Song;
use App\Models\SongRequest;
use App\Models\Venue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicJoinTest extends TestCase
{
    use RefreshDatabase;

    public function test_landing_creates_participant_and_cookie(): void
    {
        $venue = Venue::create([
            'name' => 'Test Venue',
            'timezone' => 'UTC',
        ]);

        $eventNight = EventNight::create([
            'venue_id' => $venue->id,
            'code' => 'EVENT1',
            'break_seconds' => 0,
            'request_cooldown_seconds' => 0,
            'status' => EventNight::STATUS_ACTIVE,
            'starts_at' => now(),
        ]);

        Song::create([
            'title' => 'Song A',
            'artist' => 'Artist A',
            'duration_seconds' => 180,
        ]);

        $response = $this->get("/e/{$eventNight->code}");

        $response->assertStatus(200);
        $response->assertCookie(config('public_join.device_cookie_name', 'device_cookie_id'));

        $this->assertDatabaseCount('participants', 1);
        $this->assertNotEmpty(Participant::first()->join_token_hash);
    }

    public function test_request_enforces_join_token_and_cooldown(): void
    {
        $venue = Venue::create([
            'name' => 'Test Venue',
            'timezone' => 'UTC',
        ]);

        $eventNight = EventNight::create([
            'venue_id' => $venue->id,
            'code' => 'EVENT2',
            'break_seconds' => 0,
            'request_cooldown_seconds' => 300,
            'status' => EventNight::STATUS_ACTIVE,
            'starts_at' => now(),
        ]);

        $song = Song::create([
            'title' => 'Song B',
            'artist' => 'Artist B',
            'duration_seconds' => 200,
        ]);

        $joinToken = 'join-token-12345678';
        $participant = Participant::create([
            'event_night_id' => $eventNight->id,
            'device_cookie_id' => 'device-123',
            'join_token_hash' => hash('sha256', $joinToken),
        ]);

        $response = $this->withCookie(
            config('public_join.device_cookie_name', 'device_cookie_id'),
            $participant->device_cookie_id
        )->post("/e/{$eventNight->code}/request", [
            'song_id' => $song->id,
            'join_token' => $joinToken,
            'singer_name' => 'Mario',
        ]);

        $response->assertSessionHas('status');
        $this->assertDatabaseCount('song_requests', 1);

        $cooldownResponse = $this->from("/e/{$eventNight->code}")
            ->withCookie(config('public_join.device_cookie_name', 'device_cookie_id'), $participant->device_cookie_id)
            ->post("/e/{$eventNight->code}/request", [
                'song_id' => $song->id,
                'join_token' => $joinToken,
                'singer_name' => 'Mario',
            ]);

        $cooldownResponse->assertSessionHasErrors(['cooldown']);
        $this->assertSame(1, SongRequest::count());
    }

    public function test_rate_limit_blocks_spam(): void
    {
        config([
            'public_join.rate_limit_per_ip' => 1,
            'public_join.rate_limit_per_participant' => 1,
            'public_join.rate_limit_decay_seconds' => 60,
        ]);

        $venue = Venue::create([
            'name' => 'Test Venue',
            'timezone' => 'UTC',
        ]);

        $eventNight = EventNight::create([
            'venue_id' => $venue->id,
            'code' => 'EVENT3',
            'break_seconds' => 0,
            'request_cooldown_seconds' => 0,
            'status' => EventNight::STATUS_ACTIVE,
            'starts_at' => now(),
        ]);

        $participant = Participant::create([
            'event_night_id' => $eventNight->id,
            'device_cookie_id' => 'device-789',
            'join_token_hash' => hash('sha256', 'token-12345678'),
        ]);

        $first = $this->withCookie(
            config('public_join.device_cookie_name', 'device_cookie_id'),
            $participant->device_cookie_id
        )->post("/e/{$eventNight->code}/activate", [
            'pin' => null,
        ]);

        $first->assertStatus(302);

        $second = $this->withCookie(
            config('public_join.device_cookie_name', 'device_cookie_id'),
            $participant->device_cookie_id
        )->post("/e/{$eventNight->code}/activate", [
            'pin' => null,
        ]);

        $second->assertStatus(429);
    }

    public function test_request_requires_pin_activation_when_configured(): void
    {
        $venue = Venue::create([
            'name' => 'Test Venue',
            'timezone' => 'UTC',
        ]);

        $eventNight = EventNight::create([
            'venue_id' => $venue->id,
            'code' => 'EVENT4',
            'break_seconds' => 0,
            'request_cooldown_seconds' => 0,
            'status' => EventNight::STATUS_ACTIVE,
            'starts_at' => now(),
            'join_pin' => '4321',
        ]);

        $song = Song::create([
            'title' => 'Song C',
            'artist' => 'Artist C',
            'duration_seconds' => 180,
        ]);

        $joinToken = 'join-token-87654321';
        $participant = Participant::create([
            'event_night_id' => $eventNight->id,
            'device_cookie_id' => 'device-456',
            'join_token_hash' => hash('sha256', $joinToken),
        ]);

        $response = $this->from("/e/{$eventNight->code}")
            ->withCookie(config('public_join.device_cookie_name', 'device_cookie_id'), $participant->device_cookie_id)
            ->post("/e/{$eventNight->code}/request", [
                'song_id' => $song->id,
                'join_token' => $joinToken,
                'singer_name' => 'Mario',
            ]);

        $response->assertSessionHasErrors(['pin']);

        $activate = $this->withCookie(
            config('public_join.device_cookie_name', 'device_cookie_id'),
            $participant->device_cookie_id
        )->post("/e/{$eventNight->code}/activate", [
            'pin' => '4321',
        ]);

        $activate->assertSessionHas('status');

        $approved = $this->withCookie(
            config('public_join.device_cookie_name', 'device_cookie_id'),
            $participant->device_cookie_id
        )->post("/e/{$eventNight->code}/request", [
            'song_id' => $song->id,
            'join_token' => $joinToken,
            'singer_name' => 'Mario',
        ]);

        $approved->assertSessionHas('status');
        $this->assertDatabaseCount('song_requests', 1);
    }

    public function test_search_endpoint_returns_paginated_results(): void
    {
        $venue = Venue::create([
            'name' => 'Test Venue',
            'timezone' => 'UTC',
        ]);

        $eventNight = EventNight::create([
            'venue_id' => $venue->id,
            'code' => 'EVENT5',
            'break_seconds' => 0,
            'request_cooldown_seconds' => 0,
            'status' => EventNight::STATUS_ACTIVE,
            'starts_at' => now(),
        ]);

        Song::create([
            'title' => 'Hello World',
            'artist' => 'Tester',
            'duration_seconds' => 180,
        ]);

        Song::create([
            'title' => 'Goodbye',
            'artist' => 'Another',
            'duration_seconds' => 200,
        ]);

        $response = $this->getJson("/e/{$eventNight->code}/songs?q=hello&per_page=1");

        $response->assertOk()
            ->assertJsonPath('meta.per_page', 1)
            ->assertJsonCount(1, 'data');
    }

    public function test_duplicate_song_requests_are_blocked(): void
    {
        $venue = Venue::create([
            'name' => 'Test Venue',
            'timezone' => 'UTC',
        ]);

        $eventNight = EventNight::create([
            'venue_id' => $venue->id,
            'code' => 'EVENT6',
            'break_seconds' => 0,
            'request_cooldown_seconds' => 0,
            'status' => EventNight::STATUS_ACTIVE,
            'starts_at' => now(),
        ]);

        $song = Song::create([
            'title' => 'Repeat',
            'artist' => 'Artist',
            'duration_seconds' => 200,
        ]);

        $joinToken = 'join-token-12345678';
        $participant = Participant::create([
            'event_night_id' => $eventNight->id,
            'device_cookie_id' => 'device-456',
            'join_token_hash' => hash('sha256', $joinToken),
        ]);

        $this->withCookie(
            config('public_join.device_cookie_name', 'device_cookie_id'),
            $participant->device_cookie_id
        )->post("/e/{$eventNight->code}/request", [
            'song_id' => $song->id,
            'join_token' => $joinToken,
            'singer_name' => 'Mario',
        ])->assertSessionHas('status');

        $response = $this->from("/e/{$eventNight->code}")
            ->withCookie(config('public_join.device_cookie_name', 'device_cookie_id'), $participant->device_cookie_id)
            ->post("/e/{$eventNight->code}/request", [
                'song_id' => $song->id,
                'join_token' => $joinToken,
                'singer_name' => 'Mario',
            ]);

        $response->assertSessionHasErrors(['song_id']);
        $this->assertSame(1, SongRequest::count());
    }

    public function test_my_requests_endpoint_returns_participant_requests_summary(): void
    {
        $venue = Venue::create([
            'name' => 'Test Venue',
            'timezone' => 'UTC',
        ]);

        $eventNight = EventNight::create([
            'venue_id' => $venue->id,
            'code' => 'EVENT7',
            'break_seconds' => 0,
            'request_cooldown_seconds' => 0,
            'status' => EventNight::STATUS_ACTIVE,
            'starts_at' => now(),
        ]);

        $song = Song::create([
            'title' => 'My Song',
            'artist' => 'My Artist',
            'duration_seconds' => 210,
        ]);

        $joinToken = 'join-token-23456789';
        $participant = Participant::create([
            'event_night_id' => $eventNight->id,
            'device_cookie_id' => 'device-my-requests',
            'join_token_hash' => hash('sha256', $joinToken),
            'display_name' => 'Marco',
        ]);

        SongRequest::create([
            'event_night_id' => $eventNight->id,
            'participant_id' => $participant->id,
            'song_id' => $song->id,
            'status' => SongRequest::STATUS_QUEUED,
            'position' => 1,
        ]);

        $response = $this->withCookie(
            config('public_join.device_cookie_name', 'device_cookie_id'),
            $participant->device_cookie_id
        )->get("/e/{$eventNight->code}/my-requests?join_token={$joinToken}", ['Accept' => 'application/json']);

        $response->assertOk()
            ->assertJsonPath('data.0.song_title', 'My Song')
            ->assertJsonPath('data.0.status_label', 'In coda');
    }
}
