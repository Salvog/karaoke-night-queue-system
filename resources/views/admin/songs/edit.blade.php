@extends('admin.layout')

@section('content')
    <h1>Edit Song</h1>
    <form method="POST" action="{{ route('admin.songs.update', $song) }}">
        @csrf
        @method('PUT')
        @include('admin.songs.form', ['song' => $song])
        <div class="actions" style="margin-top: 16px;">
            <button class="button success" type="submit">Save Changes</button>
            <a class="button secondary" href="{{ route('admin.songs.index') }}">Back</a>
        </div>
    </form>
@endsection
