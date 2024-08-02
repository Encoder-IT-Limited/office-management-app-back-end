<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProjectStoreRequest;
use App\Http\Requests\ProjectUpdateRequest;
use App\Http\Resources\ProjectCollection;
use App\Models\LabelStatus;
use App\Models\Task;
use App\Models\Team;
use App\Models\Project;
use App\Traits\ApiResponseTrait;
use App\Traits\ProjectTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ProjectController extends Controller
{
    use ProjectTrait, ApiResponseTrait;

    public function index(Request $request)
    {
        $user = auth()->user();
        if ($user->hasRole('admin')) {
//        if (auth()->user()->roles->contains('slug', 'admin')) {
            $queries = Project::with('users', 'status', 'notes')->withData();
        } else {
            $queries = Project::with(['client', 'labels', 'status', 'reminders' => function ($query) {
                $query->where('user_id', auth()->id());
            }]);

            if ($user->hasPermission('read-client-project')) {
                $queries->where('client_id', $user->id);
            } else if ($user->hasPermission('read-my-project')) {
                $queries->whereHas('users', function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                });
            }
        }

        if ($request->has('search_query')) {
            $queries->search($request->search_query, [
                '%name',
            ]);
        }

        if ($request->status_id) {
            $queries->whereHas('status', function ($query) use ($request) {
                $query->where('label_statuses.id', $request->status_id);
            });
        }

        if ($user->hasRole('admin')) {
            $projects = $queries->withCount(['billableTimes' => function ($query) {
                $query->whereHas('user', function ($query) {
                    $query->where('user_id', auth()->id());
                });
            }, 'reminders'])
                ->latest()->paginate($request->per_page ?? 25);
        } else {
            $projects = $queries->withCount(['billableTimes' => function ($query) {
                $query->whereHas('user', function ($query) {
                    $query->where('user_id', auth()->id());
                });
            }], ['reminders' => function ($query) {
                $query->whereHas('user', function ($query) {
                    $query->where('user_id', auth()->id());
                });
            }])->latest()->paginate($request->per_page ?? 25);
        }

        $projectStatusCounts = LabelStatus::where('franchise', 'project')
            ->where('type', 'status')
            ->withCount(['projects' => function ($query) {
                $query->where('projects.deleted_at', null);
            }])
            ->get();

        $data = [
            'projects' => $projects,
            'projectStatusCounts' => $projectStatusCounts
        ];
        return $this->success('Projects Retrieved Successfully', $data);
//        return $this->success('Projects Retrieved Successfully', ProjectCollection::make($projects));
    }

    public
    function create(ProjectStoreRequest $request): \Illuminate\Http\JsonResponse
    {
        DB::beginTransaction();
        try {
            $data = $request->validated();
            $project = Project::create([
                'name' => $request->name,
                'budget' => $request->budget,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'client_id' => $request->client_id,
                'status_id' => $request->status_id
            ]);

            $project->notes()->delete();
            if ($request->has('notes')) {
                foreach ($request->notes as $note) {
                    if ($note) {
                        $project->notes()->create([
                            'user_id' => auth()->id(),
                            'note' => $note
                        ]);
                    }
                }
            }

            if ($request->has('user_ids')) {
                $project->users()->sync($request->user_ids);
            }

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
                        'title' => $reqTask['title'],
                        'description' => $reqTask['description'],
                        'reference' => $reqTask['reference'] ?? null,
                        'author_id' => $reqTask['author_id'] ?? Auth::id(),
                        'assignee_id' => $reqTask['assignee_id'],
                        'start_date' => $reqTask['start_date'],
                        'end_date' => $reqTask['end_date'],
                    ];
                    $taskId = isset($reqTask['id']) ? $reqTask['id'] : null;

                    $task = Task::updateOrCreate([
                        'id' => $taskId,
                        'project_id' => $project->id,
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

            $project = Project::with($this->withProject)->with('notes', 'users')->find($project->id);

            if (!$request->has('id')) {
                if (isset($data['reminders'])) {
                    foreach ($data['reminders'] as $reminder) {
                        $project->reminders()->create($reminder);
                    }
                }
            }
            DB::commit();

            return $this->success('Project Successfully Updated Or Created', $project);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->failure($e->getMessage());
        }
    }

    public
    function show($id)
    {
        $project = Project::with($this->withProject)->with('notes', 'users')->findOrFail($id);

        return response()->json([
            'project' => $project
        ], 200);
    }

    public
    function updateProjectStatus(Request $request)
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

    public
    function update(ProjectUpdateRequest $request, Project $project): \Illuminate\Http\JsonResponse
    {
        DB::beginTransaction();
        try {
            $data = $request->validated();
            $project->update([
                'name' => $request->name,
                'budget' => $request->budget,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'client_id' => $request->client_id,
                'status_id' => $request->status_id
            ]);

            $project->notes()->delete();
            if ($request->has('notes')) {
                foreach ($request->notes as $note) {
                    if ($note) $project->notes()->create(['user_id' => auth()->id(), 'note' => $note]);
                }
            }

            if ($request->has('user_ids')) {
                $project->users()->sync($request->user_ids);
            }

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
                        'title' => $reqTask['title'],
                        'description' => $reqTask['description'],
                        'reference' => $reqTask['reference'] ?? null,
                        'author_id' => $reqTask['author_id'] ?? Auth::id(),
                        'assignee_id' => $reqTask['assignee_id'],
                        'start_date' => $reqTask['start_date'],
                        'end_date' => $reqTask['end_date'],
                    ];
                    $taskId = isset($reqTask['id']) ? $reqTask['id'] : null;

                    $task = Task::updateOrCreate([
                        'id' => $taskId,
                        'project_id' => $project->id,
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

            $project = Project::with($this->withProject)->with('notes', 'users')->find($project->id);

            if (!$request->has('id')) {
                if (isset($data['reminders'])) {
                    foreach ($data['reminders'] as $reminder) {
                        $project->reminders()->create($reminder);
                    }
                }
            }
            DB::commit();

            return $this->success('Project Successfully Updated Or Created', $project);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->failure($e->getMessage());
        }
    }

    public
    function destroy(Project $project): \Illuminate\Http\JsonResponse
    {

        try {
//            $project->projectTasks()->forceDelete();
            $project->billableTimes()->forceDelete();
            $project->taskStatuses()->forceDelete();
            $project->reminders()->forceDelete();
            $project->notes()->forceDelete();
            $project->tasks()->forceDelete();
            $project->forceDelete();
            return $this->success('Project Deleted Successfully');
        } catch (\Throwable $th) {
            return $this->failure($th->getMessage());
        }
    }
}
