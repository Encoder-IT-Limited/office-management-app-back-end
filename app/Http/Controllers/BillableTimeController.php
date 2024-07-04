<?php

namespace App\Http\Controllers;

use App\Http\Requests\BillableTimeRequest;
use App\Models\BillableTime;
use App\Traits\ApiResponseTrait;
use Carbon\Carbon;
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
        $billableTime = BillableTime::with(['user', 'project']);

        if (\request('search_query')) {
            $billableTime->search(\request('search_query'), [
                '%site',
                '%task',
                '%time_spent',
                '%given_time',
                '%comment',
                'user|%name,%email,%phone,%designation',
                'project|%name,%budget',
//                'task|%title,%description,%reference,%priority,%site,%estimated_time,%status',
            ]);
        }

        if (request('ids')) {
            $billableTime->whereIn('id', request('ids'));
        }
        if (auth()->user()->hasRole('admin')) {
            if (request('by_user')) {
                $billableTime->whereIn('user_id', request('by_user'));
            }
        } else if (auth()->user()->hasRole('developer') && !empty(auth()->user()->children)) {
            if (request('by_user')) {
                $billableTime->whereIn('user_id', [auth()->id(), ...auth()->user()->children->pluck('id')->toArray()]);
            } else {
                $billableTime->where('user_id', auth()->id());
            }
        } else {
            $billableTime->where('user_id', auth()->id());
        }
        if (request('by_project')) {
            $billableTime->whereIn('project_id', request('by_project'));
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
     * @param BillableTimeRequest $request
     * @return JsonResponse
     */
    public function store(BillableTimeRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['time_spent'] = (string)Carbon::parse($data['time_spent'])->format('H:i');
        $data['given_time'] = (string)Carbon::parse($data['given_time'])->format('H:i');
//        if (isset($data['time_spent']['hours']) && isset($data['time_spent']['minutes'])) {
//            $data['time_spent'] = $data['time_spent']['hours'] + $data['time_spent']['minutes'];
//        } else {
//            $data['time_spent'] = 0;
//        }
//
//        if (isset($data['given_time']['hours']) && isset($data['given_time']['minutes'])) {
//            $data['given_time'] = $data['given_time']['hours'] + $data['given_time']['minutes'];
//        } else {
//            $data['given_time'] = 0;
//        }

        $billableTime = BillableTime::create($data);
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
     * @param BillableTimeRequest $request
     * @param BillableTime $billableTime
     * @return JsonResponse
     */
    public function update(BillableTimeRequest $request, BillableTime $billableTime): JsonResponse
    {
        $billableTime->update($request->validated());
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
