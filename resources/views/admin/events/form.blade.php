@php
    $now = now();
    $defaultStart = $now->copy()->setTime(19, 0);
    $defaultEnd = $now->copy()->addDay()->setTime(2, 0);

    $startsAt = old('starts_at', optional($eventNight?->starts_at)->format('Y-m-d\TH:i') ?? $defaultStart->format('Y-m-d\TH:i'));
    $endsAt = old('ends_at', optional($eventNight?->ends_at)->format('Y-m-d\TH:i') ?? $defaultEnd->format('Y-m-d\TH:i'));
    $currentStatus = old('status', $eventNight->status ?? \App\Models\EventNight::STATUS_ACTIVE);
    $selectedVenueId = (int) old('venue_id', $eventNight->venue_id ?? ($venues->first()->id ?? 0));
    $breakSeconds = (int) old('break_seconds', $eventNight->break_seconds ?? 40);

    $cooldownMinutesDefault = $eventNight?->request_cooldown_seconds !== null
        ? (int) ceil($eventNight->request_cooldown_seconds / 60)
        : 20;
    $requestCooldownMinutes = (int) old('request_cooldown_minutes', $cooldownMinutesDefault);
    $eventCode = old('code_preview', $eventNight->code ?? $generatedCode ?? 'Generato automaticamente');
@endphp

<style>
    .event-form-grid {
        display: grid;
        gap: 16px;
    }

    .event-field {
        display: grid;
        gap: 6px;
    }

    .event-field input,
    .event-field select {
        max-width: 100%;
    }

    .event-helper {
        margin: 0;
    }

    @media (min-width: 768px) {
        .event-form-grid {
            grid-template-columns: repeat(2, minmax(260px, 380px));
            justify-content: start;
            column-gap: 22px;
        }
    }

    .event-field.code-field input {
        max-width: 180px;
        font-family: 'Consolas', 'Courier New', monospace;
        letter-spacing: 0.08em;
        font-size: 13px;
    }
</style>

<div class="event-form-grid">
    <div class="event-field">
        <label for="venue_id">Location</label>
        <select id="venue_id" name="venue_id" required>
            @foreach ($venues as $venue)
                <option value="{{ $venue->id }}" @selected($selectedVenueId === $venue->id)>
                    {{ $venue->name }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="event-field code-field">
        <label for="code_preview">Codice evento (generato automaticamente)</label>
        <input id="code_preview" type="text" value="{{ $eventCode }}" readonly>
    </div>

    <div class="event-field">
        <label for="starts_at">Data/ora inizio</label>
        <input id="starts_at" type="datetime-local" name="starts_at" value="{{ $startsAt }}" required>
    </div>

    <div class="event-field">
        <label for="ends_at">Data/ora fine</label>
        <input id="ends_at" type="datetime-local" name="ends_at" value="{{ $endsAt }}" required>
    </div>

    <div class="event-field">
        <label for="break_seconds">Secondi di pausa tra canzoni</label>
        <input id="break_seconds" type="number" name="break_seconds" min="0" value="{{ $breakSeconds }}" required>
        <p class="helper event-helper">Tempo extra aggiunto dopo ogni canzone.</p>
    </div>

    <div class="event-field">
        <label for="request_cooldown_minutes">Minuti di attesa richieste</label>
        <input id="request_cooldown_minutes" type="number" name="request_cooldown_minutes" min="0" value="{{ $requestCooldownMinutes }}" required>
        <p class="helper event-helper">Tempo minimo tra due richieste dello stesso cantante.</p>
    </div>

    <div class="event-field">
        <label for="join_pin">PIN opzionale</label>
        <input id="join_pin" type="text" name="join_pin" value="{{ old('join_pin', $eventNight->join_pin ?? '') }}">
    </div>

    <div class="event-field">
        <label for="status">Stato</label>
        <select id="status" name="status" required>
            @foreach ($statuses as $value => $label)
                <option value="{{ $value }}" @selected($currentStatus === $value)>
                    {{ $label }}
                </option>
            @endforeach
        </select>
    </div>
</div>
