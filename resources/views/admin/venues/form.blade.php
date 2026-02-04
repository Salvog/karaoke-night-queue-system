<div class="form-grid">
    <div>
        <label for="name">Nome location</label>
        <input id="name" type="text" name="name" value="{{ old('name', $venue->name ?? '') }}" required>
    </div>
    <div>
        <label for="timezone">Fuso orario</label>
        <input id="timezone" type="text" name="timezone" value="{{ old('timezone', $venue->timezone ?? 'UTC') }}" required>
        <div class="helper">Usa un identificatore TZ valido (es. Europe/Rome).</div>
    </div>
</div>
