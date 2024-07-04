<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserNoteStoreRequest;
use App\Http\Requests\UserNoteUpdateRequest;
use App\Http\Resources\UserNoteResource;
use App\Models\UserNote;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;

class UserNoteController extends Controller
{
    use ApiResponseTrait;

    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $notes = UserNote::where('user_id', auth()->id())->get();
        return $this->success('Success', UserNoteResource::collection($notes));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param UserNoteStoreRequest $request
     * @return JsonResponse
     */
    public function store(UserNoteStoreRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['user_id'] = (int)($data['user_id'] ?? auth()->id());
        $note = UserNote::create($data);
        return $this->success('Note created successfully', new UserNoteResource($note));
    }

    /**
     * Display the specified resource.
     *
     * @param UserNote $userNote
     * @return JsonResponse
     */
    public function show(UserNote $userNote): JsonResponse
    {
        return $this->success('Success', new UserNoteResource($userNote));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UserNoteUpdateRequest $request
     * @param UserNote $userNote
     * @return JsonResponse
     */
    public function update(UserNoteUpdateRequest $request, UserNote $userNote): JsonResponse
    {
        $userNote->update($request->validated());
        return $this->success('Note updated successfully', new UserNoteResource($userNote));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param UserNote $userNote
     * @return JsonResponse
     */
    public function destroy(UserNote $userNote): JsonResponse
    {
        $userNote->delete();
        return $this->success('Note deleted successfully', new UserNoteResource($userNote));
    }

    public function trash(): JsonResponse
    {
        $notes = UserNote::onlyTrashed()->where('user_id', auth()->id())->get();
        return $this->success('Success', UserNoteResource::collection($notes));
    }

    public function restore(UserNote $userNote): JsonResponse
    {
        $userNote->restore();
        return $this->success('Note restored successfully', new UserNoteResource($userNote));
    }

    public function forceDelete(UserNote $userNote): JsonResponse
    {
        $userNote->forceDelete();
        return $this->success('Note permanently deleted', new UserNoteResource($userNote));
    }
}
