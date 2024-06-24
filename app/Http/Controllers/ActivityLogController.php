<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    use ApiResponseTrait;

    /**
     * Display a listing of the resource.
     */
    public function index(): \Illuminate\Http\JsonResponse
    {
        $activity = ActivityLog::with('causer');
        if (request('user_id')) {
            $activity = $activity->where('causer_id', request('user_id'));
        }
        if (request('log_name')) {
            $activity = $activity->where('log_name', request('log_name'));
        }
        $activity = $activity->latest()->paginate(20);
        return $this->success('Activity Log Retrieved Successfully', $activity);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Activity $activity)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Activity $activity)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Activity $activity)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Activity $activity)
    {
        //
    }
}
