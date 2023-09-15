<?php

namespace App\Traits;

use App\Models\LabelStatus;
use Illuminate\Support\Facades\Auth;

trait ProjectTrait
{
    use TaskTrait;

    private $withProject;
    public function __construct()
    {
        $this->withProject = [
            'client',
            'tasks' => function ($data) {
                $data->filterAccessable()->with('assignee', 'status', 'labels');
            },
            'teams' => function ($data) {
                $data->with('users');
            },
            'labels',
            'status'
        ];
    }

    public function setProjectStatus($project, $status_id = null)
    {
        $status = LabelStatus::find($status_id);
        if (!$status) $status =  LabelStatus::getProjectDefaultStatus();

        $project->status()->sync([$status->id => [
            'color' => $status->color,
        ]]);
        return $project;
    }

    public function setProjectLabel($project, $reqLabel)
    {
        $label = LabelStatus::projectOnly()->labelOnly()->byProject($project->id)->byTitle($reqLabel)->first();

        if (!$label) $label = LabelStatus::getprojectDefaultStatus();

        $label = LabelStatus::updateOrCreate([
            'project_id' => $project->id,
            'title' => $reqLabel,
            'franchise' => 'project',
            'type' => 'label',
        ], [
            'color' => $label->color,
        ]);

        $label = LabelStatus::projectOnly()->labelOnly()->byProject($project->id)->byTitle($reqLabel)->first();

        if ($label)
            $project->labels()->syncWithoutDetaching([$label->id => [
                'color' => $label->color,
            ]]);
        return $project;
    }
}
