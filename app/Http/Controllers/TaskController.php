<?php

namespace App\Http\Controllers;

use App\Models\LabelStatus;
use App\Models\Task;
use App\Traits\ProjectTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use JetBrains\PhpStorm\Deprecated;

class TaskController extends Controller
{
    use ProjectTrait;

    public function index(Request $request)
    {
        $queries = Task::with($this->taskWith);

        $queries->when($request->has('project_id'), function ($projectQ) use ($request) {
            return $projectQ->where('project_id', $request->project_id);
        });

        $queries->when($request->has('author_id'), function ($authorQ) use ($request) {
            return $authorQ->where('author_id', $request->author_id);
        });

        $queries->when($request->has('assignee_id'), function ($assigneeQ) use ($request) {
            return $assigneeQ->where('assignee_id', $request->assignee_id);
        });

        $tasks = $queries->latest()->paginate($request->per_page ?? 25);

        return response()->json([
            'tasks' => $tasks
        ], 200);
    }

    public function store(Request $request)
    {
        $this->validateWith([
            'id'          => 'sometimes|required|exists:tasks,id',

            'title'       => 'required|string',
            'description' => 'required|string',
            'reference'   => 'sometimes|required|string',
            'project_id'  => 'required|exists:projects,id',
            'assignee_id' => 'sometimes|required|exists:users,id',
            'start_date'  => 'required|date_format:Y-m-d H:i:s',
            'end_date'    => 'required|date_format:Y-m-d H:i:s',

        ]);

        $taskData = $request->except('id');
        $taskData['author_id'] = Auth::id();
        $task = Task::updateOrCreate(['id' => $request->id ?? null], $taskData);

        $this->setTaskStatus($task, $task->status->title ?? null);

        if ($request->has('labels')) {
            $labelsArray = gettype($request->labels) == 'array' ? $request->labels : [$request->labels];
            foreach ($labelsArray as $reqLabel) {
                $this->setTaskLabel($task, $reqLabel);
            }
        }

        $task = Task::with($this->taskWith)->find($request->id ?? $task->id);

        return response()->json([
            'message' => 'Successfully Added',
            'task'    => $task
        ], 201);
    }

    public function show($id)
    {
        $task = Task::with($this->taskWith)->findOrFail($id);

        return response()->json([
            'task' => $task
        ], 200);
    }

    public function update(Request $request)
    {
        return 'Deprecated';
        $validated = $this->validateWith([
            'id'          => 'required|exists:tasks,id',
            'title'       => 'required|string',
            'description' => 'required|string',
            'reference'   => 'sometimes|required|string',
            'project_id'  => 'required|exists:projects,id',
            'assignee_id' => 'sometimes|required|exists:users,id',
            'start_date'  => 'required|date_format:Y-m-d H:i:s',
            'end_date'    => 'required|date_format:Y-m-d H:i:s',
        ]);

        try {
            Task::where('id', $validated['id'])->update($validated);

            $task = Task::with($this->taskWith)->find($validated['id']);

            if ($request->has('status')) {
                $this->setTaskStatus($task, $request->status);

                //                 $reqStatus = $request->status;
                //
                //                 $default = LabelStatus::getTaskDefaultStatus();
                //
                //                 $status = LabelStatus::updateOrCreate([
                //                     'project_id' => $task->project_id,
                //                     'title' => $reqStatus,
                //                 ], [
                //                     'color' => $default->color,
                //                     'franchise' => 'task',
                //                     'type' => 'status',
                //                 ]);
                //
                //                 $task->status()->sync([$status->id => [
                //                     'color' => $status->color,
                //                 ]]);
            }

            if ($request->has('labels')) {
                $labelsArray = gettype($request->labels) == 'array' ? $request->labels : [$request->labels];
                foreach ($labelsArray as $reqLabel) {
                    $default = LabelStatus::getTaskDefaultStatus();

                    $label = LabelStatus::updateOrCreate([
                        'project_id' => $task->project_id,
                        'title' => $reqLabel,
                    ], [
                        'color' => $default->color,
                        'franchise' => 'task',
                        'type' => 'label',
                    ]);

                    $label = LabelStatus::taskOnly()->labelOnly()->byProject($task->project_id)->byTitle($reqLabel)->first();

                    $task->labels()->syncWithoutDetaching([$label->id => [
                        'color' => $label->color,
                    ]]);
                }
            }
            $task = Task::with($this->taskWith)->findOrFail($task->id);
            return response()->json([
                'message'  => 'Successfully Updated',
                'task' => $task->refresh()
            ], 200);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function destroy($id)
    {
        Task::destroy($id);

        return response()->json([
            'message' => 'Deleted Successfully',
        ], 200);
    }
}
