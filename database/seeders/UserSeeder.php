<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        User::query()->upsert([
            ['id'=>1,'name'=>'admin','email'=>'admin@mail.com','mobile'=>'0000000001','password'=>Hash::make('password')],
            ['id'=>2,'name'=>'organizer','email'=>'organizer@mail.com','mobile'=>'0000000002','password'=>Hash::make('password')],
            ['id'=>3,'name'=>'employee','email'=>'employee@mail.com','mobile'=>'0000000003','password'=>Hash::make('password')],
            ['id'=>4,'name'=>'viewer','email'=>'viewer@mail.com','mobile'=>'0000000004','password'=>Hash::make('password')],
        ], ['id']);


        $admin=User::query()->find(1);
        $admin->assignRole('Admin');

        $organizer=User::query()->find(2);
        $organizer->assignRole('Organizer');

        $employee=User::query()->find(3);
        $employee->assignRole('Employee');

        $viewer=User::query()->find(4);
        $viewer->assignRole('Viewer');
    }
}
