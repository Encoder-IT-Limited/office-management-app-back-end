<?php

namespace Database\Seeders;

use App\Models\LabelStatus;
use Illuminate\Database\Seeder;

class LabelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

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
