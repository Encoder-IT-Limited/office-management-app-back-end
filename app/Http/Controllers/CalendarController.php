<?php

namespace App\Http\Controllers;

use App\Models\ProjectTask;
use Illuminate\Http\Request;

class CalendarController extends Controller
{
    public function developerCalendar($id)
    {
        $developerDates = ProjectTask::where('developer_id', $id)->get();

        $developerCalendars = array();
        foreach ($developerDates as $developerDate) {
            $developerCalendar = array(
                'project'   => $developerDate->project->name,
                'task'      => $developerDate->task,
                'startDate' => $developerDate->start_date,
                'enddate'   => $developerDate->end_date
            );
            $developerCalendars[] = $developerCalendar;
        }

        return response()->json($developerCalendars);
    }

    public function projectCalendar($id)
    {
        $projectDates = ProjectTask::where('project_id', $id)->get();

        $projectCalendars = array();
        foreach ($projectDates as $projectDate) {
            $projectCalendar = array(
                'developer' => $projectDate->developer->name,
                'task'      => $projectDate->task,
                'startDate' => $projectDate->start_date,
                'enddate'   => $projectDate->end_date
            );
            $projectCalendars[] = $projectCalendar;
        }

        return response()->json($projectCalendars);
    }
}
