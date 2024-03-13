<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserNoteRequest;
use App\Http\Requests\UpdateUserNoteRequest;
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
    public function index()
    {
        $notes = UserNote::where('user_id', auth()->id())->get();
        return $this->success('Success', UserNoteResource::collection($notes));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreUserNoteRequest $request
     * @return JsonResponse
     */
    public function store(StoreUserNoteRequest $request)
    {
        $data = $request->validated();
        $data['user_id'] = auth()->id();
        $note = UserNote::create($data);
        return $this->success('Note created successfully', new UserNoteResource($note));
    }

    /**
     * Display the specified resource.
     *
     * @param UserNote $userNote
     * @return JsonResponse
     */
    public function show(UserNote $userNote)
    {
        return $this->success('Success', new UserNoteResource($userNote));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateUserNoteRequest $request
     * @param UserNote $userNote
     * @return JsonResponse
     */
    public function update(UpdateUserNoteRequest $request, UserNote $userNote)
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
    public function destroy(UserNote $userNote)
    {
        $userNote->delete();
        return $this->success('Note deleted successfully');
    }

    public function trash()
    {
        $notes = UserNote::onlyTrashed()->where('user_id', auth()->id())->get();
        return $this->success('Success', UserNoteResource::collection($notes));
    }

    public function restore(UserNote $userNote)
    {
        $userNote->restore();
        return $this->success('Note restored successfully');
    }

    public function forceDelete(UserNote $userNote)
    {
        $userNote->forceDelete();
        return $this->success('Note permanently deleted');
    }
}
