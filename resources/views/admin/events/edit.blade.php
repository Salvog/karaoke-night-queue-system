@extends('admin.layout')

@section('content')
    <h1>Modifica evento #{{ $eventNight->id }}</h1>
    <form method="POST" action="{{ route('admin.events.update', $eventNight) }}">
        @csrf
        @method('PUT')
        @include('admin.events.form')
        <div class="actions" style="margin-top: 16px;">
            <button class="button success" type="submit">Salva modifiche</button>
            <a class="button secondary" href="{{ route('admin.events.index') }}">Indietro</a>
        </div>
    </form>
@endsection
