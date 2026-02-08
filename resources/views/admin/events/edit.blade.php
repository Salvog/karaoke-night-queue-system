@extends('admin.layout')

@section('content')
    <div class="page-header">
        <div>
            <h1>Modifica evento #{{ $eventNight->id }}</h1>
            <p class="subtitle">Aggiorna dettagli, tempi e accessi per una serata sempre fluida.</p>
        </div>
    </div>
    <form method="POST" action="{{ route('admin.events.update', $eventNight) }}">
        @csrf
        @method('PUT')
        @include('admin.events.form')
        <div class="form-actions">
            <button class="button success" type="submit">Salva modifiche</button>
            <a class="button secondary" href="{{ route('admin.events.index') }}">Indietro</a>
        </div>
    </form>
@endsection
