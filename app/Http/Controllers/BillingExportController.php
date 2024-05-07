<?php

namespace App\Http\Controllers;

use App\Exports\BillingExport;
use App\Models\BillableTime;
use App\Traits\ApiResponseTrait;
use App\Traits\CommonTrait;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class BillingExportController extends Controller
{
    use ApiResponseTrait, CommonTrait;

    public function export(Request $request)
    {
        $request->validate([
            'ids' => 'sometimes|required',
            'format' => 'sometimes|required|in:excel,xlsx,csv',
            'by_user' => 'sometimes|required|exists:users,id',
            'by_project' => 'sometimes|required|exists:projects,id',
            'client_id' => 'sometimes|required|exists:users,id',
            'start_date' => 'sometimes|required|date',
            'end_date' => 'sometimes|required|date',
        ]);
        $cols = [
            'project.name',
            'site',
            'user.name',
            'task.title',
            'task.estimated_time',
            'task.status',
            'time_spent',
            'given_time',
            'comment',
            'screenshot',
            'created_at',
        ];
        $headers = [
            'Project',
            'Site',
            'User',
            'Task',
            'Estimated Time',
            'Task Status',
            'Time Spent',
            'Given Time',
            'Comment',
            'Screenshot',
            'Created At',
        ];
        $exortableData = BillableTime::with(['user', 'task', 'project']);

        if ($request->ids) {
            $exortableData->whereIn('id', $request->ids);
        }
        if ($request->by_user) {
            $exortableData->where('user_id', $request->by_user);
        }
        if ($request->by_project) {
            $exortableData->where('project_id', $request->by_project);
        }
        if ($request->client_id) {
            $exortableData->where('client_id', $request->client_id);
        }
        if ($request->start_date) {
            $exortableData->where('date', '>=', $request->start_date);
        }
        if ($request->end_date) {
            $exortableData->where('date', '<=', $request->end_date);
        }

        $exortableData = $exortableData->get();

        return $this->exportData(BillableTime::class, $cols, $headers, 'billing_export', $exortableData);
    }
}
