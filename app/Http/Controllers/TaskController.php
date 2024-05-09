<?php

namespace App\Http\Controllers;

use App\Http\Requests\TaskStoreRequest;
use App\Models\LabelStatus;
use App\Models\Project;
use App\Models\Task;
use App\Models\TaskComment;
use App\Traits\ApiResponseTrait;
use App\Traits\ProjectTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use JetBrains\PhpStorm\Deprecated;

class TaskController extends Controller
{
    use ProjectTrait, ApiResponseTrait;

    public function index(Request $request)
    {
        $queries = Task::with($this->taskWith);
        $user = auth()->user();
        if (!$user->hasRole('admin')) {
            $queries->where('assignee_id', $user->id);
        }

        if (request('project_id')) $queries->where('project_id', request('project_id'));
        if (request('author_id')) $queries->where('author_id', request('author_id'));
        if (request('assignee_id')) $queries->where('assignee_id', request('assignee_id'));

        $tasks = $queries->latest()->paginate($request->per_page ?? 25);

        return response()->json([
            'tasks' => $tasks
        ], 200);
    }

    public function store(TaskStoreRequest $request): \Illuminate\Http\JsonResponse
    {
        DB::beginTransaction();
        try {
            $taskData = $request->validated();
            unset($taskData['id'], $taskData['status'], $taskData['labels'], $taskData['comment']);
            $taskData['author_id'] = Auth::id();
            $task = Task::updateOrCreate(['id' => $request->id ?? null], $taskData);

//            if ($request->has('status')) {
//                $this->setTaskStatus($task, $request->status);
//            } else {
//                $this->setTaskStatus($task, $task->status->title ?? null);
//            }

            if ($request->has('labels')) {
                $labelsArray = gettype($request->labels) == 'array' ? $request->labels : [$request->labels];
                foreach ($labelsArray as $reqLabel) {
                    $this->setTaskLabel($task, $reqLabel);
                }
            }

            if ($request->has('comment')) {
                $task->comments()->create([
                    'user_id' => auth()->id(),
                    'comment' => $request->comment,
                ]);
            }
            $task->load($this->taskWith);

            DB::commit();
            return response()->json([
                'task' => $task
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage());
            return $this->failure($e->getMessage() ?? 'Something Something', 500);
        }
    }

    public function show($id)
    {
        $task = Task::with($this->taskWith)->findOrFail($id);

        return response()->json([
            'task' => $task
        ], 200);
    }

    public function reorderTask(Request $request)
    {
        $task = Task::findOrFail($request->id);

        if ($request->has('status')) {
            // DB::table('statusables')
            //     ->where('list_order', '>', $task->status->pivot->list_order)
            //     ->where('statusable_type', Task::class)
            //     ->where('statusable_id', '!=', $task->id)
            //     ->where('label_status_id', $task->status->id)
            //     ->update(
            //         ['list_order' => DB::raw('list_order - 1')]
            //     );

            $task = $this->setTaskStatus($task, $request->status);
        }

        if ($request->has('column_list_order') && !$request->has('task_new_list_order')) {
            $newColumnOrder = $request->column_list_order;
            $oldColumnOrder = $task->status->list_order;

            if ($oldColumnOrder != $newColumnOrder) {
                if ($oldColumnOrder < $newColumnOrder) {
                    LabelStatus::where('list_order', '<=', $newColumnOrder)
                        ->where('list_order', '>', $oldColumnOrder)
                        ->where('project_id', $task->status->project_id)
                        ->update(
                            ['list_order' => DB::raw('list_order - 1')]
                        );
                } else {
                    LabelStatus::where('list_order', '>=', $newColumnOrder)
                        ->where('list_order', '<', $oldColumnOrder)
                        ->where('project_id', $task->status->project_id)
                        ->update(
                            ['list_order' => DB::raw('list_order + 1')]
                        );
                }
                $task->status->list_order = $newColumnOrder;
                $task->status->save();
                $task->status->refresh();
            }
        }

        if ($request->has('task_new_list_order')) {
            $newOrder = $request->task_new_list_order;
            $oldOrder = $task->status->pivot->list_order;

            if ($oldOrder != $newOrder) {
                if ($oldOrder < $newOrder) {
                    DB::table('statusables')
                        ->where('list_order', '<=', $newOrder)
                        ->where('list_order', '>', $oldOrder)
                        ->where('statusable_type', Task::class)
                        ->where('statusable_id', '!=', $task->id)
                        ->where('label_status_id', $task->status->id)
                        ->update(
                            ['list_order' => DB::raw('list_order - 1')]
                        );
                } else {
                    DB::table('statusables')
                        ->where('list_order', '>=', $newOrder)
                        ->where('list_order', '<', $oldOrder)
                        ->where('statusable_type', Task::class)
                        ->where('statusable_id', '!=', $task->id)
                        ->where('label_status_id', $task->status->id)
                        ->update(
                            ['list_order' => DB::raw('list_order + 1')]
                        );
                }
            } else {
                DB::table('statusables')
                    ->where('list_order', '>=', $newOrder)
                    ->where('statusable_type', Task::class)
                    ->where('statusable_id', '!=', $task->id)
                    ->where('label_status_id', $task->status->id)
                    ->update(
                        ['list_order' => DB::raw('list_order + 1')]
                    );
            }
            $task->status()->updateExistingPivot($task->status->id, ['list_order' => $newOrder]);
        }

        return response()->json([
            'task' => $task->refresh()
        ], 200);
    }

    public function update(Request $request)
    {
        return 'Deprecated';
        $validated = $this->validateWith([
            'id' => 'required|exists:tasks,id',
            'title' => 'required|string',
            'description' => 'required|string',
            'reference' => 'sometimes|required|string',
            'project_id' => 'required|exists:projects,id',
            'assignee_id' => 'sometimes|required|exists:users,id',
            'start_date' => 'required|date_format:Y-m-d H:i:s',
            'end_date' => 'required|date_format:Y-m-d H:i:s',
        ]);

        try {
            Task::where('id', $validated['id'])->update($validated);

            $task = Task::with($this->taskWith)->find($validated['id']);

            if ($request->has('status')) {
                $this->setTaskStatus($task, $request->status);
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
                'message' => 'Successfully Updated',
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

    public function destroyByStatus($id)
    {
        try {
            $data = Task
                ::whereHas('status', function ($q) use ($id) {
                    $q->where('label_status_id', $id);
                })
                ->delete();
            return response()->json([
                '$data' => $data,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                '$th' => $th->getMessage(),
            ], 200);
        }
    }

    public function updateTaskStatus(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'task_id' => 'required|exists:tasks,id',
            'status' => 'required|string|in:New,In Progress,On Hold,Completed,Testing,Issue,Canceled',
        ]);

        $task = Task::findOrFail($request->task_id);

        $task->update(['status' => $request->status]);

        return $this->success('Task status updated successfully', $task);
    }

    public function updateTaskPriority(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'task_id' => 'required|exists:tasks,id',
            'priority' => 'required|string|in:Low,Medium,High,Urgent',
        ]);

        $task = Task::findOrFail($request->task_id);

        $task->update(['priority' => $request->priority]);

        return $this->success('Task priority updated successfully', $task);
    }

    public function getTaskComments(Task $task): \Illuminate\Http\JsonResponse
    {
        return $this->success('Task comments retrieved successfully', $task->comments);
    }

}
