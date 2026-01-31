@php
    $startsAt = old('starts_at', optional($eventNight?->starts_at)->format('Y-m-d\TH:i'));
    $endsAt = old('ends_at', optional($eventNight?->ends_at)->format('Y-m-d\TH:i'));
    $currentStatus = old('status', $eventNight->status ?? \App\Models\EventNight::STATUS_DRAFT);
@endphp

<div style="margin-bottom: 16px;">
    <label for="venue_id">Venue</label>
    <select id="venue_id" name="venue_id" required>
        @foreach ($venues as $venue)
            <option value="{{ $venue->id }}" @selected((int) old('venue_id', $eventNight->venue_id ?? 0) === $venue->id)>
                {{ $venue->name }}
            </option>
        @endforeach
    </select>
</div>

<div style="margin-bottom: 16px;">
    <label for="code">Event Code</label>
    <input id="code" type="text" name="code" value="{{ old('code', $eventNight->code ?? '') }}" required maxlength="12">
</div>

<div style="margin-bottom: 16px;">
    <label for="starts_at">Start Date/Time</label>
    <input id="starts_at" type="datetime-local" name="starts_at" value="{{ $startsAt }}" required>
</div>

<div style="margin-bottom: 16px;">
    <label for="ends_at">End Date/Time</label>
    <input id="ends_at" type="datetime-local" name="ends_at" value="{{ $endsAt }}" required>
</div>

<div style="margin-bottom: 16px;">
    <label for="break_seconds">Break Seconds</label>
    <input id="break_seconds" type="number" name="break_seconds" min="0" value="{{ old('break_seconds', $eventNight->break_seconds ?? 0) }}" required>
    <div class="helper">Extra seconds added after each song before the next starts.</div>
</div>

<div style="margin-bottom: 16px;">
    <label for="request_cooldown_seconds">Request Cooldown Seconds</label>
    <input id="request_cooldown_seconds" type="number" name="request_cooldown_seconds" min="0" value="{{ old('request_cooldown_seconds', $eventNight->request_cooldown_seconds ?? 0) }}" required>
    <div class="helper">How long each singer must wait before requesting another song.</div>
</div>

<div style="margin-bottom: 16px;">
    <label for="join_pin">Optional PIN</label>
    <input id="join_pin" type="text" name="join_pin" value="{{ old('join_pin', $eventNight->join_pin ?? '') }}">
</div>

<div style="margin-bottom: 16px;">
    <label for="status">Status</label>
    <select id="status" name="status" required>
        @foreach ($statuses as $value => $label)
            <option value="{{ $value }}" @selected($currentStatus === $value)>
                {{ $label }}
            </option>
        @endforeach
    </select>
</div>
