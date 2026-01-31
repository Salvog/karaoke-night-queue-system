@extends('admin.layout')

@section('content')
    <h1>Venues</h1>
    <div style="margin-bottom: 16px;">
        <a class="button" href="{{ route('admin.venues.create') }}">Add Venue</a>
    </div>
    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Timezone</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        @forelse ($venues as $venue)
            <tr>
                <td>{{ $venue->name }}</td>
                <td>{{ $venue->timezone }}</td>
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
                <td colspan="3">No venues available.</td>
            </tr>
        @endforelse
        </tbody>
    </table>
@endsection
