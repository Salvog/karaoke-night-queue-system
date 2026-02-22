<?php

namespace App\Modules\PublicJoin\Controllers;

use App\Http\Controllers\Controller;
use App\Models\EventNight;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;

class PublicEntryController extends Controller
{
    public function show(): Response|RedirectResponse
    {
        $activeEvent = EventNight::query()
            ->where('status', EventNight::STATUS_ACTIVE)
            ->whereNotNull('starts_at')
            ->where('starts_at', '<=', now())
            ->orderByDesc('starts_at')
            ->orderByDesc('id')
            ->first();

        if ($activeEvent) {
            return redirect()->route('public.join.show', $activeEvent->code);
        }

        return response()->view('public.index');
    }
}
