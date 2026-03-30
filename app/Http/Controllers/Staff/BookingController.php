<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BookingController extends Controller
{
    public function index(): Response
    {
        $bookings = Booking::with('dog', 'owner')
            ->orderByRaw("FIELD(status, 'pendente', 'aprovado', 'rejeitado')")
            ->latest()
            ->get();

        return Inertia::render('Staff/Bookings/Index', [
            'bookings' => $bookings,
        ]);
    }

    public function update(Request $request, Booking $booking)
    {
        $validated = $request->validate([
            'status'      => 'required|in:aprovado,rejeitado',
            'staff_notes' => 'nullable|string|max:1000',
        ]);

        $booking->update($validated);

        return back();
    }
}
