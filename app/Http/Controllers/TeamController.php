<?php

namespace App\Http\Controllers;

use App\Models\Team;
use Illuminate\Http\Request;

class TeamController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $this->validateWith([
            'title' => 'sometimes|required|string',
            'project_id' => 'sometimes|required|exists:projects,id'
        ]);

        $teams = Team::filter($request)->latest()->paginate($request->per_page ?? 25);

        return response()->json([
            'teams' => $teams,
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function updateOrCreateTeam(Request $request)
    {
        $this->validateWith([
            'id'         => 'sometimes|required|exists:teams,id',
            'project_id' => 'required|exists:projects,id',
            'title'      => 'sometimes|required',
            'status'      => 'sometimes|required|in:active,inactive',
            'user_ids'   => 'sometimes|required|array',
        ]);

        $updatable = [];
        if ($request->has('title')) $updatable['title'] = $request->title;

        if ($request->has('status')) $updatable['status'] = $request->status;

        if (!empty($updatable)) {
            $team = Team::updateOrCreate([
                'id' => $request->id ?? null,
                'project_id' => $request->project_id
            ], $updatable);
        }

        if ($request->has('user_ids')) {
            $team->teamUsers()->sync($request->user_ids);
        }

        return response()->json([
            'team' => $team,
        ], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Team  $team
     * @return \Illuminate\Http\Response
     */
    public function show($team_id)
    {
        $team = Team::findOrFail($team_id);

        return response()->json([
            'team' => $team,
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Team  $team
     * @return \Illuminate\Http\Response
     */
    public function destroy($team_id)
    {
        Team::destroy($team_id);

        return response()->json([
            'message' => 'Deleted Success',
        ], 200);
    }
}
