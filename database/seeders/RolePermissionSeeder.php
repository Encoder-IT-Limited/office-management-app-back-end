<?php

namespace Database\Seeders;

use App\Models\Permission;
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
            'can-user-index',
            'can-user-store',
            'can-user-show',
            'can-user-update',
            'can-user-delete',
            'can-skill-delete',
            'can-skill-index',
            'can-role-index',
            'can-role-store',
            'can-role-show',
            'can-role-update',
            'can-role-delete',
            'can-skill-store',
            'can-skill-update',
            'can-skill-show',
            'can-project-show',
            'can-project-index',
            'can-project-store',
            'can-project-update',
            'can-project-delete',
            'can-leave-index',
            'can-leave-delete',
            'can-leave-store',
            'can-leave-update',
            'can-note-index',
            'can-note-store',
            'can-note-update',
            'can-note-delete',
            'can-see-month',

        ];
        $role = Role::findOrFail(1);
        foreach ($permission as $p) {
            // foreach($permission[$role->slug] as $permission){
            //     Permission::where('slug', $permission)->first()->attach($role->id);
            // }

            $permission_ids = Permission::where('slug', $p)->pluck('id');
            $role->permissions()->attach($permission_ids);
        }
    }
}
