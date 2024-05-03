<?php

namespace App\Http\Controllers;

use App\Http\Requests\LeaveStatusRequest;
use App\Http\Requests\LeaveStoreRequest;
use App\Http\Requests\LeaveUpdateRequest;
use App\Models\Leave;
use App\Models\User;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class LeaveController extends Controller
{
    use ApiResponseTrait;

    public function __construct()
    {
        $this->middleware(['auth:sanctum'])->except('store');
    }

    public function myLeave(): \Illuminate\Http\JsonResponse
    {
        $user = User::with('children')->findOrFail(auth()->id());
        abort_unless($user->hasPermission('read-my-leave'), 403, 'Permission Denied');

        $leaveData = Leave::with('user')
            ->where('user_id', auth()->id())
            ->orderBy('start_date', 'desc');
        return $this->success('Success', $leaveData->latest()->paginate(25));
    }

    public function index(Request $request): \Illuminate\Http\JsonResponse
    {
        $user = User::with('children')->findOrFail(auth()->id());
        abort_unless($user->hasPermission('read-leave'), 403, 'Permission Denied');

        if ($user->hasRole('admin')) {
            $leaveData = Leave::with('user')
                ->orderBy('start_date', 'desc');
        } else {
            $children_ids = $user->chindren->pluck('id')->toArray();
            $leaveData = Leave::with('user')
                ->whereIn('user_id', $children_ids)
                ->orderBy('start_date', 'desc');
        }
        $data = $leaveData->latest()->paginate($request->per_page ?? 25);

        return $this->success('Success', $data);
    }

    public function store(LeaveStoreRequest $request): \Illuminate\Http\JsonResponse
    {
        $data = $request->validated();
//        $data['status'] = "new";
        $data['user_id'] = $data['user_id'] ?? auth()->id();
        if (isset($data['message'])) $data['status'] = $data['message'];
        unset($data['message']);
        $leaveData = Leave::create($data);

        return $this->success('Successfully Added', $leaveData);
    }

    public function show(Leave $leave): \Illuminate\Http\JsonResponse
    {
        return $this->success('Success', $leave);
    }

    public function update(LeaveUpdateRequest $request, Leave $leave): \Illuminate\Http\JsonResponse
    {
        abort_if(in_array($leave->message, ['accepted', 'rejected']), 403, 'Cannot Update Accepted Or Rejected Leave');
        $data = $request->validated();
        $data['status'] = $data['message'];
        unset($data['message']);
        $leave->update($data);
        return $this->success('Successfully Updated', $leave);
    }

    public function destroy(Leave $leave): \Illuminate\Http\JsonResponse
    {
        if (!auth()->user()->hasRole('admin')) {
            abort_if(in_array($leave->message, ['accepted', 'rejected']), 403, 'Cannot Update Accepted Or Rejected Leave');
        }
        $leave->delete();
        return $this->success('Successfully Deleted', $leave);
    }

    public function leaveStatus(LeaveStatusRequest $request, Leave $leave): \Illuminate\Http\JsonResponse
    {
        $data = $request->validated();
        if ($data['message'] == 'accepted') {
            $data['accepted_by'] = auth()->id();
        }
        $data['last_updated_by'] = auth()->id();
        if (!$data['accepted_start_date']) {
            $data['accepted_start_date'] = $leave->start_date;
        }
        if (!$data['accepted_end_date']) {
            $data['accepted_end_date'] = $leave->end_date;
        }
        $data['status'] = $data['message'];
        unset($data['message']);
        $leave->update($data);

        return $this->success('Successfully Updated', $leave);
    }

    public function getFilter(Request $request)
    {
        $userId = $request->user_id;
        $month = $request->month;
        $year = $request->year;

        // Query the database using Eloquent and filter by user_id, year, and month
        $filteredLeaves = Leave::where('user_id', $userId)
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->get();
        return response()->json($filteredLeaves);
    }
}
