<?php

namespace App\Http\Controllers;

use App\Models\ProjectTask;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        $query = ProjectTask::with('developer', 'project');

        if ($user->hasRole('developer'))
            $query->where('developer_id', Auth::user()->id);

        $tasks = $query->latest()->paginate($request->per_page ?? 25);

        return response()->json([
            'tasks' => $tasks
        ], 200);
    }

    public function store(Request $request)
    {
        $validated = $this->validateWith([
            'project_id '   => 'required|exists:projects,id',
            'developer_id ' => 'required|exists:users,id',
            'task '         => 'required|string',
            'start_date'    => 'required',
            'end_date'      => 'required',
        ]);

        $task = ProjectTask::create($validated);

        return response()->json([
            'message' => 'Successfully Added',
            'task'    => $task
        ], 201);
    }

    public function show($id)
    {
        $task = ProjectTask::findOrFail($id);

        return response()->json([
            'task' => $task
        ], 200);
    }

    public function update(Request $request)
    {
        $validated = $this->validateWith([
            'project_id '   => 'required|exists:projects,id',
            'developer_id ' => 'required|exists:users,id',
            'task '         => 'required|string',
            'start_date'    => 'required',
            'end_date'      => 'required',
            'task_id'       => 'required|exists:project_tasks,id',
        ]);

        $task = ProjectTask::findOrFail($request->task_id);
        $task->update($validated);

        return response()->json([
            'message'  => 'Successfully Updated',
            'task' => $task
        ], 201);
    }

    public function destroy($id)
    {
        ProjectTask::destroy($id);

        return response()->json([
            'message' => 'Deleted Successfully',
        ], 200);
    }
}
