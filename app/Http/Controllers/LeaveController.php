<?php

namespace App\Http\Controllers;

use App\Models\Leave;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class LeaveController extends Controller
{
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
            'title'       => 'required|string',
            'description' => 'required',
            'start_date'  => 'required|date',
            'end_date'    => 'required|date',
            'user_id'     => 'required|exists:users,id',
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 500);
        }

        $data           = $validator->validated();
        $data['message'] = "new";
        $leaveData      = Leave::create($data);

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
            'leave_id'            => 'required|exists:leaves,id',
            'message'              => 'required|in:accepted,rejected',
            'reason'              => 'sometimes|required|string',
            'accepted_start_date' => 'sometimes|required|date',
            'accepted_end_date'   => 'sometimes|required|date'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 500);
        }

        $leave = Leave::findOrFail($request->leave_id);
        $leave->update($validator->validated());

        return response()->json([
            'message'   => 'Successfully Added',
            'leave'   => $leave
        ], 201);
    }
}
