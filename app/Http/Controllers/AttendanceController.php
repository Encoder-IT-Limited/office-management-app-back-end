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
        $checkedInAt = Carbon::now()->subMinutes(5);

        if ($user->attendances()->whereDate('check_in', $checkedInAt)->count() > 0)
            return response()->json([
                'message' => "You are already checked-in"
            ], 500);

        $attendance = Attendace::create([
            'employee_id' => Auth::id(),
            'check_in' => $checkedInAt,
            'check_out' => null
        ]);

        return response()->json([
            'attendance'  => $attendance->load('employee'),
            'message' => "You are successfully checked-in"
        ], 200);
    }

    public function checkOut(Request $request)
    {
        $checkedOutAt = Carbon::now();
        $user = User::findOrFail(Auth::id());

        if ($user->attendances()->whereDate('check_in', $checkedOutAt)->count() == 0) {
            return response()->json([
                'message'  => 'Success',
                'message' => "You are not checked-in"
            ], 201);
        }

        $todayAttendance = $user->attendances()->whereDate('check_in', $checkedOutAt)->whereNull('check_out');

        if ($todayAttendance->count() > 0) {
            $attendance = $todayAttendance->first();
            $attendance->update([
                'check_out' => $checkedOutAt
            ]);


            return response()->json([
                'attendance'  => $attendance->load('employee'),
                'message' => "You are successfully checked-out"
            ], 200);
        }

        return response()->json([
            'message' => "You are already checked-out"
        ], 201);
    }

    public function employeeAttendance(Request $request)
    {
        $this->validateWith([
            'employee_id' => 'sometimes|required|exists:users,id',
        ]);

        $user = User::findOrFail(Auth::id());
        $query = Attendace::with('employee');

        if ($request->has('employee_id'))
            $query->where('employee_id', $request->employee_id);
        if ($request->has('month'))
            $query->whereMonth('check_in', '=', $request->month);
        if ($request->has('year'))
            $query->whereYear('check_in', '=', $request->year);
        if ($request->has('date'))
            $query->whereDay('check_in', '=', $request->date);


        if ($user->hasRole('developer'))
            $query->where('employee_id', $user->id);

        $attendances = $query->latest()->paginate($request->per_page ?? 31);
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
        $user = User::findOrFail(Auth::id());
        $delay_time = $user->delay_time;
        $year = $request->year;
        $month = $request->month;
        $query = User::delays($year, $month, $delay_time)->whereHas('roles', function ($role) {
            $role->where('slug', 'developer')->orWhere('slug', 'manager');
        })->where('status', 'active')->orderBy('created_at', 'desc');

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
            'check_out' => 'nullable'
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
            'attendance' => $attendance->load('employee'),
            'message' => 'Updated Successfully',
        ], 200);
    }
}
