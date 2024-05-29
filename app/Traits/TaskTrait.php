<?php

namespace App\Traits;

use App\Models\LabelStatus;
use Illuminate\Support\Facades\Auth;

trait TaskTrait
{
    public $taskWith = ['author', 'assignee', 'project', 'status', 'labels', 'comments'];

    public function setTaskStatus($task, $reqStatus = null)
    {
        $status = LabelStatus::taskOnly()->statusOnly()->byProject($task->project_id)->byTitle($reqStatus)->first();

        if (!$status) $status = LabelStatus::getTaskDefaultStatus();

        if (!$reqStatus) $reqStatus = $status->title;

        $status = LabelStatus::updateOrCreate([
            'project_id' => $task->project_id,
            'title' => $reqStatus,
            'franchise' => 'task',
            'type' => 'status',
        ], [
            'color' => $status?->color,
        ]);

        $task->status()->sync([$status->id => [
            'color' => $status?->color,
            'list_order' => $status?->list_order,
        ]]);

        return $task->refresh();
    }

    public function setTaskLabel($task, $reqLabel)
    {
        $label = LabelStatus::taskOnly()->labelOnly()->byProject($task->project_id)->byTitle($reqLabel)->first();

        if (!$label) $label = LabelStatus::getTaskDefaultStatus();

        $label = LabelStatus::updateOrCreate([
            'project_id' => $task->project_id,
            'title' => $reqLabel,
            'franchise' => 'task',
            'type' => 'label',
        ], [
            'color' => $label->color,
        ]);

        $label = LabelStatus::taskOnly()->labelOnly()->byProject($task->project_id)->byTitle($reqLabel)->first();

        if ($task)
            $task->labels()->syncWithoutDetaching([$label->id => [
                'color' => $label->color,
            ]]);
        return $task;
    }
}
