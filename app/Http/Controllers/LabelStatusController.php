<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\LabelStatus;
use Illuminate\Support\Facades\Redis;

class LabelStatusController extends Controller
{
    public function getLabelStatus(Request $request)
    {
        $this->validateWith([
            'type' => 'sometimes|required|in:label,status',
            'franchise' => 'sometimes|required|in:project,task',
            'project_id' => 'sometimes|required|exists:projects,id'
        ]);

        $queries = LabelStatus::filter($request);

        $labelStatus = $queries->latest()->paginate($request->per_page ?? 25);

        return response()->json([
            'label_status' => $labelStatus
        ], 200);
    }

    public function setLabelStatus(Request $request)
    {
        $this->validateWith([
            'id'      => 'sometimes|required|exists:label_statuses,id',
            'title'      => 'required|string',
            'color'      => 'sometimes|required',
            'type'       => 'required|in:label,status',
            'franchise'  => 'required|in:project,task',
            'project_id' => 'required|exists:projects,id'
        ]);

        $labelStatus = LabelStatus::updateOrCreate([
            'id' => $request->id,
            'project_id' => $request->project_id
        ], $request->except('id', 'project_id'));

        $labelStatus = LabelStatus::findOrFail($request->id ?? $labelStatus->id);

        return response()->json([
            'label_status' => $labelStatus
        ], 200);
    }

    public function updateLabelStatus(Request $request)
    {
        $validated = $this->validateWith([
            'title'      => 'required|string',
            'color'      => 'sometimes|required',
            'type'       => 'required|in:label,status',
            'franchise'  => 'required|in:project,task',
            'project_id' => 'required|exists:projects,id'
        ]);

        LabelStatus::updateOrCreate(['id' => $request->id], $validated);

        $labelStatus = LabelStatus::filter($request)->latest()->first();

        return response()->json([
            'label_status' => $labelStatus
        ], 200);
    }

    public function deleteLabelStatus($id)
    {
        LabelStatus::destroy($id);

        return response()->json([
            'message' => 'Deleted Successfully',
        ], 200);
    }
}
