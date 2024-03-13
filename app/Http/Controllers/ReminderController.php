<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReminderStoreRequest;
use App\Http\Requests\ReminderUpdateRequest;
use App\Http\Resources\ReminderResource;
use App\Models\Reminder;
use App\Traits\ApiResponseTrait;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ReminderController extends Controller
{
    use ApiResponseTrait;

    public function index(Request $request): \Illuminate\Http\JsonResponse
    {
        $reminder = Reminder::with('users', 'projects')
            ->where('id', auth()->id())
            ->get();
        return $this->success('Success', ReminderResource::collection($reminder));
    }

    public function store(ReminderStoreRequest $request): \Illuminate\Http\JsonResponse
    {
        $data = $request->validated();
        $data['user_id'] = $data['user_id'] ?? auth()->id();
        $reminder = Reminder::create($data);
        return $this->success('Successfully Created', new ReminderResource($reminder));
    }

    public function show(Reminder $reminder): \Illuminate\Http\JsonResponse
    {
        return $this->success('Success', new ReminderResource($reminder));
    }

    public function update(ReminderUpdateRequest $request, Reminder $reminder): \Illuminate\Http\JsonResponse
    {
        $data = $request->validated();
        $reminder->update($data);
        return $this->success('Successfully Updated', new ReminderResource($reminder));
    }

    public function destroy(Reminder $reminder): \Illuminate\Http\JsonResponse
    {
        $reminder->delete();
        return $this->success('Successfully Deleted');
    }
}
