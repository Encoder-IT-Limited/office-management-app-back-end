<?php

namespace App\Http\Controllers;

use App\Models\Attendace;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AttendanceController extends Controller
{
    public function checkIn(Request $request)
    {
        $carbon = Carbon::now();
        $check_in = $carbon->subMinutes(5)->toDateTimeString();

        $user_check = Attendace::where('employee_id', Auth::id())->whereDate('check_in', $carbon)->first();
        if ($user_check) {
            return response()->json([
                'status'  => 'Success',
                'message' => "You are already checked-in"
            ], 201);
        }
        $attendance = Attendace::create([
            'employee_id' => Auth::id(),
            'check_in' => $check_in,
        ]);

        return response()->json([
            'status'  => 'Success',
            'message' => "You are successfully checked-in"
        ], 201);
    }

    public function checkOut(Request $request)
    {
        $carbon = Carbon::now();
        // $carbon_out = $carbon->toDateTimeString();

        $user_check = Attendace::whereDate('check_in', $carbon)->where('employee_id', Auth::id());
        $check_in = $user_check->first();
        if (!$check_in) {
            return response()->json([
                'status'  => 'Success',
                'message' => "You are not checked-in"
            ], 201);
        }

        $check_out = $user_check->whereNull('check_out')->first();
        if ($check_out) {
            $attendance = Attendace::where('employee_id', Auth::id());
            $data = $attendance->update([
                'check_out' => $carbon
            ]);

            return response()->json([
                'status'  => 'Success',
                'message' => "You are successfully checked-out"
            ], 201);
        }
        return response()->json([
            'status'  => 'Success',
            'message' => "You are already checked-out"
        ], 201);
    }

    public function employeeAttendance(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'employee_id' => 'sometimes|required|exists:users,id',
            'month'       => 'sometimes|required',
            'year'        => 'sometimes|required'
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()]);
        }

        $user = User::findOrFail(Auth::id());
        $query = Attendace::with('employee');

        if ($request->has('employee_id'))
            $query->where('employee_id', $request->employee_id);
        if ($request->has('month'))
            $query->whereMonth('check_in', '=', $request->month);
        if ($request->has('year'))
            $query->whereYear('check_in', '=', $request->year);

        if ($user->hasRole('developer'))
            $query->where('employee_id', $user->id);

        $attendances = $query->paginate($request->per_page ?? 25);
        return response()->json([
            'status'  => 'Success',
            'attendance' => $attendances
        ], 201);
    }

    public function employeeDelay(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'month'       => 'sometimes|required',
            'year'        => 'sometimes|required'
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()]);
        }

        $users = User::whereHas('roles', function ($role) {
            $role->where('slug', 'developer');
        })->where('status', 'active')->pluck('id')->toArray();

        $delay_array = array();
        foreach ($users as $user) {
            $data = Attendace::with('employee')->where('employee_id', $user)->whereTime('check_in', '>', Carbon::parse('09:30'))
                ->whereYear('check_in', '=', $request->year)->whereMonth('check_in', '=', $request->month)->get();
            if ($data->isNotEmpty()) {
                $delay_array[] = [
                    'id' => $data[0]->employee_id,
                    'name' => $data[0]->employee->name,
                    'delay' => count($data),
                ];
            }
        }
        return response()->json([
            'status'  => 'Success',
            'delay' => $delay_array
        ], 201);
    }
}
