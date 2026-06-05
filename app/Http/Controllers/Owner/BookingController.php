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

        if (!$owner) {
            $owner = auth()->user()->owner()->create([
                'name'  => auth()->user()->name,
                'email' => auth()->user()->email,
            ]);
        }

        $validated = $request->validate([
            'dog_id'     => 'required|exists:dogs,id',
            'type'       => 'required|in:atl,hotel,aula,integracao,pack_creche',
            'subtype'    => 'nullable|string|max:50',
            'is_regular' => 'boolean',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date'   => 'required_if:type,hotel|nullable|date|after:start_date',
            'frequency'  => 'required_if:type,atl|required_if:type,aula|required_if:type,integracao|nullable|in:semanal,quinzenal,mensal',
            'pet_taxi'   => 'boolean',
            'notes'      => 'nullable|string|max:1000',
        ]);

        // Ensure the dog belongs to this owner
        abort_unless($owner->dogs()->where('id', $validated['dog_id'])->exists(), 403);

        $owner->bookings()->create([
            ...$validated,
            'pet_taxi'   => $request->boolean('pet_taxi'),
            'is_regular' => $request->boolean('is_regular', true),
            'status'     => 'pendente',
        ]);

        return redirect()->route('owner.dashboard');
    }
}
