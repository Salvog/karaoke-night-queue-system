@php
    $now = now();
    $defaultStart = $now->copy()->setTime(19, 0);
    $defaultEnd = $now->copy()->addDay()->setTime(2, 0);
    $startsAt = old(
        'starts_at',
        optional($eventNight?->starts_at)->format('Y-m-d\TH:i') ?? $defaultStart->format('Y-m-d\TH:i')
    );
    $endsAt = old(
        'ends_at',
        optional($eventNight?->ends_at)->format('Y-m-d\TH:i') ?? $defaultEnd->format('Y-m-d\TH:i')
    );
    $generatedCode = \Illuminate\Support\Str::upper(\Illuminate\Support\Str::random(6));
    $codeValue = old('code', $eventNight->code ?? $generatedCode);
    $currentStatus = old('status', $eventNight->status ?? \App\Models\EventNight::STATUS_DRAFT);
    $breakSeconds = old('break_seconds', $eventNight->break_seconds ?? 40);
    $requestCooldownMinutes = old('request_cooldown_seconds', $eventNight->request_cooldown_seconds ?? 20);
@endphp

<div class="form-grid">
    <div class="form-field">
        <label for="venue_id">Location</label>
        <select id="venue_id" name="venue_id" required>
            @foreach ($venues as $venue)
                <option value="{{ $venue->id }}" @selected((int) old('venue_id', $eventNight->venue_id ?? 0) === $venue->id)>
                    {{ $venue->name }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="form-field">
        <label for="code">Codice evento</label>
        <input
            id="code"
            type="text"
            name="code"
            value="{{ $codeValue }}"
            required
            maxlength="12"
        >
        <div class="helper">Generato automaticamente per facilitare l'accesso pubblico.</div>
    </div>

    <div class="form-field">
        <label for="starts_at">Data/ora inizio</label>
        <input id="starts_at" type="datetime-local" name="starts_at" value="{{ $startsAt }}" required>
    </div>

    <div class="form-field">
        <label for="ends_at">Data/ora fine</label>
        <input id="ends_at" type="datetime-local" name="ends_at" value="{{ $endsAt }}" required>
    </div>

    <div class="form-field">
        <label for="break_seconds">Secondi di pausa</label>
        <input id="break_seconds" type="number" name="break_seconds" min="0" value="{{ $breakSeconds }}" required>
        <div class="helper">Secondi extra aggiunti dopo ogni canzone prima della successiva.</div>
    </div>

    <div class="form-field">
        <label for="request_cooldown_seconds">Minuti di attesa richieste</label>
        <input id="request_cooldown_seconds" type="number" name="request_cooldown_seconds" min="0" value="{{ $requestCooldownMinutes }}" required>
        <div class="helper">Tempo minimo (in minuti) di attesa per ogni cantante prima di fare un'altra richiesta.</div>
    </div>

    <div class="form-field">
        <label for="join_pin">PIN opzionale</label>
        <input id="join_pin" type="text" name="join_pin" value="{{ old('join_pin', $eventNight->join_pin ?? '') }}">
    </div>

    <div class="form-field">
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
