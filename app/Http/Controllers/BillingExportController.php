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
            'task',
//            'task.estimated_time',
//            'task.status',
            'time_spent',
            'given_time',
            'comment',
            'date',
            'screenshot',
            'created_at',
        ];
        $headers = [
            'Project',
            'Site',
            'User',
            'Task',
//            'Estimated Time',
//            'Task Status',
            'Time Spent',
            'Given Time',
            'Comment',
            'Date',
            'Screenshot',
            'Created At',
        ];
        $exortableData = BillableTime::with(['user', 'project']);

        if (\request('search_query')) {
            $exortableData->search(\request('search_query'), [
                '%site',
                '%time_spent',
                '%given_time',
                '%comment',
                'user|%name,%email,%phone,%designation',
                'project|%name,%budget',
                'project.client|%name,%budget',
//                'task|%title,%description,%reference,%priority,%site,%estimated_time,%status',
            ]);
        }

        if ($request->ids) $exortableData->whereIn('id', $request->ids);
        if ($request->by_user) {
            $exortableData->whereIn('user_id', request('by_user'));
            $exortableData->orWhereHas('project.client', function ($query) {
                $query->whereIn('id', request('by_user'));
            });
        }
        if ($request->by_project) $exortableData->whereIn('project_id', $request->by_project);
        if ($request->client_id) $exortableData->whereIn('client_id', $request->by_user);
//        if ($request->client_id) $exortableData->whereIn('client_id', $request->client_id);
        if ($request->start_date) $exortableData->whereDate('date', '>=', $request->start_date);
        if ($request->end_date) $exortableData->whereDate('date', '<=', $request->end_date);

        $exortableData = $exortableData->get();

        return $this->exportData(BillableTime::class, $cols, $headers, 'billing_export', $exortableData);
    }
}
