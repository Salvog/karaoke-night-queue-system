@extends('admin.layout')

@section('content')
    <h1>Create Event</h1>

    <form method="POST" action="{{ route('admin.events.store') }}">
        @csrf
        @include('admin.events.form', ['eventNight' => null])
        <button class="button" type="submit">Create Event</button>
    </form>
@endsection
