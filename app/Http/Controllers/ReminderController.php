<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReminderStoreRequest;
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
        $reminder = Reminder::query();
        $reminder->with('users', 'project');
        if (request('start_date')) {
            $reminder->whereDate('remind_at', '>=', request('start_date'));
        }
        if (request('end_date')) {
            $reminder->whereDate('remind_at', '<=', request('end_date'));
        }
        if (request()->has('project_id')) {
            $reminder->where('project_id', $request->project_id);
        }
//        if (!request('start_date') && !request('end_date')) {
//            $reminder->whereDate('remind_at', '=', Carbon::now()->format('Y-m-d'));
//        }
        $reminder = $reminder->where('user_id', auth()->id())
            ->latest()
            ->get();
        return $this->success('Success', ReminderResource::collection($reminder));
    }


    public function store(ReminderStoreRequest $request): \Illuminate\Http\JsonResponse
    {
        if ($request->remind_at && Carbon::parse($request->remind_at)->lessThanOrEqualTo(Carbon::now())) {
            return $this->failure('Remind at date should be greater than current date time');
        }

        $data = $request->validated();
        $data['user_id'] = $data['user_id'] ?? auth()->id();
//        $data['remind_at'] = Carbon::parse($data['remind_at']);
        $reminder = Reminder::create($data);
        return $this->success('Successfully Created', new ReminderResource($reminder));
    }

    public function show(Reminder $reminder): \Illuminate\Http\JsonResponse
    {
        return $this->success('Success', new ReminderResource($reminder->load('users', 'projects')));
    }

    public function update(ReminderStoreRequest $request, Reminder $reminder): \Illuminate\Http\JsonResponse
    {
        if ($request->remind_at && Carbon::parse($request->remind_at)->lessThanOrEqualTo(Carbon::now())) {
            return $this->failure('Remind at date should be greater than current date time');
        }
        $data = $request->validated();
        $data['remind_at'] = Carbon::parse($data['remind_at']);
        $reminder->update($data);
        return $this->success('Successfully Updated', new ReminderResource($reminder));
    }

    public function toggleStatus(Request $request, Reminder $reminder): \Illuminate\Http\JsonResponse
    {
        $reminder->update(['status' => !$reminder->status]);
        return $this->success('Successfully Updated', new ReminderResource($reminder));
    }

    public function destroy(Reminder $reminder): \Illuminate\Http\JsonResponse
    {
        $reminder->delete();
        return $this->success('Successfully Deleted');
    }
}
