<div class="split">
    <div>
        <label for="title">Title</label>
        <input id="title" type="text" name="title" value="{{ old('title', $song->title ?? '') }}" required>
    </div>
    <div>
        <label for="artist">Artist</label>
        <input id="artist" type="text" name="artist" value="{{ old('artist', $song->artist ?? '') }}">
    </div>
</div>

<div class="split" style="margin-top: 16px;">
    <div>
        <label for="duration_seconds">Duration (seconds)</label>
        <input id="duration_seconds" type="number" name="duration_seconds" min="1" value="{{ old('duration_seconds', $song->duration_seconds ?? 0) }}" required>
    </div>
    <div>
        <label for="lyrics">Lyrics (optional)</label>
        <textarea id="lyrics" name="lyrics">{{ old('lyrics', $song->lyrics ?? '') }}</textarea>
    </div>
</div>
