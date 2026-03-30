<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $date = $request->query('date', today()->toDateString());

        $attendances = Attendance::with('dog.owner')
            ->whereDate('date', $date)
            ->orderBy('type')
            ->get();

        return Inertia::render('Dashboard', [
            'attendances' => $attendances,
            'date' => $date,
        ]);
    }
}
