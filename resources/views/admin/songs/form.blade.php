@php
    $songTitle = old('title', $song->title ?? '');
    $songArtist = old('artist', $song->artist ?? '');
    $duration = old('duration_seconds', $song->duration_seconds ?? 180);
    $lyrics = old('lyrics', $song->lyrics ?? '');
@endphp

<div style="margin-bottom: 16px;">
    <label for="title">Title</label>
    <input id="title" type="text" name="title" value="{{ $songTitle }}" required>
</div>

<div style="margin-bottom: 16px;">
    <label for="artist">Artist</label>
    <input id="artist" type="text" name="artist" value="{{ $songArtist }}">
</div>

<div style="margin-bottom: 16px;">
    <label for="duration_seconds">Duration (seconds)</label>
    <input id="duration_seconds" type="number" name="duration_seconds" min="1" value="{{ $duration }}" required>
</div>

<div style="margin-bottom: 16px;">
    <label for="lyrics">Lyrics (optional)</label>
    <textarea id="lyrics" name="lyrics" rows="5" style="width: 100%;">{{ $lyrics }}</textarea>
</div>
