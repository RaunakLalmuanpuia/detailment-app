<?php

namespace App\Repositories;

use App\Models\Duty;
use App\Models\Employee;
use App\Models\Event;
use App\Models\EventAssignment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class EventRepository
{
    public function getAllEvents()
    {
        $events = Event::with([
            'eventDuties.duty',
            'eventDuties.assignments.employee.designation',
            'guests'
        ])->get();

        return $events->map(function ($event) {
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
    }

    public function getDuties()
    {
        return Duty::all();
    }

    public function getEmployees()
    {
        return Employee::with('designation')->get();
    }

    public function store(array $data)
    {
        DB::beginTransaction();
        try {
            $event = Event::create([
                'title' => $data['title'],
                'date' => $data['date'],
                'start_time' => $data['start_time'],
                'end_time' => $data['end_time'],
                'location' => $data['location'],
                'status' => $data['status'],
            ]);

            if (!empty($data['guests'])) {
                foreach ($data['guests'] as $guest) {
                    $event->guests()->create($guest);
                }
            }

            if (!empty($data['assignments'])) {
                foreach ($data['assignments'] as $assignmentGroup) {
                    $dutyId = $assignmentGroup['duty']['id'] ?? null;
                    if (!$dutyId) continue;

                    $eventDuty = $event->eventDuties()->firstOrCreate([
                        'duty_id' => $dutyId
                    ]);

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
            return $event;
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function update(Event $event, array $data)
    {
        DB::beginTransaction();
        try {
            $event->update([
                'title' => $data['title'],
                'date' => $data['date'],
                'start_time' => $data['start_time'],
                'end_time' => $data['end_time'],
                'location' => $data['location'],
                'status' => $data['status'],
            ]);

            $event->guests()->delete();
            if (!empty($data['guests'])) {
                foreach ($data['guests'] as $guest) {
                    $event->guests()->create($guest);
                }
            }

            if (!empty($data['assignments'])) {
                foreach ($data['assignments'] as $assignmentGroup) {
                    $dutyId = $assignmentGroup['duty']['id'] ?? null;
                    if (!$dutyId) continue;

                    $eventDuty = $event->eventDuties()->firstOrCreate([
                        'duty_id' => $dutyId
                    ]);

                    $eventDuty->assignments()->delete();

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
            return $event;
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function destroy(Event $event)
    {
        return $event->delete();
    }

    public function getAvailableEmployees($date, $start_time, $end_time, $ignoreEventId = null)
    {
        $start = Carbon::parse("$date $start_time");
        $end = Carbon::parse("$date $end_time");

        $unavailableIds = EventAssignment::whereHas('eventDuty.event', function ($query) use ($start, $end, $ignoreEventId) {
            $query->where(function ($q) use ($start, $end) {
                $q->whereBetween(DB::raw("CONCAT(date, ' ', start_time)"), [$start, $end])
                    ->orWhereBetween(DB::raw("CONCAT(date, ' ', end_time)"), [$start, $end])
                    ->orWhere(function ($q2) use ($start, $end) {
                        $q2->where(DB::raw("CONCAT(date, ' ', start_time)"), '<=', $start)
                            ->where(DB::raw("CONCAT(date, ' ', end_time)"), '>=', $end);
                    });
            });

            // Ignore current event if editing
            if ($ignoreEventId) {
                $query->where('id', '<>', $ignoreEventId);
            }
        })->pluck('employee_id');

        return Employee::with('designation')
            ->where('status', 'active')
            ->whereNotIn('id', $unavailableIds)
            ->get();
    }


    /**
     * Check if employee is already busy during this slot.
     */
    public function isEmployeeBusy($employeeId, $date, $start_time, $end_time, $ignoreEventId = null): bool
    {
        $start = Carbon::parse("$date $start_time");
        $end = Carbon::parse("$date $end_time");

        return EventAssignment::where('employee_id', $employeeId)
            ->whereHas('eventDuty.event', function ($query) use ($start, $end, $ignoreEventId) {
                $query->where(function ($q) use ($start, $end) {
                    $q->whereBetween(DB::raw("CONCAT(date, ' ', start_time)"), [$start, $end])
                        ->orWhereBetween(DB::raw("CONCAT(date, ' ', end_time)"), [$start, $end])
                        ->orWhere(function ($q2) use ($start, $end) {
                            $q2->where(DB::raw("CONCAT(date, ' ', start_time)"), '<=', $start)
                                ->where(DB::raw("CONCAT(date, ' ', end_time)"), '>=', $end);
                        });
                });

                // Ignore current event if provided
                if ($ignoreEventId) {
                    $query->where('id', '<>', $ignoreEventId);
                }
            })
            ->exists();
    }


}
