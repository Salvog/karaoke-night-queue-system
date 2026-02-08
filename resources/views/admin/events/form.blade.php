@php
    $defaultStartsAt = now()->setTime(19, 0);
    $defaultEndsAt = now()->addDay()->setTime(2, 0);
    $startsAt = old('starts_at', optional($eventNight?->starts_at)->format('Y-m-d\TH:i') ?? $defaultStartsAt->format('Y-m-d\TH:i'));
    $endsAt = old('ends_at', optional($eventNight?->ends_at)->format('Y-m-d\TH:i') ?? $defaultEndsAt->format('Y-m-d\TH:i'));
    $breakSeconds = old('break_seconds', $eventNight->break_seconds ?? 40);
    $cooldownMinutes = old('request_cooldown_minutes', $eventNight->request_cooldown_seconds ?? 20);
    $currentStatus = old('status', $eventNight->status ?? \App\Models\EventNight::STATUS_DRAFT);
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
        @if (isset($eventNight))
            <input id="code" type="text" name="code" value="{{ old('code', $eventNight->code ?? '') }}" readonly maxlength="12">
            <div class="helper">Generato automaticamente, non modificabile.</div>
        @else
            <input id="code" type="text" value="Generato automaticamente" readonly>
            <div class="helper">Verrà creato un codice univoco al salvataggio.</div>
        @endif
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
        <label for="request_cooldown_minutes">Minuti di attesa richieste</label>
        <input id="request_cooldown_minutes" type="number" name="request_cooldown_minutes" min="0" value="{{ $cooldownMinutes }}" required>
        <div class="helper">Tempo minimo di attesa (in minuti) per ogni cantante prima di fare un'altra richiesta.</div>
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
