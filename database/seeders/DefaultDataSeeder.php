<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\LabelStatus;
use App\Models\Permission;
use App\Models\ProjectStatus;
use App\Models\Role;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DefaultDataSeeder extends Seeder
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

            'read-attendance',
            'read-my-attendance',
            'read-client-attendance',
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
        ];

        Role::updateOrCreate(['slug' => 'admin'], [
            'name' => 'Admin'
        ]);
        $role = Role::where('slug', 'admin')->first();

        User::updateOrCreate(['email' => 'admin@admin.com'], [
            'name' => 'Admin',
            'phone' => '12345678',
            'password' => Hash::make('12345678'),
            'designation' => 'Admin',
            'status' => 'active',
        ]);
        $user = User::where('email', 'admin@admin.com')->first();

        $roleAttachedIds = $role->users()->pluck('users.id')->toArray();

        if (!in_array($user->id, $roleAttachedIds)) $role->users()->attach($user->id);

        foreach ($permissions as $slug) {
            Permission::updateOrCreate(['slug' => $slug], [
                'name' => Str::replace('-', ' ', Str::ucfirst($slug))
            ]);
        }
        $role->permissions()->syncWithoutDetaching(Permission::all()->pluck('id')->toArray());

        foreach (Attendance::all() as $attendance) {
            if (!$attendance->delay_time) {
                $time = $attendance->employee->delay_time;
                $date = Carbon::parse($attendance->check_in)->toDateString();
                $default_delay_time = Carbon::createFromFormat('Y-m-d H:i:s', $date . ' ' . $time, config('app.timezone'));
                $attendance->update(['delay_time' => $default_delay_time]);
            }
        }

        $status = [
            ['title' => "lead", 'color' => 'green', 'type' => 'status', 'franchise' => 'project'],
            ['title' => "pending", 'color' => 'green', 'type' => 'status', 'franchise' => 'project'],
            ['title' => "on-going", 'color' => 'green', 'type' => 'status', 'franchise' => 'project'],
            ['title' => "accepted", 'color' => 'green', 'type' => 'status', 'franchise' => 'project'],
            ['title' => "rejected", 'color' => 'green', 'type' => 'status', 'franchise' => 'project'],
            ['title' => "completed", 'color' => 'green', 'type' => 'status', 'franchise' => 'project'],
        ];

        // Added Project Default Status
        foreach ($status as $p) {
            LabelStatus::updateOrCreate($p);
        }

        // Added Task Default Status
        LabelStatus::updateOrCreate([
            'title' => 'Initialize',
            'color' => 'green',
            'type'  => 'status',
            'franchise' => 'task'
        ]);
    }
}
