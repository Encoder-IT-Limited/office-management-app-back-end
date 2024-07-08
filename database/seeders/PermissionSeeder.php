<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PermissionSeeder extends Seeder
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
            'read-my-user',
            'read-client-user',
            'store-user',
            'show-user',
            'update-user',
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
            'read-my-project',
            'read-client-project',
            'store-project',
            'show-project',
            'update-project',
            'delete-project',
            'update-project-status',

            'read-leave',
            'read-my-leave',
            'read-client-leave',
            'store-leave',
            'update-leave',
            'show-leave',
            'delete-leave',
            'status-update-leave',

            'read-note',
            'read-my-note',
            'read-client-note',
            'store-note',
            'show-note',
            'update-note',
            'delete-note',

            'read-reminder',
            'read-my-reminder',
            'read-client-reminder',
            'store-reminder',
            'show-reminder',
            'update-reminder',
            'delete-reminder',

            'read-break',
            'read-my-break',
            'read-client-break',
            'store-break',
            'show-break',
            'update-break',
            'delete-break',

            'view-all-attendance',
            'view-my-attendance',
            'view-developer-attendance',
//            'read-attendance',
//            'read-my-attendance',
//            'read-developer-attendance',
            'update-attendance',
            'checkin-attendance',
            'checkout-attendance',
            'delete-attendance',
            'read-delays',

            'read-calendar',
            'see-month',
            'see-project-budget',

            'read-task',
            'read-my-task',
            'read-client-task',
            'store-task',
            'show-task',
            'update-task',
            'delete-task',

            'read-user-note',
            'store-user-note',
            'update-user-note',
            'delete-user-note',
            'read-trashed-user-note',
            'restore-user-note',
            'force-delete-user-note',

            'read-project-note',
            'store-project-note',
            'update-project-note',
            'delete-project-note',
            'read-trashed-project-note',
            'restore-project-note',
            'force-delete-project-note',

            'read-user-reminder',
            'store-user-reminder',
            'update-user-reminder',
            'delete-user-reminder',
            'read-trashed-user-reminder',
            'restore-user-reminder',
            'force-delete-user-reminder',

            'read-project-bill',
            'store-project-bill',
            'update-project-bill',
            'delete-project-bill',
            'read-trashed-project-bill',
            'restore-project-bill',
            'force-delete-project-bill',
        ];

        Role::updateOrCreate(['slug' => 'admin'], [
            'name' => 'Admin'
        ]);
        Role::updateOrCreate(['slug' => 'developer'], [
            'name' => 'Developer'
        ]);
        Role::updateOrCreate(['slug' => 'client'], [
            'name' => 'Client'
        ]);

        foreach ($permissions as $slug) {
            Permission::updateOrCreate(['slug' => $slug], [
                'name' => Str::replace('-', ' ', Str::ucfirst($slug))
            ]);
        }


        $role = Role::where('slug', 'admin')->first();
        $user = User::where('email', 'admin@admin.com')->first();
        if ($user) {
//            $role->permissions()->detach(Permission::all()->pluck('id')->toArray());

            $roleAttachedIds = $role->users()->pluck('users.id')->toArray();
            if (!in_array($user->id, $roleAttachedIds)) $role->users()->attach($user->id);
        }
        $role->permissions()->syncWithoutDetaching(Permission::all()->pluck('id')->toArray());
    }
}
