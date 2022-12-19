<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectTask;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProjectControler extends Controller
{

    public function index(Request $request)
    {
        $porjects = Project::with('projectTasks')->latest()->paginate($request->per_page ?? 25);

        return response()->json([
            'status'   => 'Success',
            'porjects' => $porjects
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
            'developer_task' => 'required|array',
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()]);
        }

        $data = $validator->validated();
        $data['status'] =  "lead";
        $project = Project::create($data);

        if ($project) {
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
            'status'  => 'Success',
            'project' => $project
        ], 201);
    }

    public function show($id)
    {
        $project = Project::findOrFail($id);

        return response()->json([
            'status'  => 'Success',
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
            'status'     => 'required|in:lead,pending,on_going,accepted,rejected,completed',
            'client_id'  => 'required|exists:users,id',
            'project_id' => 'required|exists:projects,id'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()]);
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
            'status'  => 'Success',
            'project' => $project
        ], 201);
    }

    public function destroy($id)
    {
        $project = Project::findOrFail($id);
        $project->projectTasks()->delete();
        $project->delete();

        return response()->json([
            'status' => 'Deleted Success',
        ], 200);
    }
}
