<?php

namespace App\Http\Controllers;

use App\Models\Attendace;
use App\Models\BreakTime;
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
            'check_out' => null
        ]);
        // $attendance = Attendace::findOrFail($data->id);
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
                'message'  => 'Success',
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
                'message'  => 'Success',
                'message' => "You are successfully checked-out"
            ], 201);
        }
        return response()->json([
            'message' => "You are already checked-out"
        ], 201);
    }

    public function employeeAttendance(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'employee_id' => 'sometimes|required|exists:users,id',
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 500);
        }

        $user = User::findOrFail(Auth::id());
        $query = Attendace::with('employee');

        if ($request->has('employee_id'))
            $query->where('employee_id', $request->employee_id);
        if ($request->has('month'))
            $query->whereMonth('created_at', '=', $request->month);
        if ($request->has('year'))
            $query->whereYear('created_at', '=', $request->year);
        if ($request->has('date'))
            $query->whereDay('created_at', '=', $request->date);


        if ($user->hasRole('developer'))
            $query->where('employee_id', $user->id);

        $attendances = $query->latest()->paginate($request->per_page ?? 25);
        return response()->json([
            'message'  => 'Success',
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
        $query = User::delays($year, $month)->whereHas('roles', function ($role) {
            $role->where('slug', 'developer')->orWhere('slug', 'manager');
        })->where('status', 'active')->orderBy('created_at', 'desc');

        $user = User::findOrFail(Auth::id());
        if (!$user->hasRole('admin'))
            $queries = $query->where('id', $user->id);

        $queries = $query->paginate($request->per_page ?? 8);

        return response()->json([
            'message'  => 'Success',
            'delay' => $queries
        ], 201);
    }

    public function breakStart(Request $request)
    {
        Validator::make($request->all(), [
            'reason' => 'required|string',
        ]);

        $break = BreakTime::create([
            'employee_id' => Auth::id(),
            'start_time' => Carbon::now(),
            'reason' => $request->reason,
        ]);

        return response()->json([
            'break'  => $break,
            'message' => "Break Start"
        ], 200);
    }

    public function breakEnd(Request $request)
    {
        $break = BreakTime::whereDate('created_at', '=', date('Y-m-d'))->where('end_time', null)->where('employee_id', Auth::id())->latest()->first();
        $break->update(['end_time' => Carbon::now()]);

        return response()->json([
            'message' => "Break End"
        ], 200);
    }

    public function getBreakData(Request $request)
    {
        $user = Auth::user();
        $query = BreakTime::with('employee');
        if ($user->hasRole('developer'))
            $query->where('employee_id', $user->id);
        if ($request->has('employee_id'))
            $query->where('employee_id', $request->employee_id);
        if ($request->has('month'))
            $query->whereMonth('created_at', '=', $request->month);
        if ($request->has('year'))
            $query->whereYear('created_at', '=', $request->year);
        if ($request->has('date'))
            $query->whereDate('created_at', '=', $request->date);
        else
            $query->whereDate('created_at', '=', date('Y-m-d'));

        $breaks = $query->latest()->paginate($request->per_page ?? 25);
        return response()->json([
            'message'  => 'Success',
            'attendance' => $breaks
        ], 201);
    }

    public function attendaceUpdate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:attendaces,id',
            'check_in' => 'required',
            'check_out' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 500);
        }

        $attendance = Attendace::findOrFail($request->id);

        $attendance->update([
            'check_in' => $request->check_in,
            'check_out' => $request->check_out,
        ]);

        return response()->json([
            'message' => 'Updated Successfully',
        ], 200);
    }
}
