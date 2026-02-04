@extends('admin.layout')

@section('content')
    <div class="actions" style="justify-content: space-between; align-items: center;">
        <div>
            <h1 style="margin: 0;">Location</h1>
            <div class="helper">Gestisci le location disponibili per gli eventi.</div>
        </div>
        <a class="button success" href="{{ route('admin.venues.create') }}">Aggiungi location</a>
    </div>

    <div class="divider"></div>

    <table>
        <thead>
            <tr>
                <th>Nome</th>
                <th>Fuso orario</th>
                <th>Aggiornato</th>
                <th>Azioni</th>
            </tr>
        </thead>
        <tbody>
        @forelse ($venues as $venue)
            <tr>
                <td>{{ $venue->name }}</td>
                <td>{{ $venue->timezone }}</td>
                <td>{{ $venue->updated_at?->format('Y-m-d H:i') ?? '—' }}</td>
                <td>
                    <div class="actions">
                        <a class="button secondary" href="{{ route('admin.venues.edit', $venue) }}">Modifica</a>
                        <form method="POST" action="{{ route('admin.venues.destroy', $venue) }}">
                            @csrf
                            @method('DELETE')
                            <button class="button danger" type="submit">Elimina</button>
                        </form>
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="4">Nessuna location creata.</td>
            </tr>
        @endforelse
        </tbody>
    </table>
@endsection
