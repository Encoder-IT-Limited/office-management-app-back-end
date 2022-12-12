<?php

namespace App\Http\Controllers;

use App\Models\Leave;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LeaveController extends Controller
{
    public function index(Request $request)
    {
        $leaveData = Leave::latest()->paginate($request->per_page ?? 25);

        return response()->json([
            'status'     => 'Success',
            'leave_data' => $leaveData
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
            return response()->json(['error' => $validator->errors()]);
        }

        $data           = $validator->validated();
        $data['status'] = "new";
        $leaveData      = Leave::create($data);

        return response()->json([
            'status' => 'Success',
            'leave_data'   => $leaveData
        ], 201);
    }

    public function show($id)
    {
        $leaveData = Leave::find($id);

        if (!$leaveData)
            return response()->json(['status' => 'Leave data Not Found'], 404);

        return response()->json([
            'status'     => 'Success',
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
            return response()->json(['error' => $validator->errors()]);
        }

        $leaveData              = Leave::findOrFail($request->leave_id);
        $leaveData->title       = $request->title;
        $leaveData->description = $request->description;
        $leaveData->start_date  = $request->start_date;
        $leaveData->end_date    = $request->end_date;
        $leaveData->save();

        return response()->json([
            'status'    => 'Success',
            'leaveData' => $leaveData
        ], 201);
    }

    public function destroy($id)
    {
        $leaveData = Leave::destroy($id);

        return response()->json([
            'status' => 'Deleted Success',
        ], 200);
    }
}
