<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ScheduleController extends Controller
{
    public function index(Request $request): Response
    {
        $weekStart = Carbon::parse($request->input('week', now()->startOfWeek(Carbon::MONDAY)))->startOfDay();
        $weekEnd   = $weekStart->copy()->addDays(6)->endOfDay();

        $bookings = Booking::with('dog', 'owner')
            ->where('status', 'aprovado')
            ->get();

        // Build schedule: date (Y-m-d) => array of event entries
        $schedule = [];
        for ($i = 0; $i < 7; $i++) {
            $schedule[$weekStart->copy()->addDays($i)->toDateString()] = [];
        }

        foreach ($bookings as $booking) {
            foreach ($this->occurrencesInWeek($booking, $weekStart, $weekEnd) as $entry) {
                $date = $entry['date'];
                if (array_key_exists($date, $schedule)) {
                    $schedule[$date][] = [
                        'id'         => $booking->id,
                        'type'       => $booking->type,
                        'subtype'    => $booking->subtype,
                        'is_regular' => $booking->is_regular,
                        'pet_taxi'   => $booking->pet_taxi,
                        'event_type' => $entry['event_type'],
                        'frequency'  => $booking->frequency,
                        'dog'        => ['id' => $booking->dog->id,   'name' => $booking->dog->name],
                        'owner'      => ['id' => $booking->owner->id, 'name' => $booking->owner->name],
                    ];
                }
            }
        }

        return Inertia::render('Staff/Schedule/Index', [
            'schedule'  => $schedule,
            'weekStart' => $weekStart->toDateString(),
        ]);
    }

    private function occurrencesInWeek(Booking $booking, Carbon $weekStart, Carbon $weekEnd): array
    {
        $start   = Carbon::parse($booking->start_date);
        $results = [];

        switch ($booking->type) {
            case 'hotel':
                $end = Carbon::parse($booking->end_date ?? $booking->start_date);
                if ($start >= $weekStart && $start <= $weekEnd) {
                    $results[] = ['date' => $start->toDateString(), 'event_type' => 'checkin'];
                }
                // Intermediate stay days
                $cur = $start->copy()->addDay()->max($weekStart->copy());
                while ($cur <= $weekEnd && $cur < $end) {
                    $results[] = ['date' => $cur->toDateString(), 'event_type' => 'stay'];
                    $cur->addDay();
                }
                if (!$end->equalTo($start) && $end >= $weekStart && $end <= $weekEnd) {
                    $results[] = ['date' => $end->toDateString(), 'event_type' => 'checkout'];
                }
                break;

            case 'atl':
                foreach ($this->recurringDates($booking, $weekStart, $weekEnd) as $date) {
                    $results[] = ['date' => $date, 'event_type' => 'allday'];
                }
                break;

            case 'aula':
            case 'integracao':
                foreach ($this->recurringDates($booking, $weekStart, $weekEnd) as $date) {
                    $results[] = ['date' => $date, 'event_type' => 'session'];
                }
                break;

            default:
                // pack_creche, pet_sitting, dog_walking, banho — one-time on start_date
                if ($start >= $weekStart && $start <= $weekEnd) {
                    $results[] = ['date' => $start->toDateString(), 'event_type' => 'session'];
                }
        }

        return $results;
    }

    private function recurringDates(Booking $booking, Carbon $weekStart, Carbon $weekEnd): array
    {
        $start = Carbon::parse($booking->start_date);
        if ($start > $weekEnd) return [];

        $dates = [];

        for ($i = 0; $i < 7; $i++) {
            $day = $weekStart->copy()->addDays($i);
            if ($day < $start) continue;

            $diffDays = (int) $start->diffInDays($day);

            $occurs = match($booking->frequency) {
                'semanal'   => $diffDays % 7 === 0,
                'quinzenal' => $diffDays % 14 === 0,
                'mensal'    => $day->day === $start->day,
                default     => false,
            };

            if ($occurs) {
                $dates[] = $day->toDateString();
            }
        }

        return $dates;
    }
}
