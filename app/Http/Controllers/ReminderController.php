<?php

namespace App\Http\Controllers;

use App\Models\Reminder;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ReminderController extends Controller
{
    public function index(Request $request)
    {
        $reminder = Reminder::with('users', 'clients', 'projects')->where('id', Auth::user()->id)->get();

        return response()->json([
            'status'   => 'Success',
            'reminders' => $reminder
        ], 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'client_id'   => 'required|exists:users,id',
            'project_id'  => 'sometimes|required|exists:projects,id',
            'date'        => 'required',
            'time'        => 'required',
            'reminder_at' => 'required',
            'description' => 'required|string',
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 500);
        }

        $data = $validator->validated();
        $data['user_id'] =  Auth::user()->id;
        $reminder = Reminder::create($data);

        return response()->json([
            'status'  => 'Success',
            'reminder' => $reminder
        ], 201);
    }

    public function show($id)
    {
        $reminder = Reminder::findOrFail($id);

        return response()->json([
            'status'  => 'Success',
            'reminder' => $reminder
        ], 200);
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'client_id'   => 'required|exists:users,id',
            'project_id'  => 'sometimes|required|exists:projects,id',
            'date'        => 'required',
            'time'        => 'required',
            'reminder_at' => 'required',
            'description' => 'required|string',
            'reminder_id' => 'required|exists:reminders,id',
            'user_id'     => 'required|exists:users,id'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 500);
        }

        $reminder = Reminder::findOrFail($request->reminder_id);
        $reminder->update($validator->validated());

        return response()->json([
            'status'  => 'Success',
            'reminder' => $reminder
        ], 201);
    }

    public function destroy($id)
    {
        Reminder::destroy($id);

        return response()->json([
            'status' => 'Deleted Success',
        ], 200);
    }
}
