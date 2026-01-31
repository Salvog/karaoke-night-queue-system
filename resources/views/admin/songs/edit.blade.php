@extends('admin.layout')

@section('content')
    <h1>Edit Song</h1>

    <form method="POST" action="{{ route('admin.songs.update', $song) }}">
        @csrf
        @method('PUT')
        @include('admin.songs.form', ['song' => $song])
        <button class="button" type="submit">Save Song</button>
    </form>
@endsection
