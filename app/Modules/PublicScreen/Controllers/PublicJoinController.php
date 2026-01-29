<?php

namespace App\Modules\PublicScreen\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\PublicScreen\Actions\ResolveJoinRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PublicJoinController extends Controller
{
    public function __construct(private readonly ResolveJoinRequest $action)
    {
    }

    public function __invoke(Request $request, string $eventCode, string $joinToken): JsonResponse
    {
        $validated = validator(
            ['eventCode' => $eventCode, 'joinToken' => $joinToken],
            [
                'eventCode' => ['required', 'string', 'min:4', 'max:12'],
                'joinToken' => ['required', 'string', 'min:8', 'max:64'],
            ]
        )->validate();

        $payload = $this->action->execute($validated['eventCode'], $validated['joinToken']);

        return response()->json($payload);
    }
}
