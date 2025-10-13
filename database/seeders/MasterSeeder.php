<?php

namespace Database\Seeders;

use App\Models\Duty;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\Event;
use App\Models\EventAssignment;
use App\Models\EventDuty;
use App\Models\EventGuest;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;

class MasterSeeder extends Seeder
{
    public function run(): void
    {
        // ----------------------
        // 1. Duties
        // ----------------------
        $duties = [
            ['name' => 'Attending Offices', 'min_required' => 2],
            ['name' => 'Press Coverage', 'min_required' => 2],
            ['name' => 'Video Coverage', 'min_required' => 2],
            ['name' => 'Photo Coverage', 'min_required' => 2],
            ['name' => 'P.A. System', 'min_required' => 1],
            ['name' => 'Driver/Vehicle', 'min_required' => 3],
            ['name' => 'Recording', 'min_required' => 1],
        ];

        foreach ($duties as $duty) {
            Duty::firstOrCreate(['name' => $duty['name']], $duty);
        }

        // ----------------------
        // 2. Designations
        // ----------------------
        $designations = [
            ['title' => 'Office Representative', 'duty_type' => 'Attending Offices'],
            ['title' => 'Press Officer', 'duty_type' => 'Press Coverage'],
            ['title' => 'Videographer', 'duty_type' => 'Video Coverage'],
            ['title' => 'Photographer', 'duty_type' => 'Photo Coverage'],
            ['title' => 'Sound Technician', 'duty_type' => 'P.A. System'],
            ['title' => 'Driver', 'duty_type' => 'Driver/Vehicle'],
            ['title' => 'Archivist', 'duty_type' => 'Recording'],
        ];

        foreach ($designations as $designation) {
            Designation::firstOrCreate(['title' => $designation['title']], $designation);
        }

        // ----------------------
        // 3. Employees (5 per designation)
        // ----------------------
        foreach ($designations as $designationData) {
            $designation = Designation::where('title', $designationData['title'])->first();
            if (! $designation) continue;

            for ($i = 1; $i <= 5; $i++) {
                $name = "{$designation->title} {$i}";
                $email = strtolower(str_replace(' ', '_', $designation->title)) . "_{$i}@example.com";

                $user = User::firstOrCreate(
                    ['email' => $email],
                    [
                        'name' => $name,
                        'mobile' => fake()->unique()->numerify('9#########'),
                        'password' => Hash::make('password'),
                    ]
                );

                if (! $user->hasRole('Employee')) {
                    $user->assignRole('Employee');
                }

                Employee::firstOrCreate(
                    ['user_id' => $user->id],
                    [
                        'name' => $name,
                        'designation_id' => $designation->id,
                        'contact_info' => $email,
                        'status' => 'active',
                    ]
                );
            }
        }

        // ----------------------
        // 4. Events (10 events, with overlaps)
        // ----------------------

        $baseDate = Carbon::create(2025, 10, 5); // Starting in October 2025
        $events = [];

        for ($i = 1; $i <= 10; $i++) {
            // Spread events across October, November, and December
            // Each set of ~3–4 events per month
            $month = match(true) {
                $i <= 3 => 10, // October
                $i <= 6 => 11, // November
                default => 12, // December
            };

            $baseDate = Carbon::create(2025, $month, 5);

            // Create overlapping pattern (every 2nd event overlaps previous)
            $date = $baseDate->copy()->addDays(($i % 2 === 0) ? $i - 2 : $i - 1);

            // Randomize time slightly (9–11 AM start, 4-hour duration)
            $start = Carbon::createFromTime(9 + ($i % 3), 0);
            $end = $start->copy()->addHours(4);

            $events[] = [
                'title' => "Event {$i}",
                'date' => $date->toDateString(),
                'start_time' => $start->format('H:i:s'),
                'end_time' => $end->format('H:i:s'),
                'location' => "Venue " . Str::upper(Str::random(3)),
                'status' => 'upcoming',
            ];
        }

        foreach ($events as $event) {
            Event::firstOrCreate(['title' => $event['title']], $event);
        }


        // ----------------------
        // 5. Event Guests
        // ----------------------
        $eventTitles = Event::pluck('title');
        foreach ($eventTitles as $title) {
            $event = Event::firstWhere('title', $title);
            EventGuest::firstOrCreate(
                ['event_id' => $event->id, 'role' => 'chief_guest'],
                [
                    'name' => fake()->name(),
                    'designation' => fake()->jobTitle(),
                    'organization' => fake()->company(),
                    'contact' => fake()->unique()->safeEmail(),
                ]
            );
        }

        // ----------------------
        // 6. Event Duties
        // ----------------------
        $duties = Duty::all();
        $events = Event::all();

        foreach ($events as $event) {
            foreach ($duties as $duty) {
                EventDuty::firstOrCreate([
                    'event_id' => $event->id,
                    'duty_id' => $duty->id,
                ]);
            }
        }

        // ----------------------
        // 7. Event Assignments (avoid conflicts)
        // ----------------------
        $employees = Employee::with('designation')->get();
        $eventDuties = EventDuty::with(['event', 'duty'])->get();

        foreach ($eventDuties as $eventDuty) {
            $eligibleEmployees = $employees->filter(function ($emp) use ($eventDuty) {
                return $emp->designation->duty_type === $eventDuty->duty->name;
            });

            foreach ($eligibleEmployees as $emp) {
                // Check if employee already assigned to overlapping event
                $hasConflict = EventAssignment::where('employee_id', $emp->id)
                    ->whereHas('eventDuty.event', function ($q) use ($eventDuty) {
                        $q->where('date', $eventDuty->event->date)
                            ->where(function ($q2) use ($eventDuty) {
                                $q2->whereBetween('start_time', [$eventDuty->event->start_time, $eventDuty->event->end_time])
                                    ->orWhereBetween('end_time', [$eventDuty->event->start_time, $eventDuty->event->end_time]);
                            });
                    })
                    ->exists();

                if (! $hasConflict) {
                    EventAssignment::firstOrCreate(
                        ['event_duty_id' => $eventDuty->id, 'employee_id' => $emp->id],
                        ['status' => 'assigned']
                    );
                    break; // assign only one per event-duty
                }
            }
        }

    }
}
