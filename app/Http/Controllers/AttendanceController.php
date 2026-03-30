<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Dog;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AttendanceController extends Controller
{
    public function create(Request $request): Response
    {
        $dogs = Dog::with('owner')->orderBy('name')->get();

        return Inertia::render('Attendances/Create', [
            'dogs' => $dogs,
            'date' => $request->query('date', today()->toDateString()),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'dog_id' => 'required|exists:dogs,id',
            'date' => 'required|date',
            'type' => 'required|in:atl,hotel,aula',
            'notes' => 'nullable|string',
        ]);

        Attendance::create($validated);

        return redirect()->route('dashboard', ['date' => $validated['date']]);
    }

    public function destroy(Attendance $attendance)
    {
        $date = $attendance->date->toDateString();
        $attendance->delete();

        return redirect()->route('dashboard', ['date' => $date]);
    }
}
