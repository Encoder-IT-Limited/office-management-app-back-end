<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use Exception;
use Carbon\Carbon;

use App\Models\{User, Attendance};

class AttendanceController extends Controller
{
    private $year, $month, $date;

    public function __construct()
    {
        $this->year = Carbon::today()->format('Y');
        $this->month = Carbon::today()->format('m');
        $this->date = Carbon::today()->format('d');
    }

    public function checkIn(Request $request)
    {
        $user = User::findOrFail(Auth::id());
        $checkedInAt = Carbon::now()->subMinutes(5);
        if ($user->hasRole('admin')) {
            if ($request->has('employee_id')) {
                $user = User::findOrFail($request->employee_id);
            }
            if ($request->has('check_in')) {
                $checkedInAt = Carbon::parse($request->check_in);
            }
        }

        $delayTime = Carbon::parse($user->delay_time);

        if (!$user->todayAttendance) {
            $todayAttendance = $user->todayAttendance()->create([
                'check_in' => $checkedInAt,
                'delay_time' => $delayTime
            ])->refresh();

            return response()->json([
                'attendance'  => $todayAttendance->load('employee'),
            ], 200);
        } else throw new Exception('You are already checked-in', 500);
    }

    public function checkOut(Request $request)
    {
        $user = User::findOrFail(Auth::id());
        $checkedOutAt = Carbon::now();

        if ($user->hasRole('admin')) {
            if ($request->has('employee_id')) {
                $user = User::findOrFail($request->employee_id);
            }
            if ($request->has('check_out')) {
                $checkedOutAt = Carbon::parse($request->check_out);
            }
        }

        if ($user->todayAttendance && !$user->todayAttendance->check_out) {
            $user->todayAttendance->update([
                'check_out' => $checkedOutAt
            ]);

            return response()->json([
                'attendance'  => $user->todayAttendance->load('employee'),
            ], 200);
        }
        throw new Exception('You are already checked-out or not checked-in yet!', 500);
    }

    public function createAttendance(Request $request)
    {
        $validated = $this->validateWith([
            'employee_id' => 'required|exists:users,id',
            'id' => 'sometimes|required|exists:attendances,id',
            'check_in' => 'sometimes|required',
            'check_out' => 'sometimes|required'
        ]);

        $user = User::findOrFail($request->employee_id);

        $validated['delay_time'] = Carbon::parse($request->delay_time) ?? Carbon::parse($user->delay_time);

        $attendance = Attendance::updateOrCreate($request->except(['check_in', 'check_out']), $validated);

        return response()->json([
            'attendance' => $attendance,
        ], 200);
    }

    public function getEmployeeAttendances(Request $request)
    {
        $validated = $this->validateWith([
            'year'        => 'sometimes|required',
            'month'       => 'sometimes|required',
            'date'        => 'sometimes|required',
            'employee_id' => 'sometimes|required|exists:users,id',
        ]);

        $this->year = $validated['year'] ?? $this->year;
        $this->month = $validated['month'] ?? $this->month;

        $queries = Attendance::with('employee')->whereYear('check_in', '=', $this->year)
            ->whereMonth('check_in', '=', $this->month);

        $user = User::findOrFail(Auth::id());
        if ($user->hasRole('admin')) {
            $queries->when($request->has('employee_id'), function ($employeeQ) use ($request) {
                $employeeQ->where('employee_id', $request->employee_id);
            })->when($request->has('date'), function ($dateQ) use ($request) {
                $dateQ->whereDay('check_in', '=', $request->date);
            });
        }

        if ($user->hasRole('developer')) $queries->where('employee_id', $user->id);

        $attendances = $queries->orderByDesc('check_in')->paginate($request->per_page ?? 31);

        return response()->json([
            'attendance' => $attendances
        ], 200);
    }

    public function deleteAttendances($id)
    {
        Attendance::destroy($id);
        return response()->json([
            'message' => 'Attendances deleted successfully!'
        ], 200);
    }

    public function getEmployeeDelays(Request $request)
    {
        $validated = $this->validateWith([
            'month'       => 'sometimes|required',
            'year'        => 'sometimes|required',
        ]);

        $this->year = $validated['year'] ?? $this->year;
        $this->month = $validated['month'] ?? $this->month;

        $employees = User::delaysCount($this->year, $this->month)->paginate($request->per_page ?? 20);

        return response()->json([
            'employees' => $employees ?? []
        ], 200);
    }
}
