<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $owner = auth()->user()->owner()->with(['dogs', 'bookings' => function ($q) {
            $q->with('dog')->latest();
        }])->first();

        return Inertia::render('Owner/Dashboard', [
            'owner' => $owner,
        ]);
    }
}
