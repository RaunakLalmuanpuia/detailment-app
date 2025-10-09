<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EventController extends Controller
{
    //
    public function index(Request $request)
    {
        $events = Event::with([
            'eventDuties.duty',
            'eventDuties.assignments.employee.designation'
        ])
            ->get()
            ->map(function ($event) {
                $start = $event->date
                    ? $event->date . 'T' . ($event->start_time ?? '00:00:00')
                    : null;

                $end = $event->date
                    ? $event->date . 'T' . ($event->end_time ?? '00:00:00')
                    : null;

                return [
                    'id' => $event->id,
                    'title' => $event->title,
                    'location' => $event->location,
                    'start' => $start,
                    'end' => $end,
                    'extendedProps' => [
                        'location' => $event->location,
                        'duties' => $event->eventDuties->map(function ($eventDuty) {
                            $duty = $eventDuty->duty;
                            return [
                                'id' => $duty?->id,
                                'name' => $duty?->name,
                                'min_required' => $duty?->min_required,
                                'assignments' => $eventDuty->assignments->map(function ($assignment) {
                                    $employee = $assignment->employee;
                                    return [
                                        'id' => $assignment->id,
                                        'employee_name' => $employee?->name,
                                        'designation' => $employee?->designation?->title,
                                        'status' => $assignment->status,
                                    ];
                                }),
                            ];
                        }),
                    ],
                ];
            });

        return Inertia::render('Backend/Events/Index', [
            'events' => $events,
        ]);
    }

}
