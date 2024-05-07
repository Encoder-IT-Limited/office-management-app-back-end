<?php

namespace App\Http\Controllers;

use App\Models\TaskComment;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;

class TaskCommentController extends Controller
{
    use ApiResponseTrait;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'task_id' => 'required|exists:tasks,id',
            'comment' => 'required|string',
        ]);

        $taskComment = TaskComment::create([
            'task_id' => $request->task_id,
            'user_id' => auth()->id(),
            'comment' => $request->comment,
        ]);

        return $this->success('Comment added successfully', $taskComment);
    }

    /**
     * Display the specified resource.
     *
     * @param TaskComment $taskComment
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(TaskComment $taskComment): \Illuminate\Http\JsonResponse
    {
        return $this->success('Task comment retrieved successfully', $taskComment);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param TaskComment $taskComment
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, TaskComment $taskComment)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param TaskComment $taskComment
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(TaskComment $taskComment): \Illuminate\Http\JsonResponse
    {
        $taskComment->delete();

        return $this->success('Task comment deleted successfully');
    }
}
