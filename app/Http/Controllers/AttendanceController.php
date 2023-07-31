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
        $user = User::findOrFail(Auth::id());
        $check_in = Carbon::now();
        if ($user->hasRole('admin')) {
            $check_in = Carbon::createFromFormat('Y-m-d H:i:s', $request->check_in);
        }

        $user_check = Attendace::whereDate('check_in', $check_in)->where('employee_id', $user->id)->first();
        if ($user_check)
            return response()->json([
                'message' => "You are already checked-in"
            ], 201);;

        $attendance = Attendace::create([
            'employee_id' => Auth::id(),
            'check_in' => $check_in->subMinutes(5),
        ]);

        return response()->json([
            'attendance'  => $attendance,
            'message' => "You are successfully checked-in"
        ], 200);
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
            return response()->json(['error' => $validator->errors()], 500);
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

        $attendances = $query->latest()->paginate($request->per_page ?? 25);
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
            return response()->json(['error' => $validator->errors()], 500);
        }
        $year = $request->year;
        $month = $request->month;
        $queries = User::delays($year, $month)->whereHas('roles', function ($role) {
            $role->where('slug', 'developer')->orWhere('slug', 'manager');
        })->where('status', 'active')->orderBy('created_at', 'desc')->paginate($request->per_page ?? 8);

        return response()->json([
            'status'  => 'Success',
            'delay' => $queries
        ], 201);
    }
}
