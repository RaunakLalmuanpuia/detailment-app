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

class MasterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
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
        // 3. Employees (with linked users)
        // ----------------------
        $employees = [
            ['name' => 'Alice Sharma', 'designation' => 'Office Representative', 'contact_info' => 'alice@example.com'],
            ['name' => 'Ravi Kumar', 'designation' => 'Press Officer', 'contact_info' => 'ravi@example.com'],
            ['name' => 'Neha Singh', 'designation' => 'Videographer', 'contact_info' => 'neha@example.com'],
            ['name' => 'Amit Patel', 'designation' => 'Photographer', 'contact_info' => 'amit@example.com'],
            ['name' => 'Sunita Rao', 'designation' => 'Sound Technician', 'contact_info' => 'sunita@example.com'],
            ['name' => 'Vikram Joshi', 'designation' => 'Driver', 'contact_info' => 'vikram@example.com'],
            ['name' => 'Meena Iyer', 'designation' => 'Archivist', 'contact_info' => 'meena@example.com'],
        ];

        foreach ($employees as $emp) {
            $designation = Designation::where('title', $emp['designation'])->first();
            if (! $designation) continue;

            $user = User::firstOrCreate(
                ['email' => $emp['contact_info']],
                [
                    'name' => $emp['name'],
                    'mobile' => fake()->unique()->numerify('##########'),
                    'password' => Hash::make('password'),
                ]
            );
            if (! $user->hasRole('Employee')) $user->assignRole('Employee');

            Employee::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'name' => $emp['name'],
                    'designation_id' => $designation->id,
                    'contact_info' => $emp['contact_info'],
                    'status' => 'active',
                ]
            );
        }

        // ----------------------
        // 4. Events (4 events around October 2025)
        // ----------------------
        $events = [
            ['title'=>'Cultural Fest', 'date'=>'2025-10-05','start_time'=>'10:00:00','end_time'=>'14:00:00','location'=>'Main Hall','status'=>'upcoming'],
            ['title'=>'Science Exhibition', 'date'=>'2025-10-12','start_time'=>'09:00:00','end_time'=>'16:00:00','location'=>'Exhibition Ground','status'=>'upcoming'],
            ['title'=>'Annual Sports Day', 'date'=>'2025-10-20','start_time'=>'08:30:00','end_time'=>'17:00:00','location'=>'Sports Ground','status'=>'upcoming'],
            ['title'=>'Teachers Day Celebration', 'date'=>'2025-10-31','start_time'=>'11:00:00','end_time'=>'13:30:00','location'=>'Auditorium','status'=>'upcoming'],
        ];

        foreach ($events as $event) {
            Event::firstOrCreate(['title' => $event['title']], $event);
        }

        // ----------------------
        // 5. Event Guests (1 chief guest or chairman per event)
        // ----------------------
        $eventGuests = [
            'Cultural Fest' => [
                ['role'=>'chief_guest','name'=>'Dr. Arjun Malhotra','designation'=>'Scientist','organization'=>'Research Institute','contact'=>'arjun@example.com']
            ],
            'Science Exhibition' => [
                ['role'=>'chairman','name'=>'Ms. Leena Kapoor','designation'=>'Director','organization'=>'Science Board','contact'=>'leena@example.com']
            ],
            'Annual Sports Day' => [
                ['role'=>'chief_guest','name'=>'Mr. Rajesh Singh','designation'=>'Sports Officer','organization'=>'Sports Authority','contact'=>'rajesh@example.com']
            ],
            'Teachers Day Celebration' => [
                ['role'=>'chairman','name'=>'Dr. Meera Nair','designation'=>'Principal','organization'=>'High School','contact'=>'meera@example.com']
            ],
        ];

        foreach ($eventGuests as $eventTitle => $guests) {
            $event = Event::firstWhere('title', $eventTitle);
            foreach ($guests as $guest) {
                EventGuest::firstOrCreate(
                    ['event_id'=>$event->id, 'role'=>$guest['role'], 'name'=>$guest['name']],
                    $guest
                );
            }
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
        // 7. Event Assignments
        // ----------------------
        $eventDuties = EventDuty::all();
        $employees   = Employee::all();

        foreach ($eventDuties as $eventDuty) {
            $emp = $employees->firstWhere(fn($e) => $e->designation->duty_type === $eventDuty->duty->name);
            if ($emp) {
                EventAssignment::firstOrCreate(
                    ['event_duty_id'=>$eventDuty->id,'employee_id'=>$emp->id],
                    ['status'=>'assigned']
                );
            }
        }
    }
}
