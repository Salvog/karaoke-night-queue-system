<?php

namespace App\Modules\Auth\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

class AdminDashboardController extends Controller
{
    public function __invoke(): JsonResponse
    {
        Gate::authorize('access-admin');

        return response()->json(['message' => 'Admin area stub']);
    }
}
