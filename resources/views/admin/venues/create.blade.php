@extends('admin.layout')

@section('content')
    <h1>Create Venue</h1>
    <form method="POST" action="{{ route('admin.venues.store') }}">
        @csrf
        @include('admin.venues.form', ['venue' => null])
        <div class="actions" style="margin-top: 16px;">
            <button class="button success" type="submit">Create Venue</button>
            <a class="button secondary" href="{{ route('admin.venues.index') }}">Back</a>
        </div>
    </form>
@endsection
