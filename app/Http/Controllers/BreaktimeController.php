<?php

namespace App\Http\Controllers;

use App\Http\Resources\BreakTimeResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use Carbon\Carbon;

use App\Models\{User, BreakTime};

class BreaktimeController extends Controller
{
    private $year, $month, $date;

    public function __construct()
    {
        $this->year = Carbon::today()->format('Y');
        $this->month = Carbon::today()->format('m');
        $this->date = Carbon::today()->format('d');
    }

    public function startingBreak(Request $request)
    {
        $this->validateWith([
            'reason' => 'required|string',
        ]);

        $user = User::findOrFail(Auth::id());

        // existing break did not end
        $breaks = $user->breakTimes()->whereDate('created_at', Carbon::now())->whereNull('end_time')->get();
        if ($breaks->count() > 0) {
            return response()->json([
                'break' => $breaks->first()->load('employee'),
            ], 200);
        }

        $break = $user->breakTimes()
            ->create([
                'start_time' => Carbon::now(),
                'reason' => $request->reason,
            ]);

        return response()->json([
            'break' => $break->load('employee'),
        ], 200);
    }

    public function endingBreak(Request $request)
    {
        $user = auth()->user();
        $breaks = $user->breakTimes()->whereDate('created_at', Carbon::now())->whereNull('end_time')->get();
        if ($breaks->count() === 0) {
            return response()->json([
                'message' => 'No break found to end!',
            ], 404);
        }
        foreach ($breaks as $break) {
            info($break->toArray());
            $break->update([
                'end_time' => Carbon::now(),
            ]);
        }
        info($breaks->toArray());

        return response()->json([
            'break' => $user->breakTimes()->latest()->first()->load('employee'),
        ], 200);
    }

    public function createBreak(Request $request)
    {
        $validated = $this->validateWith([
            'id' => 'sometimes|required|exists:break_times,id',
            'employee_id' => 'required|exists:users,id',
            'start_time' => 'required',
            'end_time' => 'sometimes|required',
            'reason' => 'required'
        ]);

        $break = BreakTime::updateOrCreate([
            'id' => $request->id,
        ], $validated);

        return response()->json([
            'break' => $break,
        ], 200);
    }

    public function getEmployeeBreaks(Request $request)
    {
        $user = auth()->user();
        abort_if(!($user->hasPermission('read-my-user')), 403, 'You are not allowed to access this resource!');
        $validated = $this->validateWith([
            'year' => 'sometimes|required',
            'month' => 'sometimes|required',
            'date' => 'sometimes|required',
            'employee_id' => 'sometimes|required|exists:users,id',
        ]);

        $this->year = $validated['year'] ?? $this->year;
        $this->month = $validated['month'] ?? $this->month;
        $this->date = $validated['date'] ?? $this->date;

        $query = User::query();
        if (!$user->hasRole('admin')) {
            $query->where('id', $user->id);
        }
        $query->with(['breakTimes' => function ($breakQ) {
            $breakQ->breakFilter($this->year, $this->month, $this->date)
                ->select('employee_id')
                ->selectRaw('SUM(TIMESTAMPDIFF(SECOND, start_time, end_time)) as break_duration')
                ->groupBy('employee_id');
        }])->withCount(['breakTimes as break_count' => function ($breakQ) {
            return $breakQ->breakFilter($this->year, $this->month, $this->date);
        }])
//            ->filteredByPermissions()
            ->onlyDeveloper();

        $employees = $query->paginate($request->per_page ?? 20);

        $employees->getCollection()->transform(function ($employee) {
            $employee = $employee->toArray();
            if ($employee['break_count'] > 0) {
                $employee['break_time_duration'] = (int)$employee['break_times'][0]['break_duration'];
            } else {
                $employee['break_time_duration'] = 0;
            }
            unset($employee['break_times']);
            return $employee;
        });

        return response()->json([
            'employees' => $employees ?? []
        ], 200);
    }

    public function getEmployeeBreakDetails(Request $request)
    {
        $validated = $this->validateWith([
            'year' => 'required',
            'month' => 'required',
            'date' => 'required',
            'employee_id' => 'required|exists:users,id',
        ]);

        $this->year = $validated['year'] ?? $this->year;
        $this->month = $validated['month'] ?? $this->month;
        $this->date = $validated['date'] ?? $this->date;

        $user = User::findOrFail(Auth::id());
        $queries = BreakTime::with('employee')->breakFilter($this->year, $this->month, $this->date);

        if ($user->hasRole('admin')) {
            $queries->when($request->has('employee_id'), function ($employeeQ) use ($request) {
                $employeeQ->where('employee_id', $request->employee_id);
            });
        } else if ($user->hasRole('developer')) $queries->where('employee_id', $user->id);

        $breaks = $queries->orderByDesc('start_time')->paginate($request->per_page ?? 25);

        return response()->json([
            'breaks' => $breaks
        ], 200);
    }

    public function deleteBreakTime($id)
    {
        BreakTime::destroy($id);
        return response()->json([
            'message' => 'Break deleted successfully!'
        ], 200);
    }
}
