@extends('admin.layout')

@section('content')
    <div class="page-header">
        <div>
            <h1>Crea evento</h1>
            <p class="subtitle">Imposta la serata con dettagli smart, rapidi e sempre coerenti con il mood karaoke.</p>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.events.store') }}">
        @csrf
        @include('admin.events.form', ['eventNight' => null])
        <div class="form-actions">
            <button class="button success" type="submit">Crea evento</button>
            <a class="button secondary" href="{{ route('admin.events.index') }}">Indietro</a>
        </div>
    </form>
@endsection
