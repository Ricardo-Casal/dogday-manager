<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Mail\BookingStatusUpdated;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use Inertia\Response;

class BookingController extends Controller
{
    public function index(Request $request): Response
    {
        $tab = $request->input('tab', 'pendente');
        $tab = in_array($tab, ['pendente', 'aprovado', 'rejeitado']) ? $tab : 'pendente';

        $bookings = Booking::with('dog', 'owner')
            ->where('status', $tab)
            ->latest()
            ->get();

        $counts = Booking::selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return Inertia::render('Staff/Bookings/Index', [
            'bookings' => $bookings,
            'tab'      => $tab,
            'counts'   => $counts,
        ]);
    }

    public function update(Request $request, Booking $booking)
    {
        $validated = $request->validate([
            'status'      => 'required|in:aprovado,rejeitado',
            'staff_notes' => 'nullable|string|max:1000',
        ]);

        $booking->update($validated);
        $booking->load('dog', 'owner');

        $email = $booking->owner?->email ?? $booking->owner?->user?->email;

        if ($email) {
            Mail::to($email)->send(new BookingStatusUpdated($booking));
        }

        return back();
    }
}
