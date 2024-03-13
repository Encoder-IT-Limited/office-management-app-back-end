<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProjectNoteRequest;
use App\Http\Requests\UpdateUserNoteRequest;
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
    public function index()
    {
        $notes = ProjectNote::where('user_id', auth()->id())->get();
        return $this->success('Success', ProjectNoteResource::collection($notes));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreProjectNoteRequest $request
     * @return JsonResponse
     */
    public function store(StoreProjectNoteRequest $request)
    {
        $data = $request->validated();
        $data['user_id'] = auth()->id();
        $note = ProjectNote::create($data);
        return $this->success('Note created successfully', new ProjectNoteResource($note));
    }

    /**
     * Display the specified resource.
     *
     * @param ProjectNote $projectNote
     * @return JsonResponse
     */
    public function show(ProjectNote $projectNote)
    {
        return $this->success('Success', new ProjectNoteResource($projectNote));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param ProjectNote $projectNote
     * @return JsonResponse
     */
    public function update(UpdateUserNoteRequest $request, ProjectNote $projectNote)
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
    public function destroy(ProjectNote $projectNote)
    {
        $projectNote->delete();
        return $this->success('Note deleted successfully');
    }

    public function trash()
    {
        $notes = ProjectNote::onlyTrashed()->where('user_id', auth()->id())->get();
        return $this->success('Success', ProjectNoteResource::collection($notes));
    }

    public function restore(ProjectNote $projectNote)
    {
        $projectNote->restore();
        return $this->success('Note restored successfully', new ProjectNoteResource($projectNote));
    }

    public function forceDelete(ProjectNote $projectNote)
    {
        $projectNote->forceDelete();
        return $this->success('Note permanently deleted successfully');
    }


}
