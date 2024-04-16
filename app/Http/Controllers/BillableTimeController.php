<?php

namespace App\Http\Controllers;

use App\Models\BillableTime;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BillableTimeController extends Controller
{
    use ApiResponseTrait;

    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $per_page = request('per_page', 25);
        $billableTime = BillableTime::with(['user', 'task', 'project']);

        if (request('ids')) {
            $billableTime->whereIn('id', request('ids'));
        }
        if (request('by_user')) {
            $billableTime->where('user_id', request('by_user'));
        }
        if (request('by_project')) {
            $billableTime->where('project_id', request('by_project'));
        }
        if (request('start_date')) {
            $billableTime->where('date', '>=', request('start_date'));
        }
        if (request('end_date')) {
            $billableTime->where('date', '<=', request('end_date'));
        }

        $data = $billableTime->latest()->paginate($per_page);

        return $this->success('Billable time retrieved successfully', $data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'project_id' => 'required|exists:projects,id',
            'task_id' => 'required|exists:tasks,id',
            'user_id' => 'required|exists:users,id',
            'site' => 'sometimes|required|numeric',
            'time_spent' => 'required|numeric',
            'date' => 'required|date_format:Y-m-d H:i:s',
            'comment' => 'sometimes|required|string',
            'screenshot' => 'sometimes|required|string',
            'given_time' => 'sometimes|required|string',
            'is_freelancer' => 'sometimes|required|boolean',
        ]);

        $billableTime = BillableTime::create($request->all());

        return $this->success('Billable time added successfully', $billableTime);
    }

    /**
     * Display the specified resource.
     *
     * @param BillableTime $billableTime
     * @return JsonResponse
     */
    public function show(BillableTime $billableTime): JsonResponse
    {
        return $this->success('Billable time retrieved successfully', $billableTime);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param BillableTime $billableTime
     * @return JsonResponse
     */
    public function update(Request $request, BillableTime $billableTime): JsonResponse
    {
        $request->validate([
            'project_id' => 'sometimes|required|exists:projects,id',
            'task_id' => 'sometimes|required|exists:tasks,id',
            'user_id' => 'sometimes|required|exists:users,id',
            'site' => 'sometimes|required|numeric',
            'time_spent' => 'sometimes|required|numeric',
            'date' => 'sometimes|required|date_format:Y-m-d H:i:s',
            'comment' => 'sometimes|required|string',
            'screenshot' => 'sometimes|required|string',
            'given_time' => 'sometimes|required|string',
            'is_freelancer' => 'sometimes|required|boolean',
        ]);

        $billableTime->update($request->all());

        return $this->success('Billable time updated successfully', $billableTime);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param BillableTime $billableTime
     * @return JsonResponse
     */
    public function destroy(BillableTime $billableTime): JsonResponse
    {
        $billableTime->delete();

        return $this->success('Billable time deleted successfully');
    }
}
