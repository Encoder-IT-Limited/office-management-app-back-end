<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\ProjectStatus;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $permissions = [
            'read-user',
            'store-user',
            'show-user',
            'update-user',
            'block-user',
            'unblock-user',
            'delete-user',

            'read-skill',
            'store-skill',
            'update-skill',
            'show-skill',
            'delete-skill',

            'read-role',
            'store-role',
            'show-role',
            'update-role',
            'delete-role',

            'read-project',
            'store-project',
            'show-project',
            'update-project',
            'delete-project',

            'read-leave',
            'store-leave',
            'update-leave',
            'show-leave',
            'delete-leave',
            'status-update-leave',

            'read-note',
            'store-note',
            'show-note',
            'update-note',
            'delete-note',

            'read-reminder',
            'store-reminder',
            'show-reminder',
            'update-reminder',
            'delete-reminder',

            'read-break',
            'store-break',
            'show-break',
            'update-break',
            'delete-break',

            'read-attendance',
            'update-attendance',
            'checkin-attendance',
            'checkout-attendance',
            'read-delays',

            'read-calendar',
            'see-month',
            'see-project-budget'
        ];
        foreach ($permissions as $slug) {
            Permission::updateOrCreate(['slug' => $slug], [
                'name' => Str::replace('-', ' ', Str::ucfirst($slug))
            ]);
        }

        Role::updateOrCreate(['slug' => 'admin'], [
            'name' => 'Admin'
        ]);
        User::updateOrCreate(['email' => 'admin@test.com'], [
            'name' => 'Admin',
            'phone' => '123456789',
            'password' => Hash::make('12345678'),
            'designation' => 'Admin',
            'status' => 'active',
        ]);

        $role = Role::where('slug', 'admin')->first();

        $role->users()->sync(1);

        $role->permissions()->attach(Permission::all()->pluck('id')->toArray());

        $status = [
            ['status' => "lead"],
            ['status' => "pending"],
            ['status' => "on-going"],
            ['status' => "accepted"],
            ['status' => "rejected"],
            ['status' => "Completed"],
        ];

        foreach ($status as $p) {
            ProjectStatus::create($p);
        }
    }
}
