<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProjectNoteStoreRequest;
use App\Http\Requests\UserNoteUpdateRequest;
use App\Http\Resources\ProjectNoteResource;
use App\Models\ProjectNote;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProjectNoteController extends Controller
{
    use ApiResponseTrait;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(): JsonResponse
    {
        $notes = ProjectNote::where('user_id', auth()->id())->get();
        return $this->success('Success', ProjectNoteResource::collection($notes));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param ProjectNoteStoreRequest $request
     * @return JsonResponse
     */
    public function store(ProjectNoteStoreRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['user_id'] = $data['user_id'] ?? auth()->id();
        $note = ProjectNote::create($data);
        return $this->success('Note created successfully', new ProjectNoteResource($note));
    }

    /**
     * Display the specified resource.
     *
     * @param ProjectNote $projectNote
     * @return JsonResponse
     */
    public function show(ProjectNote $projectNote): JsonResponse
    {
        return $this->success('Success', new ProjectNoteResource($projectNote));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UserNoteUpdateRequest $request
     * @param ProjectNote $projectNote
     * @return JsonResponse
     */
    public function update(UserNoteUpdateRequest $request, ProjectNote $projectNote): JsonResponse
    {
        $projectNote = $projectNote->update($request->validated());
        return $this->success('Note updated successfully', new ProjectNoteResource($projectNote));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param ProjectNote $projectNote
     * @return JsonResponse
     */
    public function destroy(ProjectNote $projectNote): JsonResponse
    {
        $projectNote->delete();
        return $this->success('Note deleted successfully', new ProjectNoteResource($projectNote));
    }

    public function trash(): JsonResponse
    {
        $notes = ProjectNote::onlyTrashed()->where('user_id', auth()->id())->get();
        return $this->success('Success', ProjectNoteResource::collection($notes));
    }

    public function restore(ProjectNote $projectNote): JsonResponse
    {
        $projectNote->restore();
        return $this->success('Note restored successfully', new ProjectNoteResource($projectNote));
    }

    public function forceDelete(ProjectNote $projectNote): JsonResponse
    {
        $projectNote->forceDelete();
        return $this->success('Note permanently deleted successfully', new ProjectNoteResource($projectNote));
    }


}
