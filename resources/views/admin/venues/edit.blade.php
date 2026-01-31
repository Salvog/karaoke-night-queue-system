@extends('admin.layout')

@section('content')
    <h1>Edit Venue</h1>
    <form method="POST" action="{{ route('admin.venues.update', $venue) }}">
        @csrf
        @method('PUT')
        @include('admin.venues.form', ['venue' => $venue])
        <div class="actions" style="margin-top: 16px;">
            <button class="button success" type="submit">Save Changes</button>
            <a class="button secondary" href="{{ route('admin.venues.index') }}">Back</a>
        </div>
    </form>
@endsection
