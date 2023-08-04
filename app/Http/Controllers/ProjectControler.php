<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectStatus;
use App\Models\ProjectTask;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ProjectControler extends Controller
{

    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Project::with(['clients', 'projectTasks' => function ($data) {
            $data->with('developer');
        }, 'status']);
        if ($user->hasRole('developer')) {
            $query->whereHas('projectTasks', function ($q) use ($user) {
                $q->where('developer_id', $user->id);
            });
        } elseif ($user->hasRole('client')) {
            $query->where('client', $user->id);
        }
        $projects = $query->latest()->paginate($request->per_page ?? 25);

        return response()->json([
            'message'   => 'Success',
            'porjects' => $projects
        ], 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'           => 'required|string|unique:projects',
            'budget'         => 'required',
            'start_date'     => 'required|date',
            'end_date'       => 'required|date',
            'client_id'      => 'required|exists:users,id',
            'developer_task' => 'sometimes|required|array',
            'status_id' => 'required|exists:project_statuses,id'
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 500);
        }

        $data = $validator->validated();
        $project = Project::create($data);

        if ($project && $request->has("developer_task")) {
            foreach ($request->developer_task as $developerTask) {
                $projectTask               = new ProjectTask();
                $projectTask->task         = $developerTask['task'];
                $projectTask->project_id   = $project->id;
                $projectTask->developer_id = $developerTask['developer_id'];
                $projectTask->start_date   = $developerTask['start_date'];
                $projectTask->end_date     = $developerTask['end_date'];
                $projectTask->save();
            }
        }

        return response()->json([
            'message'  => 'Success Added',
            'project' => $project
        ], 201);
    }

    public function show($id)
    {
        $project = Project::findOrFail($id);

        return response()->json([
            'message'  => 'Success',
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
