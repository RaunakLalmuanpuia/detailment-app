<?php

namespace App\Http\Controllers;

use App\Models\Duty;
use App\Models\Employee;
use App\Models\Event;
use App\Models\EventAssignment;
use App\Models\EventDuty;
use App\Models\EventGuest;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class EventController extends Controller
{
    //
    public function index()
    {
        $events = Event::with([
            'eventDuties.duty',
            'eventDuties.assignments.employee.designation',
            'guests'
        ])->get();

        // Transform structure for frontend
        $events = $events->map(function ($event) {
            return [
                'id' => $event->id,
                'title' => $event->title,
                'location' => $event->location,
                'date' => $event->date,
                'start_time' => $event->start_time,
                'end_time' => $event->end_time,
                'status' => $event->status,
                'guests' => $event->guests,
                'duties' => $event->eventDuties->map(function ($eventDuty) {
                    return [
                        'id' => $eventDuty->duty->id,
                        'name' => $eventDuty->duty->name,
                        'employees' => $eventDuty->assignments->map(function ($assign) {
                            return [
                                'id' => $assign->employee->id,
                                'name' => $assign->employee->name,
                                'designation' => $assign->employee->designation->title ?? null,
                            ];
                        }),
                    ];
                }),
            ];
        });

        $duties = Duty::all();
        $employees =Employee::with('designation')->get();

        return Inertia::render('Backend/Events/Index', [
            'events' => $events,
            'duties' => $duties,
            'employees' => $employees,
        ]);
    }


//    public function index(Request $request)
//    {
//        $events = Event::with([
//            'eventDuties.duty',
//            'eventDuties.assignments.employee.designation'
//        ])
//            ->get()
//            ->map(function ($event) {
//                $start = $event->date
//                    ? $event->date . 'T' . ($event->start_time ?? '00:00:00')
//                    : null;
//
//                $end = $event->date
//                    ? $event->date . 'T' . ($event->end_time ?? '00:00:00')
//                    : null;
//
//                return [
//                    'id' => $event->id,
//                    'title' => $event->title,
//                    'location' => $event->location,
//                    'start' => $start,
//                    'end' => $end,
//                    'extendedProps' => [
//                        'location' => $event->location,
//                        'duties' => $event->eventDuties->map(function ($eventDuty) {
//                            $duty = $eventDuty->duty;
//                            return [
//                                'id' => $duty?->id,
//                                'name' => $duty?->name,
//                                'min_required' => $duty?->min_required,
//                                'assignments' => $eventDuty->assignments->map(function ($assignment) {
//                                    $employee = $assignment->employee;
//                                    return [
//                                        'id' => $assignment->id,
//                                        'employee_name' => $employee?->name,
//                                        'designation' => $employee?->designation?->title,
//                                        'status' => $assignment->status,
//                                    ];
//                                }),
//                            ];
//                        }),
//                    ],
//                ];
//            });
//
//        return Inertia::render('Backend/Events/Index', [
//            'events' => $events,
//        ]);
//    }

    public function jsonIndex(Request $request){

    }

    public function store(Request $request)
    {
//        dd($request);
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'date' => 'required|date',
            'start_time' => 'required',
            'end_time' => 'required',
            'location' => 'required|string|max:255',
            'status' => 'required|string|in:upcoming,completed,cancelled',
            'guests' => 'array',
            'assignments' => 'array'
        ]);

        DB::beginTransaction();
        try {
            // 1️⃣ Create the event
            $event = Event::create([
                'title' => $validated['title'],
                'date' => $validated['date'],
                'start_time' => $validated['start_time'],
                'end_time' => $validated['end_time'],
                'location' => $validated['location'],
                'status' => $validated['status'],
            ]);

            // 2️⃣ Create guests
            if (!empty($validated['guests'])) {
                foreach ($validated['guests'] as $guest) {
                    $event->guests()->create([
                        'role' => $guest['role'] ?? null,
                        'name' => $guest['name'] ?? null,
                        'designation' => $guest['designation'] ?? null,
                        'organization' => $guest['organization'] ?? null,
                        'contact' => $guest['contact'] ?? null,
                    ]);
                }
            }

            // 3️⃣ Create event_duties and assignments
            if (!empty($validated['assignments'])) {
                foreach ($validated['assignments'] as $assignmentGroup) {
                    $dutyId = $assignmentGroup['duty']['id'] ?? null;
                    if (!$dutyId) continue;

                    // Create EventDuty
                    $eventDuty = $event->eventDuties()->firstOrCreate([
                        'duty_id' => $dutyId
                    ]);

                    // Assign multiple employees
                    if (!empty($assignmentGroup['employees'])) {
                        foreach ($assignmentGroup['employees'] as $empData) {
                            if (!empty($empData['employee_id'])) {
                                $eventDuty->assignments()->create([
                                    'employee_id' => $empData['employee_id'],
                                    'status' => 'assigned',
                                ]);
                            }
                        }
                    }
                }
            }

            DB::commit();

            return redirect()->route('events.index')->with('success', 'Event created successfully!');
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);
            return back()->withErrors(['error' => 'Failed to create event.']);
        }
    }



    public function availableEmployees(Request $request)
    {
        $validated = $request->validate([
            'start_time' => 'required',
            'end_time' => 'required',
        ]);

        $start = Carbon::parse($validated['start_time']);
        $end = Carbon::parse($validated['end_time']);

        $unavailableEmployeeIds = EventAssignment::whereHas('eventDuty.event', function ($query) use ($start, $end) {
            $query->where(function ($q) use ($start, $end) {
                $q->whereBetween('start_time', [$start, $end])
                    ->orWhereBetween('end_time', [$start, $end])
                    ->orWhere(function ($q2) use ($start, $end) {
                        $q2->where('start_time', '<=', $start)
                            ->where('end_time', '>=', $end);
                    });
            });
        })->pluck('employee_id');

        $availableEmployees = Employee::with('designation')
            ->whereNotIn('id', $unavailableEmployeeIds)
            ->where('status', 'active')
            ->get();

        return response()->json($availableEmployees);
    }

    public function update(Request $request, Event $event)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'date' => 'required|date',
            'start_time' => 'required',
            'end_time' => 'required',
            'location' => 'required|string|max:255',
            'status' => 'required|string|in:upcoming,completed,cancelled',
            'guests' => 'array',
            'assignments' => 'array'
        ]);

        DB::beginTransaction();
        try {
            // 1️⃣ Update event
            $event->update([
                'title' => $validated['title'],
                'date' => $validated['date'],
                'start_time' => $validated['start_time'],
                'end_time' => $validated['end_time'],
                'location' => $validated['location'],
                'status' => $validated['status'],
            ]);

            // 2️⃣ Sync guests
            $event->guests()->delete();
            if (!empty($validated['guests'])) {
                foreach ($validated['guests'] as $guest) {
                    $event->guests()->create([
                        'role' => $guest['role'] ?? null,
                        'name' => $guest['name'] ?? null,
                        'designation' => $guest['designation'] ?? null,
                        'organization' => $guest['organization'] ?? null,
                        'contact' => $guest['contact'] ?? null,
                    ]);
                }
            }

            // 3️⃣ Sync duties and assignments
            if (!empty($validated['assignments'])) {
                foreach ($validated['assignments'] as $assignmentGroup) {
                    $dutyId = $assignmentGroup['duty']['id'] ?? null;
                    if (!$dutyId) continue;

                    // Find or create EventDuty
                    $eventDuty = $event->eventDuties()->firstOrCreate([
                        'duty_id' => $dutyId
                    ]);

                    // Delete old assignments for this duty
                    $eventDuty->assignments()->delete();

                    // Assign new employees
                    if (!empty($assignmentGroup['employees'])) {
                        foreach ($assignmentGroup['employees'] as $empData) {
                            if (!empty($empData['employee_id'])) {
                                $eventDuty->assignments()->create([
                                    'employee_id' => $empData['employee_id'],
                                    'status' => 'assigned',
                                ]);
                            }
                        }
                    }
                }
            }

            DB::commit();

            return redirect()->route('events.index')->with('success', 'Event updated successfully!');
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);
            return back()->withErrors(['error' => 'Failed to update event.']);
        }
    }

    public function destroy(Event $event)
    {
        $event->delete(); // or $event->forceDelete() if needed
        return redirect()->back()->with('success', 'Event deleted successfully!');
    }




}
