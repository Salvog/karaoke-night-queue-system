@extends('admin.layout')

@section('content')
    <h1>Create Event</h1>

    <form method="POST" action="{{ route('admin.events.store') }}">
        @csrf
        @include('admin.events.form', ['eventNight' => null])
        <div class="actions" style="margin-top: 16px;">
            <button class="button success" type="submit">Create Event</button>
            <a class="button secondary" href="{{ route('admin.events.index') }}">Back</a>
        </div>
    </form>
@endsection
