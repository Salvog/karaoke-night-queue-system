@php
    $venueName = old('name', $venue->name ?? '');
    $timezone = old('timezone', $venue->timezone ?? 'UTC');
@endphp

<div style="margin-bottom: 16px;">
    <label for="name">Venue Name</label>
    <input id="name" type="text" name="name" value="{{ $venueName }}" required>
</div>

<div style="margin-bottom: 16px;">
    <label for="timezone">Timezone</label>
    <input id="timezone" type="text" name="timezone" value="{{ $timezone }}" required>
    <div style="font-size: 12px; color: #6b7280;">Use an IANA timezone like Europe/Rome or UTC.</div>
</div>
