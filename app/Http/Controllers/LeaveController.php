<?php

namespace App\Http\Controllers;

use App\Http\Requests\LeaveRequest;
use App\Http\Requests\LeaveStatusRequest;
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
        $leaveData = [];

        if ($user->hasPermission('read-leave')) {
            $leaveData[] = Leave::with('user')
                ->orderBy('start_date', 'desc');
        }

        if ($user->hasrole(['manager', 'developer'])) {
            $children_ids = $user->chindren->pluck('id')->toArray();
            $children_ids[] = $user->id;
            $leaveData->whereIn('user_id', $children_ids);
        }

        $query = $leaveData->latest()->paginate($request->per_page ?? 25);

        return response()->json([
            'message' => 'Success',
            'leave_data' => $query
        ], 200);
    }

    public function store(LeaveRequest $request): \Illuminate\Http\JsonResponse
    {
        $data = $request->validated();
//        $data['status'] = "new";
        $data['user_id'] = $data['user_id'] ?? auth()->id();
        $leaveData = Leave::create($data);

        return $this->success('Successfully Added', $leaveData);
    }

    public function show(Leave $leave): \Illuminate\Http\JsonResponse
    {
        return $this->success('Success', $leave);
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string',
            'description' => 'required',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'leave_id' => 'required|exists:leaves,id'
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 500);
        }

        $leaveData = Leave::findOrFail($request->leave_id);
        $leaveData->title = $request->title;
        $leaveData->description = $request->description;
        $leaveData->start_date = $request->start_date;
        $leaveData->end_date = $request->end_date;
        $leaveData->save();

        return response()->json([
            'message' => 'Successfully Updated',
            'leaveData' => $leaveData
        ], 201);
    }

    public function destroy(Leave $leave): \Illuminate\Http\JsonResponse
    {
        $leave->delete();
        return $this->success('Successfully Deleted', $leave);
    }

    public function leaveStatus(LeaveStatusRequest $request): \Illuminate\Http\JsonResponse
    {
        $data = $request->validated();
        $leave = Leave::findOrFail($data['leave_id']);
        $leave->update($request->validated());

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
