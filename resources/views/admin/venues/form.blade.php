<div class="form-grid">
    <div>
        <label for="name">Venue Name</label>
        <input id="name" type="text" name="name" value="{{ old('name', $venue->name ?? '') }}" required>
    </div>
    <div>
        <label for="timezone">Timezone</label>
        <input id="timezone" type="text" name="timezone" value="{{ old('timezone', $venue->timezone ?? 'UTC') }}" required>
        <div class="helper">Use a valid TZ identifier (e.g. Europe/Rome).</div>
    </div>
</div>
