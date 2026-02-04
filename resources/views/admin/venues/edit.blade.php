@extends('admin.layout')

@section('content')
    <h1>Modifica location</h1>
    <form method="POST" action="{{ route('admin.venues.update', $venue) }}">
        @csrf
        @method('PUT')
        @include('admin.venues.form', ['venue' => $venue])
        <div class="actions" style="margin-top: 16px;">
            <button class="button success" type="submit">Salva modifiche</button>
            <a class="button secondary" href="{{ route('admin.venues.index') }}">Indietro</a>
        </div>
    </form>
@endsection
