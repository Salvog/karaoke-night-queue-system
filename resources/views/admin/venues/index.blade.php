@extends('admin.layout')

@section('content')
    <h1>Venues</h1>

    <section class="section">
        <div class="section-header">
            <h2>Add Venue</h2>
        </div>
        <form method="POST" action="{{ route('admin.venues.store') }}" class="split">
            @csrf
            <div>
                <label for="name">Name</label>
                <input id="name" type="text" name="name" value="{{ old('name') }}" required>
            </div>
            <div>
                <label for="timezone">Timezone</label>
                <input id="timezone" type="text" name="timezone" value="{{ old('timezone', 'UTC') }}" required>
                <small class="help">Example: Europe/Rome, America/New_York, UTC.</small>
            </div>
            <div class="actions" style="align-self: end;">
                <button class="button" type="submit">Save Venue</button>
            </div>
        </form>
    </section>

    <section class="section">
        <div class="section-header">
            <h2>Existing Venues</h2>
        </div>
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Timezone</th>
                    <th>Events</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            @forelse ($venues as $venue)
                <tr>
                    <td>{{ $venue->name }}</td>
                    <td>{{ $venue->timezone }}</td>
                    <td>{{ $venue->event_nights_count }}</td>
                    <td>
                        <form method="POST" action="{{ route('admin.venues.update', $venue) }}" class="actions" style="gap: 6px;">
                            @csrf
                            @method('PUT')
                            <input type="text" name="name" value="{{ $venue->name }}" required style="max-width: 160px;">
                            <input type="text" name="timezone" value="{{ $venue->timezone }}" required style="max-width: 160px;">
                            <button class="button secondary" type="submit">Update</button>
                        </form>
                        <form method="POST" action="{{ route('admin.venues.destroy', $venue) }}" style="margin-top: 8px;">
                            @csrf
                            @method('DELETE')
                            <button class="button danger" type="submit">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4">No venues yet.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </section>
@endsection
