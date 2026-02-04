@extends('admin.layout')

@section('content')
    <h1>Modifica canzone</h1>
    <form method="POST" action="{{ route('admin.songs.update', $song) }}">
        @csrf
        @method('PUT')
        @include('admin.songs.form', ['song' => $song])
        <div class="actions" style="margin-top: 16px;">
            <button class="button success" type="submit">Salva modifiche</button>
            <a class="button secondary" href="{{ route('admin.songs.index') }}">Indietro</a>
        </div>
    </form>
@endsection
