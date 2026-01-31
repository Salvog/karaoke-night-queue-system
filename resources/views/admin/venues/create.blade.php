@extends('admin.layout')

@section('content')
    <h1>Create Venue</h1>

    <form method="POST" action="{{ route('admin.venues.store') }}">
        @csrf
        @include('admin.venues.form', ['venue' => null])
        <button class="button" type="submit">Create Venue</button>
    </form>
@endsection
