<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Team;
use App\Models\Project;
use App\Traits\ProjectTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ProjectController extends Controller
{
    use ProjectTrait;

    public function index(Request $request)
    {
        $queries = Project::withData()->filteredByPermissions();

        $projects = $queries->latest()->paginate($request->per_page ?? 25);

        return response()->json([
            'projects' => $projects
        ], 200);
    }

    public function updateOrCreateProject(Request $request)
    {
        $this->validateWith([
            'id'         => 'sometimes|required|exists:projects,id',
            'name'       => 'required|string|unique:projects,name,' . $request->id,
            'budget'     => 'required',
            'start_date' => 'required|date',
            'end_date'   => 'required|date',
            'client_id'  => 'required|exists:users,id',
            'status_id'  => 'required|exists:label_statuses,id',

            'teams'            => 'sometimes|required|array',
            'teams.*.id'       => 'sometimes|required|exists:teams,id',
            'teams.*.title'    => 'sometimes|required',
            'teams.*.user_ids' => 'sometimes|required|array',

            'tasks'               => 'sometimes|required|array',
            'tasks.*.id'          => 'sometimes|required|exists:tasks,id',
            'tasks.*.title'       => 'required|string',
            'tasks.*.description' => 'required|string',
            'tasks.*.reference'   => 'sometimes|required|string',
            'tasks.*.assignee_id' => 'sometimes|required|exists:users,id',
            'tasks.*.start_date'  => 'required',
            'tasks.*.end_date'    => 'required',

            'tasks.*.labels'      => 'sometimes|required|array',
        ]);

        $project = Project::updateOrCreate(
            [
                'id' => $request->id ?? null,
                'client_id' => $request->client_id
            ],
            [
                'name' => $request->name,
                'budget' => $request->budget,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'client_id' => $request->client_id
            ]
        );
        $project = Project::findOrFail($request->id ?? $project->id);

        if ($request->has("teams")) {
            foreach ($request->teams as $reqTeam) {
                $team = Team::updateOrCreate([
                    'id' => $reqTeam['id'] ?? null,
                    'project_id' => $project->id
                ], [
                    'title' => $reqTeam['title']
                ]);

                $team->teamUsers()->sync($reqTeam['user_ids']);
            }
        }

        if ($request->has("tasks")) {
            foreach ($request->tasks as $reqTask) {
                $taskData = [
                    'title'       => $reqTask['title'],
                    'description' => $reqTask['description'],
                    'reference'   => $reqTask['reference'] ?? null,
                    'author_id'   => $reqTask['author_id'] ?? Auth::id(),
                    'assignee_id' => $reqTask['assignee_id'],
                    'start_date'  => $reqTask['start_date'],
                    'end_date'    => $reqTask['end_date'],
                ];
                $taskId = isset($reqTask['id']) ? $reqTask['id'] : null;

                $task = Task::updateOrCreate([
                    'id' => $taskId,
                    'project_id'  => $project->id,
                ], $taskData);
                $task = Task::findOrFail($taskId ?? $task->id);

                if (isset($reqTask['status'])) {
                    $this->setTaskStatus($task, $reqTask['status']);
                } else {
                    $this->setTaskStatus($task);
                }

                if (isset($reqTask['labels'])) {
                    foreach ($reqTask['labels'] as $reqLabel) {
                        $this->setTaskLabel($task, $reqLabel);
                    }
                }
            }
        }

        $this->setProjectStatus($project, $request->status_id ?? null);

        if ($request->has('labels')) {
            foreach ($request->labels as $reqLabel) {
                $project = $this->setProjectLabel($project, $reqLabel);
            }
        }

        if ($request->has('labels')) {
            foreach ($request->labels as $reqLabel) {
                $project = $this->setProjectLabel($project, $reqLabel);
            }
        }

        $project = Project::with($this->withProject)->find($project->id);

        return response()->json([
            'project' => $project
        ], 200);
    }

    public function show($id)
    {
        $project = Project::with($this->withProject)->findOrFail($id);

        return response()->json([
            'project' => $project
        ], 200);
    }

    public function updateProjectStatus(Request $request)
    {
        $this->validateWith([
            'project_id' => 'required|exists:projects,id',
            'status_id' => 'required|exists:label_statuses,id'
        ]);

        $project = Project::findOrFail($request->project_id);
        $project = $this->setProjectStatus($project, $request->status_id ?? null);

        return response()->json([
            'project' => $project
        ], 200);
    }

    public function update(Request $request)
    {
        return 'Deprecated';

        $validator = Validator::make($request->all(), [
            'name'       => 'required|string',
            'budget'     => 'required',
            'start_date' => 'required|date',
            'end_date'   => 'required|date',
            'status_id' => 'required|exists:project_statuses,id',
            'client_id'  => 'required|exists:users,id',
            'project_id' => 'required|exists:projects,id'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 500);
        }

        $project = Project::findOrFail($request->project_id);
        $project->update($validator->validated());

        return response()->json([
            'message'  => 'Success Updated',
            'project' => $project
        ], 201);
    }

    public function destroy($id)
    {
        $project = Project::findOrFail($id);
        $project->delete();

        try {
            $project->projectTasks()->delete();
        } catch (\Throwable $th) {
            //
        }

        return response()->json([
            'message' => 'Deleted Success',
        ], 200);
    }
}
