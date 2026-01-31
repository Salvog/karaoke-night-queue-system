@extends('admin.layout')

@section('content')
    <h1>Edit Event #{{ $eventNight->id }}</h1>
    <p>Event code: <strong>{{ $eventNight->code }}</strong></p>

    <form method="POST" action="{{ route('admin.events.update', $eventNight) }}">
        @csrf
        @method('PUT')
        @include('admin.events.form')
        <button class="button" type="submit">Save Changes</button>
    </form>
@endsection
