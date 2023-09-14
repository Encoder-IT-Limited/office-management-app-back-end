<?php

namespace App\Http\Controllers;

use App\Models\LabelStatus;
use App\Models\Project;
use App\Models\ProjectStatus;
use App\Models\ProjectTask;
use App\Models\Task;
use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ProjectController extends Controller
{

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
            'status'
        ];
    }

    public function index(Request $request)
    {
        $queries = Project::with($this->withProject)->filterByRole();

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
            'tasks.*.start_date'  => 'required|date_format:Y-m-d H:i:s',
            'tasks.*.end_date'    => 'required|date_format:Y-m-d H:i:s',

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

                $team->teamUsers()->syncWithoutDetaching($reqTeam['user_ids']);
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

                if (!$task->status || !isset($reqTask['status'])) {
                    $initialStatus = LabelStatus::getTaskDefaultStatus();
                    $task->status()->sync([$initialStatus->id => [
                        'color' => $initialStatus->color,
                    ]]);
                } else {
                    $reqStatus = $reqTask['status'];

                    $default = LabelStatus::getTaskDefaultStatus();

                    $status = LabelStatus::updateOrCreate([
                        'project_id' => $project->id,
                        'title' => $reqStatus,
                    ], [
                        'color' => $default->color,
                        'franchise' => 'task',
                        'type' => 'status',
                    ]);

                    $task->status()->sync([$status->id => [
                        'color' => $status->color,
                    ]]);
                }

                if (isset($reqTask['labels'])) {
                    foreach ($reqTask['labels'] as $reqLabel) {
                        $default = LabelStatus::getTaskDefaultStatus();

                        $label = LabelStatus::updateOrCreate([
                            'project_id' => $project->id,
                            'title' => $reqLabel,
                        ], [
                            'color' => $default->color,
                            'franchise' => 'task',
                            'type' => 'label',
                        ]);

                        $label = LabelStatus::taskOnly()->labelOnly()->byProject($project->id)->byTitle($reqLabel)->first();

                        if ($label)
                            $task->labels()->syncWithoutDetaching([$label->id => [
                                'color' => $label->color,
                            ]]);
                    }
                }
            }
        }

        $status = LabelStatus::findOrFail($request->status_id);
        if (!$status) $status =  LabelStatus::getProjectDefaultStatus();
        $project->status()->sync([$status->id => [
            'color' => $status->color,
        ]]);

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

    public function update(Request $request)
    {
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

        if (isset($request->developer_task)) {
            $project->projectTasks()->delete();
            foreach ($request->developer_task as $developerTask) {
                $projectTask               = new ProjectTask();
                $projectTask->task         = $developerTask['task'];
                $projectTask->project_id   = $request->project_id;
                $projectTask->developer_id = $developerTask['developer_id'];
                $projectTask->start_date   = $developerTask['start_date'];
                $projectTask->end_date     = $developerTask['end_date'];
                $projectTask->save();
            }
        }

        return response()->json([
            'message'  => 'Success Updated',
            'project' => $project
        ], 201);
    }

    public function destroy($id)
    {
        $project = Project::findOrFail($id);
        $project->projectTasks()->delete();
        $project->delete();

        return response()->json([
            'message' => 'Deleted Success',
        ], 200);
    }

    public function projectstatus(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'project_id' => 'required|exists:projects,id',
            'message' => 'required|status'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 500);
        }

        $project = Project::findOrFail($request->project_id);
        $project->update(['message' => $request->status]);

        return response()->json([
            'message'  => 'Success Updated',
            'project' => $project
        ], 201);
    }

    public function getStatus()
    {
        $status = ProjectStatus::all();
        return response()->json([
            'status' => $status
        ]);
    }
}
