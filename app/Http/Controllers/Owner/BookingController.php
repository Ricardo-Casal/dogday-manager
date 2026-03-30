<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BookingController extends Controller
{
    public function create(): Response
    {
        $owner = auth()->user()->owner()->with('dogs')->first();
        $settings = Setting::all()->keyBy('key');

        return Inertia::render('Owner/Bookings/Create', [
            'owner' => $owner,
            'prices' => $settings->map(fn($s) => $s->value),
        ]);
    }

    public function store(Request $request)
    {
        $owner = auth()->user()->owner;

        $validated = $request->validate([
            'dog_id'    => 'required|exists:dogs,id',
            'type'      => 'required|in:atl,hotel,aula',
            'start_date'=> 'required|date|after_or_equal:today',
            'end_date'  => 'required_if:type,hotel|nullable|date|after:start_date',
            'frequency' => 'required_if:type,atl|required_if:type,aula|nullable|in:semanal,quinzenal,mensal',
            'pet_taxi'  => 'boolean',
            'notes'     => 'nullable|string|max:1000',
        ]);

        // Ensure the dog belongs to this owner
        abort_unless($owner->dogs()->where('id', $validated['dog_id'])->exists(), 403);

        $owner->bookings()->create([
            ...$validated,
            'pet_taxi' => $request->boolean('pet_taxi'),
            'status' => 'pendente',
        ]);

        return redirect()->route('owner.dashboard');
    }
}
