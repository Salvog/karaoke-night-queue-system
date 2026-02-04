@extends('admin.layout')

@section('content')
    <h1>Crea location</h1>
    <form method="POST" action="{{ route('admin.venues.store') }}">
        @csrf
        @include('admin.venues.form', ['venue' => null])
        <div class="actions" style="margin-top: 16px;">
            <button class="button success" type="submit">Crea location</button>
            <a class="button secondary" href="{{ route('admin.venues.index') }}">Indietro</a>
        </div>
    </form>
@endsection
