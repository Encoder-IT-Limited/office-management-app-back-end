<?php

namespace App\Http\Controllers;

use App\Models\Leave;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class LeaveController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:sanctum'])->except('store');
    }
    public function index(Request $request)
    {
        $user = User::findOrFail(Auth::id());

        $leaveData = Leave::with('user');
        if ($user->hasrole(['manager', 'developer'])) {
            $leaveData->where('user_id', $user->id);
        }
        $query = $leaveData->latest()->paginate($request->per_page ?? 25);

        return response()->json([
            'message'     => 'Success',
            'leave_data' => $query
        ], 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id'     => 'exists:users,id',
            'title'       => 'required|string',
            'description' => 'required',
            'start_date'  => 'required|date',
            'end_date'    => 'required|date',
            'reason'     => 'required|string',
            'accepted_start_date' => 'nullable|date',
            'accepted_end_date' => 'nullable|date',
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 500);
        }

        $data           = $validator->validated();
        $data['status'] = "new";
        $data['user_id'] = Auth::user()->id;
        $leaveData      = Leave::create($data);
        // dd($leaveData);
        return response()->json([
            'message'   => 'Successfully Added',
            'leave_data'   => $leaveData
        ], 201);
    }

    public function show($id)
    {
        $leaveData = Leave::find($id);

        if (!$leaveData)
            return response()->json(['message' => 'Leave data Not Found'], 404);

        return response()->json([
            'message'     => 'Success',
            'leave_data' => $leaveData
        ], 200);
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title'       => 'required|string',
            'description' => 'required',
            'start_date'  => 'required|date',
            'end_date'    => 'required|date',
            'leave_id'    => 'required|exists:leaves,id'
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 500);
        }

        $leaveData              = Leave::findOrFail($request->leave_id);
        $leaveData->title       = $request->title;
        $leaveData->description = $request->description;
        $leaveData->start_date  = $request->start_date;
        $leaveData->end_date    = $request->end_date;
        $leaveData->save();

        return response()->json([
            'message'    => 'Successfully Updated',
            'leaveData' => $leaveData
        ], 201);
    }

    public function destroy($id)
    {
        Leave::destroy($id);

        return response()->json([
            'message' => 'Deleted Successfully',
        ], 200);
    }

    public function leaveStatus(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id'            => 'exists:leaves,id',
            'reason'              => 'sometimes|required|string',
            'accepted_start_date' => 'sometimes|required|date',
            'accepted_end_date'   => 'sometimes|required|date'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 500);
        }

        $leave = Leave::findOrFail($request->leave_id);
        $leave->status = $request->status;
        $leave->update($validator->validated());

        return response()->json([
            'message'   => 'Successfully Added',
            'leave'   => $leave
        ], 201);
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
