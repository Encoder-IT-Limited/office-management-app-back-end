<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\ProjectStatus;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $permission = [
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

            'read-attendance',
            'update-attendance',
            'checkin-attendance',
            'checkout-attendance',
            'read-delays',

            'read-calendar',
            'see-month',
            'see-project-budget'

        ];
        // $role = Role::findOrFail(1);
        // foreach ($permission as $p) {
        //     // foreach($permission[$role->slug] as $permission){
        //     //     Permission::where('slug', $permission)->first()->attach($role->id);
        //     // }

        //     $permission_ids = Permission::where('slug', $p)->pluck('id');
        //     $role->permissions()->attach($permission_ids);
        // }

        $status = [
            ['message' => "lead"],
            ['message' => "pending"],
            ['message' => "on-going"],
            ['message' => "accepted"],
            ['message' => "rejected"],
            ['message' => "Completed"],
        ];

        foreach ($status as $p) {
            ProjectStatus::create($p);
        }
    }
}
