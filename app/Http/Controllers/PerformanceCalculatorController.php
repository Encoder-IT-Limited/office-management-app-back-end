<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;

class PerformanceCalculatorController extends Controller
{
    use ApiResponseTrait;

    public function projectContributions(Project $project): \Illuminate\Http\JsonResponse
    {
        $project->load('users');
        $project->load('tasks');

        $project->users->map(function ($user) use ($project) {
            $user->total_tasks = $project->tasks->count();
            $user->completed_tasks = $project->tasks->where('status', 'completed')->count();
            $user->incompleted_tasks = $project->tasks->where('status', 'incomplete')->count();
            $user->performance = $user->completed_tasks / $user->total_tasks * 100;
        });

        return $this->success('Project contributions', $project->users);
    }
}
