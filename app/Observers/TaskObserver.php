<?php

namespace App\Observers;

use App\Models\BillableTime;
use App\Models\Task;
use Carbon\Carbon;

class TaskObserver
{
    /**
     * Handle the Task "created" event.
     *
     * @param Task $task
     * @return void
     */
    public function created(Task $task)
    {
        //
    }

    /**
     * Handle the Task "updated" event.
     *
     * @param Task $task
     * @return void
     */
    public function updated(Task $task)
    {
        if ($task->status === 'Completed') {
            BillableTime::create([
                'task_id' => $task->id,
                'project_id' => $task->project_id,
                'user_id' => $task->assignee_id,
                'site' => $task->site,
                'date' => $task->end_date,
                'time_spent' => Carbon::parse($task->end_date)->diffInMinutes($task->start_date),
                'given_time' => $task->estimated_time,
                'comment' => null,
                'screenshot' => null,
                'is_freelancer' => false,
            ]);
        }
    }

    /**
     * Handle the Task "deleted" event.
     *
     * @param Task $task
     * @return void
     */
    public function deleted(Task $task)
    {
        //
    }

    /**
     * Handle the Task "restored" event.
     *
     * @param Task $task
     * @return void
     */
    public function restored(Task $task)
    {
        //
    }

    /**
     * Handle the Task "force deleted" event.
     *
     * @param Task $task
     * @return void
     */
    public function forceDeleted(Task $task)
    {
        //
    }
}
