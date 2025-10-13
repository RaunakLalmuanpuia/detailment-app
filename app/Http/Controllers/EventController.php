<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Repositories\EventRepository;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EventController extends Controller
{
    protected EventRepository $repo;

    public function __construct(EventRepository $repo)
    {
        $this->repo = $repo;
    }

    public function index()
    {
        $events = $this->repo->getAllEvents();
        $duties = $this->repo->getDuties();
        $employees = $this->repo->getEmployees();

        return Inertia::render('Backend/Events/Index', compact('events', 'duties', 'employees'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'date' => 'required|date',
            'start_time' => 'required',
            'end_time' => 'required',
            'location' => 'required|string|max:255',
            'status' => 'required|string|in:upcoming,completed,cancelled',
            'guests' => 'array',
            'assignments' => 'array',
        ]);

        try {
            // 🔍 Check for busy employees before creating event
            foreach ($validated['assignments'] ?? [] as $assignment) {
                $employeeId = $assignment['employee_id'] ?? null;
                if ($employeeId && $this->repo->isEmployeeBusy($employeeId, $validated['date'], $validated['start_time'], $validated['end_time'])) {
                    return back()->withErrors([
                        'error' => 'Employee ID ' . $employeeId . ' is already assigned to another event during this time.'
                    ])->withInput();
                }
            }

            $this->repo->store($validated);
            return redirect()->route('events.index')->with('success', 'Event created successfully!');
        } catch (\Throwable $e) {
            report($e);
            return back()->withErrors(['error' => 'Failed to create event.']);
        }
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
            'assignments' => 'array',
        ]);

        try {
            // 🔍 Check for busy employees before updating event
            foreach ($validated['assignments'] ?? [] as $assignment) {
                $employeeId = $assignment['employee_id'] ?? null;
                if ($employeeId && $this->repo->isEmployeeBusy($employeeId, $validated['date'], $validated['start_time'], $validated['end_time'], $event->id)) {
                    return back()->withErrors([
                        'error' => 'Employee ID ' . $employeeId . ' is already assigned to another event during this time.'
                    ])->withInput();
                }
            }

            $this->repo->update($event, $validated);
            return redirect()->route('events.index')->with('success', 'Event updated successfully!');
        } catch (\Throwable $e) {
            report($e);
            return back()->withErrors(['error' => 'Failed to update event.']);
        }
    }


    public function destroy(Event $event)
    {
        $this->repo->destroy($event);
        return redirect()->back()->with('success', 'Event deleted successfully!');
    }

    public function availableEmployees(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'start_time' => 'required',
            'end_time' => 'required',
            'event_id' => 'nullable|integer',
        ]);

        $employees = $this->repo->getAvailableEmployees(
            $validated['date'],
            $validated['start_time'],
            $validated['end_time'],
             $validated['event_id'] ?? null
        );

        return response()->json($employees);
    }

}
