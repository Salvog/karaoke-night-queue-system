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

    public function test_public_entry_redirects_to_active_event_when_available(): void
    {
        $venue = Venue::create([
            'name' => 'Main Venue',
            'timezone' => 'UTC',
        ]);

        $eventNight = EventNight::create([
            'venue_id' => $venue->id,
            'code' => 'ACTIVE99',
            'break_seconds' => 0,
            'request_cooldown_seconds' => 0,
            'status' => EventNight::STATUS_ACTIVE,
            'starts_at' => now(),
        ]);

        $response = $this->get('/public');

        $response->assertRedirect(route('public.join.show', $eventNight->code));
    }

    public function test_public_entry_does_not_redirect_when_active_event_has_not_started_yet(): void
    {
        $venue = Venue::create([
            'name' => 'Main Venue',
            'timezone' => 'UTC',
        ]);

        EventNight::create([
            'venue_id' => $venue->id,
            'code' => 'FUTURE01',
            'break_seconds' => 0,
            'request_cooldown_seconds' => 0,
            'status' => EventNight::STATUS_ACTIVE,
            'starts_at' => now()->addHour(),
        ]);

        $response = $this->get('/public');

        $response->assertOk();
        $response->assertSee('Codice evento');
        $response->assertSee('Partecipa');
    }

    public function test_public_entry_redirects_to_started_active_event_when_future_active_event_exists(): void
    {
        $venue = Venue::create([
            'name' => 'Main Venue',
            'timezone' => 'UTC',
        ]);

        $startedEvent = EventNight::create([
            'venue_id' => $venue->id,
            'code' => 'STARTED1',
            'break_seconds' => 0,
            'request_cooldown_seconds' => 0,
            'status' => EventNight::STATUS_ACTIVE,
            'starts_at' => now()->subHour(),
        ]);

        EventNight::create([
            'venue_id' => $venue->id,
            'code' => 'FUTURE02',
            'break_seconds' => 0,
            'request_cooldown_seconds' => 0,
            'status' => EventNight::STATUS_ACTIVE,
            'starts_at' => now()->addHour(),
        ]);

        $response = $this->get('/public');

        $response->assertRedirect(route('public.join.show', $startedEvent->code));
    }

    public function test_public_entry_shows_code_input_when_no_active_event(): void
    {
        $venue = Venue::create([
            'name' => 'Main Venue',
            'timezone' => 'UTC',
        ]);

        EventNight::create([
            'venue_id' => $venue->id,
            'code' => 'DRAFT11',
            'break_seconds' => 0,
            'request_cooldown_seconds' => 0,
            'status' => EventNight::STATUS_DRAFT,
            'starts_at' => now(),
        ]);

        $response = $this->get('/public');

        $response->assertOk();
        $response->assertSee('Codice evento');
        $response->assertSee('Partecipa');
        $response->assertDontSee('Schermo pubblico');
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
            'display_name' => 'Mario',
        ]);

        $response->assertRedirect(route('public.join.show', $eventNight->code));
        $response->assertSessionHas('status');
        $this->assertDatabaseCount('song_requests', 1);
        $this->assertDatabaseHas('participants', [
            'id' => $participant->id,
            'display_name' => 'Mario',
        ]);

        $cooldownResponse = $this->from("/e/{$eventNight->code}")
            ->withCookie(config('public_join.device_cookie_name', 'device_cookie_id'), $participant->device_cookie_id)
            ->post("/e/{$eventNight->code}/request", [
                'song_id' => $song->id,
                'join_token' => $joinToken,
                'display_name' => 'Mario',
            ]);

        $cooldownResponse->assertSessionHasErrors(['cooldown']);
        $this->assertSame(1, SongRequest::count());
    }

    public function test_request_requires_display_name(): void
    {
        $venue = Venue::create([
            'name' => 'Test Venue',
            'timezone' => 'UTC',
        ]);

        $eventNight = EventNight::create([
            'venue_id' => $venue->id,
            'code' => 'EVENTX',
            'break_seconds' => 0,
            'request_cooldown_seconds' => 0,
            'status' => EventNight::STATUS_ACTIVE,
            'starts_at' => now(),
        ]);

        $song = Song::create([
            'title' => 'Song Missing Name',
            'artist' => 'Artist',
            'duration_seconds' => 190,
        ]);

        $joinToken = 'join-token-name-required';
        $participant = Participant::create([
            'event_night_id' => $eventNight->id,
            'device_cookie_id' => 'device-name-required',
            'join_token_hash' => hash('sha256', $joinToken),
        ]);

        $response = $this->from("/e/{$eventNight->code}")
            ->withCookie(config('public_join.device_cookie_name', 'device_cookie_id'), $participant->device_cookie_id)
            ->post("/e/{$eventNight->code}/request", [
                'song_id' => $song->id,
                'join_token' => $joinToken,
            ]);

        $response->assertSessionHasErrors(['display_name']);
        $this->assertSame(0, SongRequest::count());
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

        $second->assertRedirect(route('public.join.show', $eventNight->code));
        $second->assertSessionHasErrors(['rate_limit']);
    }

    public function test_rate_limit_returns_json_429_for_api_requests(): void
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
            'code' => 'EVENT3API',
            'break_seconds' => 0,
            'request_cooldown_seconds' => 0,
            'status' => EventNight::STATUS_ACTIVE,
            'starts_at' => now(),
        ]);

        $first = $this->getJson("/e/{$eventNight->code}/eta?join_token=abcdefgh");
        $first->assertStatus(403);

        $second = $this->getJson("/e/{$eventNight->code}/eta?join_token=abcdefgh");
        $second->assertStatus(429)
            ->assertJsonPath('message', 'Troppe richieste in poco tempo. Attendi qualche secondo e riprova.');
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
                'display_name' => 'Lucia',
            ]);

        $response->assertSessionHasErrors(['pin']);

        $activate = $this->withCookie(
            config('public_join.device_cookie_name', 'device_cookie_id'),
            $participant->device_cookie_id
        )->post("/e/{$eventNight->code}/activate", [
            'pin' => '4321',
        ]);

        $activate->assertRedirect(route('public.join.show', $eventNight->code));
        $activate->assertSessionHas('status');

        $approved = $this->withCookie(
            config('public_join.device_cookie_name', 'device_cookie_id'),
            $participant->device_cookie_id
        )->post("/e/{$eventNight->code}/request", [
            'song_id' => $song->id,
            'join_token' => $joinToken,
            'display_name' => 'Lucia',
        ]);

        $approved->assertRedirect(route('public.join.show', $eventNight->code));
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
            'display_name' => 'Gianni',
        ])->assertSessionHas('status');

        $response = $this->from("/e/{$eventNight->code}")
            ->withCookie(config('public_join.device_cookie_name', 'device_cookie_id'), $participant->device_cookie_id)
            ->post("/e/{$eventNight->code}/request", [
                'song_id' => $song->id,
                'join_token' => $joinToken,
                'display_name' => 'Gianni',
            ]);

        $response->assertSessionHasErrors(['song_id']);
        $this->assertSame(1, SongRequest::count());
    }

    public function test_songs_endpoint_uses_read_rate_limit_scope(): void
    {
        config([
            'public_join.rate_limit_read_per_ip' => 1,
            'public_join.rate_limit_read_per_participant' => 10,
            'public_join.rate_limit_read_decay_seconds' => 60,
        ]);

        $venue = Venue::create([
            'name' => 'Test Venue',
            'timezone' => 'UTC',
        ]);

        $eventNight = EventNight::create([
            'venue_id' => $venue->id,
            'code' => 'EVENT8',
            'break_seconds' => 0,
            'request_cooldown_seconds' => 0,
            'status' => EventNight::STATUS_ACTIVE,
            'starts_at' => now(),
        ]);

        Song::create([
            'title' => 'Read Rate Song',
            'artist' => 'Artist',
            'duration_seconds' => 180,
        ]);

        $songsFirst = $this->getJson("/e/{$eventNight->code}/songs");
        $songsFirst->assertOk();

        $songsSecond = $this->getJson("/e/{$eventNight->code}/songs");
        $songsSecond->assertStatus(429);
    }

    public function test_my_requests_endpoint_uses_read_rate_limit_scope_by_participant(): void
    {
        config([
            'public_join.rate_limit_read_per_ip' => 10,
            'public_join.rate_limit_read_per_participant' => 1,
            'public_join.rate_limit_read_decay_seconds' => 60,
        ]);

        $venue = Venue::create([
            'name' => 'Test Venue',
            'timezone' => 'UTC',
        ]);

        $eventNight = EventNight::create([
            'venue_id' => $venue->id,
            'code' => 'EVENT10',
            'break_seconds' => 0,
            'request_cooldown_seconds' => 0,
            'status' => EventNight::STATUS_ACTIVE,
            'starts_at' => now(),
        ]);

        $song = Song::create([
            'title' => 'Read Rate Song',
            'artist' => 'Artist',
            'duration_seconds' => 180,
        ]);

        $joinToken = 'join-token-read-rate-participant';
        $participant = Participant::create([
            'event_night_id' => $eventNight->id,
            'device_cookie_id' => 'device-read-rate-participant',
            'join_token_hash' => hash('sha256', $joinToken),
            'display_name' => 'Elena',
        ]);

        SongRequest::create([
            'event_night_id' => $eventNight->id,
            'participant_id' => $participant->id,
            'song_id' => $song->id,
            'status' => SongRequest::STATUS_QUEUED,
            'position' => 1,
        ]);

        $first = $this->withServerVariables(['REMOTE_ADDR' => '10.0.0.1'])
            ->withCookie(config('public_join.device_cookie_name', 'device_cookie_id'), $participant->device_cookie_id)
            ->getJson("/e/{$eventNight->code}/my-requests?join_token={$joinToken}");
        $first->assertOk();

        $second = $this->withServerVariables(['REMOTE_ADDR' => '10.0.0.2'])
            ->withCookie(config('public_join.device_cookie_name', 'device_cookie_id'), $participant->device_cookie_id)
            ->getJson("/e/{$eventNight->code}/my-requests?join_token={$joinToken}");
        $second->assertStatus(429);
    }

    public function test_my_requests_endpoint_uses_read_rate_limit_scope_by_ip(): void
    {
        config([
            'public_join.rate_limit_read_per_ip' => 1,
            'public_join.rate_limit_read_per_participant' => 10,
            'public_join.rate_limit_read_decay_seconds' => 60,
        ]);

        $venue = Venue::create([
            'name' => 'Test Venue',
            'timezone' => 'UTC',
        ]);

        $eventNight = EventNight::create([
            'venue_id' => $venue->id,
            'code' => 'EVENT9',
            'break_seconds' => 0,
            'request_cooldown_seconds' => 0,
            'status' => EventNight::STATUS_ACTIVE,
            'starts_at' => now(),
        ]);

        $song = Song::create([
            'title' => 'Read Rate Song',
            'artist' => 'Artist',
            'duration_seconds' => 180,
        ]);

        $joinToken = 'join-token-read-rate';
        $participant = Participant::create([
            'event_night_id' => $eventNight->id,
            'device_cookie_id' => 'device-read-rate',
            'join_token_hash' => hash('sha256', $joinToken),
            'display_name' => 'Paolo',
        ]);

        SongRequest::create([
            'event_night_id' => $eventNight->id,
            'participant_id' => $participant->id,
            'song_id' => $song->id,
            'status' => SongRequest::STATUS_QUEUED,
            'position' => 1,
        ]);

        $first = $this->withCookie(
            config('public_join.device_cookie_name', 'device_cookie_id'),
            $participant->device_cookie_id
        )->getJson("/e/{$eventNight->code}/my-requests?join_token={$joinToken}");
        $first->assertOk();

        $second = $this->withCookie(
            config('public_join.device_cookie_name', 'device_cookie_id'),
            $participant->device_cookie_id
        )->getJson("/e/{$eventNight->code}/my-requests?join_token={$joinToken}");
        $second->assertStatus(429);
    }

    public function test_my_requests_endpoint_returns_personal_queue_data(): void
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
            'title' => 'Solo Song',
            'artist' => 'Artist',
            'duration_seconds' => 180,
        ]);

        $joinToken = 'join-token-76543210';
        $participant = Participant::create([
            'event_night_id' => $eventNight->id,
            'device_cookie_id' => 'device-999',
            'join_token_hash' => hash('sha256', $joinToken),
            'display_name' => 'Sara',
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
        )->getJson("/e/{$eventNight->code}/my-requests?join_token={$joinToken}");

        $response->assertOk()
            ->assertJsonPath('meta.count', 1)
            ->assertJsonPath('data.0.status', SongRequest::STATUS_QUEUED)
            ->assertJsonPath('data.0.status_label', 'In coda')
            ->assertJsonPath('data.0.title', 'Solo Song');
    }
}
