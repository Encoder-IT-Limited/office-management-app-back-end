<?php

namespace App\Http\Controllers;

use App\Models\LeaveManagement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LeaveManageController extends Controller
{
    public function index(Request $request)
    {
        $leaveData = LeaveManagement::latest()->paginate($request->per_page ?? 25);

        return response()->json([
            'status' => 'Success',
            'leave_data'   => $leaveData
        ], 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title'               => 'required',
            'description'         => 'required',
            'start_date'          => 'required|date',
            'end_date'            => 'required|date',
            // 'user_id'             => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()]);
        }

        $leaveData = LeaveManagement::create([
            'title' => $request->title,
            'description'  => $request->description,
            'start_date'  => $request->start_date,
            'end_date'  => $request->end_date,
            'status'  => "New",
            'user_id'  => $request->user_id,
        ]);

        return response()->json([
            'status' => 'Success',
            'leave_data'   => $leaveData
        ], 201);
    }

    public function show($id)
    {
        $leaveData = LeaveManagement::find($id);

        if (!$leaveData)
            return response()->json(['status' => 'Leave data Not Found'], 404);

        return response()->json([
            'status' => 'Success',
            'leave_data'   => $leaveData
        ], 200);
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title'       => 'required',
            'description' => 'required',
            'start_date'  => 'required|date',
            'end_date'    => 'required|date',
            'leave_id'    => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()]);
        }

        $leaveData = LeaveManagement::find($request->leave_id);

        if (!$leaveData)
            return response()->json(['status' => 'Leave data Not Found'], 404);

        $leaveData->title  = $request->title;
        $leaveData->description = $request->description;
        $leaveData->start_date = $request->start_date;
        $leaveData->end_date = $request->end_date;
        $leaveData->save();

        return response()->json([
            'status' => 'Success',
            'leaveData'   => $leaveData
        ], 201);
    }

    public function destroy($id)
    {
        $leaveData = LeaveManagement::find($id);

        if (!$leaveData)
            return response()->json(['status' => 'Leave data Not Found'], 404);

        $leaveData->delete();

        return response()->json([
            'status' => 'Deleted Success',
        ], 200);
    }
}
