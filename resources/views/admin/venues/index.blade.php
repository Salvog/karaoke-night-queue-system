@extends('admin.layout')

@section('content')
    <div class="actions" style="justify-content: space-between; align-items: center;">
        <div>
            <h1 style="margin: 0;">Venues</h1>
            <div class="helper">Manage the locations available for events.</div>
        </div>
        <a class="button success" href="{{ route('admin.venues.create') }}">Add Venue</a>
    </div>

    <div class="divider"></div>

    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Timezone</th>
                <th>Updated</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        @forelse ($venues as $venue)
            <tr>
                <td>{{ $venue->name }}</td>
                <td>{{ $venue->timezone }}</td>
                <td>{{ $venue->updated_at?->format('Y-m-d H:i') ?? '—' }}</td>
                <td>
                    <div class="actions">
                        <a class="button secondary" href="{{ route('admin.venues.edit', $venue) }}">Edit</a>
                        <form method="POST" action="{{ route('admin.venues.destroy', $venue) }}">
                            @csrf
                            @method('DELETE')
                            <button class="button danger" type="submit">Delete</button>
                        </form>
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="4">No venues created yet.</td>
            </tr>
        @endforelse
        </tbody>
    </table>
@endsection
