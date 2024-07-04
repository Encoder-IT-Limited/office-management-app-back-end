<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\LabelStatus;
use Illuminate\Support\Facades\DB;
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

        $queries = LabelStatus::query();
        if ($request->has('type')) $queries = $queries->where('type', $request->type);
        if ($request->has('franchise')) $queries = $queries->where('franchise', $request->franchise);
        if ($request->has('project_id')) $queries = $queries->where('project_id', $request->project_id);

        $labelStatus = $queries->latest()->paginate($request->per_page ?? 25);

        return response()->json([
            'label_status' => $labelStatus
        ], 200);
    }

    public function setLabelStatus(Request $request)
    {
        $this->validateWith([
            'id' => 'sometimes|required|exists:label_statuses,id',
            'title' => 'required|string',
            'color' => 'sometimes|required',
            'type' => 'required|in:label,status',
            'franchise' => 'required|in:project,task',
            'project_id' => 'sometimes|required|exists:projects,id'
        ]);

        $labelStatus = LabelStatus::updateOrCreate([
            'id' => $request->id ?? null,
            'project_id' => $request->project_id
        ], $request->except('id', 'project_id'));

        $labelStatus = LabelStatus::findOrFail($request->id ?? $labelStatus->id);

        if (!$request->has('id')) {
            $maxOrder = LabelStatus::where('project_id', $request->project_id)->max('list_order');
            $labelStatus->list_order = $maxOrder + 1;
            $labelStatus->save();
        }

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

    public function reorderLabelStatus(Request $request)
    {
        $labelStatus = LabelStatus::findOrFail($request->id);

        $newOrder = $request->new_order;
        $oldOrder = $labelStatus->list_order;

        if ($oldOrder != $newOrder) {
            if ($oldOrder < $newOrder) {
                LabelStatus::where('list_order', '<=', $newOrder)
                    ->where('list_order', '>', $oldOrder)
                    ->where('project_id', $labelStatus->project_id)
                    ->update(
                        ['list_order' => DB::raw('list_order - 1')]
                    );
            } else {
                $data = LabelStatus::where('list_order', '>=', $newOrder)
                    ->where('list_order', '<', $oldOrder)
                    ->where('project_id', $labelStatus->project_id)
                    ->update(
                        ['list_order' => DB::raw('list_order + 1')]
                    );
            }
            $labelStatus->list_order = $newOrder;
            $labelStatus->save();
        }

        return response()->json([
            'status' => $labelStatus->refresh()
        ], 200);
    }
}
