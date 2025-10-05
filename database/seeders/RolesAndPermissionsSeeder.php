<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'event-view','event-view-all', 'event-create', 'event-update', 'event-delete',
            'user-view', 'user-create', 'user-update', 'user-delete',
            'employee-view', 'employee-create', 'employee-update', 'employee-delete',
        ];

        // Create permissions
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Define roles and their permissions
        $roles = [

            'Admin' => [
                'event-view','event-view-all', 'event-create', 'event-update', 'event-delete',
                'user-view', 'user-create', 'user-update', 'user-delete',
                'employee-view', 'employee-create', 'employee-update', 'employee-delete',
            ],

            'Organizer'=>[
                'event-view','event-view-all', 'event-create', 'event-update', 'event-delete',
            ],

            'Employee'=>[
                'event-view','event-view-all',
            ],

            'Viewer'=>[
                'event-view','event-view-all',
            ]
        ];

        // Create roles and assign permissions
        foreach ($roles as $roleName => $rolePermissions) {
            $role = Role::firstOrCreate(['name' => $roleName]);
            $role->syncPermissions($rolePermissions);
        }


    }
}
