@extends('admin.layout')

@section('content')
    <h1>Edit Song</h1>

    <form method="POST" action="{{ route('admin.songs.update', $song) }}">
        @csrf
        @method('PUT')
        @include('admin.songs.form')
        <div class="actions" style="margin-top: 16px;">
            <button class="button" type="submit">Save Changes</button>
            <a class="button outline" href="{{ route('admin.songs.index') }}">Back to Songs</a>
        </div>
    </form>
@endsection
