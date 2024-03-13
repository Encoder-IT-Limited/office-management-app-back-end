<?php

use App\Http\Controllers\ProjectNoteController;
use App\Http\Controllers\UserNoteController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApiKeyController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BreaktimeController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\EmployeeNoteController;
use App\Http\Controllers\LabelStatusController;
use App\Http\Controllers\LeaveController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ReminderController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SkillController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\UserController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {

    Route::get('logout', [AuthController::class, 'logout']);

    Route::prefix('roles')->middleware('role:admin')->group(function () {
        Route::get('/', [RoleController::class, 'index'])->middleware('permission:read-role');
        Route::post('store', [RoleController::class, 'store'])->middleware('permission:read-role');
        Route::get('show/{id}', [RoleController::class, 'show'])->middleware('permission:read-role');
        Route::patch('update', [RoleController::class, 'update'])->middleware('permission:read-role');
        Route::delete('delete/{id}', [RoleController::class, 'destroy'])->middleware('permission:read-role');
        Route::get('all-permissions', [RoleController::class, 'allPermissions']);
        Route::post('permission-store', [RoleController::class, 'permissionStore']);
    });

    Route::prefix('users')->group(function () {
        Route::get('/', [UserController::class, 'index'])->middleware('permission:read-user,read-my-user,read-client-user');
        Route::post('store', [UserController::class, 'store'])->middleware('permission:store-user');
        Route::get('show/{id}', [UserController::class, 'show'])->middleware('permission:show-user');
        Route::post('update', [UserController::class, 'update'])->middleware('permission:update-user');
        Route::post('status-update', [UserController::class, 'updateUserStatus'])->middleware('permission:update-user');
        Route::delete('delete/{id}', [UserController::class, 'destroy'])->middleware('permission:delete-user');
        Route::post('restore', [UserController::class, 'restore'])->middleware('permission:delete-user');
        Route::get('/details', [UserController::class, 'details']);
        Route::patch('/details', [UserController::class, 'updateOwnProfile']);
    });

    Route::prefix('api-keys')->group(function () {
        Route::get('/', [ApiKeyController::class, 'index']);
        Route::post('/', [ApiKeyController::class, 'store']);
    });

    Route::prefix('skills')->group(function () {
        Route::get('/', [SkillController::class, 'index'])->middleware('permission:read-skill');
        Route::post('store', [SkillController::class, 'store'])->middleware('permission:store-skill');
        Route::get('show/{id}', [SkillController::class, 'show'])->middleware('permission:show-skill');
        Route::patch('update', [SkillController::class, 'update'])->middleware('permission:update-skill');
        Route::delete('delete/{id}', [SkillController::class, 'destroy'])->middleware('permission:delete-skill');
    });

    Route::prefix('leave')->group(function () {
        Route::get('/', [LeaveController::class, 'index'])->middleware('permission:read-leave');
        Route::post('store', [LeaveController::class, 'store'])->middleware('permission:store-leave');
        Route::get('show/{id}', [LeaveController::class, 'show'])->middleware('permission:show-leave');
        Route::patch('update', [LeaveController::class, 'update'])->middleware('permission:update-leave');
        Route::delete('delete/{id}', [LeaveController::class, 'destroy'])->middleware('permission:delete-leave');
        Route::post('status', [LeaveController::class, 'leaveStatus'])->middleware('permission:status-update-leave');
    });

    Route::prefix('employee-notes')->group(function () {
        Route::get('/', [EmployeeNoteController::class, 'index'])->middleware('permission:read-note');
        Route::post('store', [EmployeeNoteController::class, 'store'])->middleware('permission:store-note');
        Route::get('show/{id}', [EmployeeNoteController::class, 'show'])->middleware('permission:show-note');
        Route::patch('update', [EmployeeNoteController::class, 'update'])->middleware('permission:update-note');
        Route::delete('delete/{id}', [EmployeeNoteController::class, 'destroy'])->middleware('permission:delete-note');
    });

    Route::prefix('projects')->group(function () {
        Route::get('/', [ProjectController::class, 'index'])->middleware('permission:read-project,read-client-project,read-my-project');
        Route::post('/', [ProjectController::class, 'updateOrCreateProject'])->middleware('permission:store-project,update-project');
        Route::get('show/{id}', [ProjectController::class, 'show'])->middleware('permission:show-project');
        // Route::patch('update', [ProjectController::class, 'update'])->middleware('permission:update-project');
        Route::delete('delete/{id}', [ProjectController::class, 'destroy'])->middleware('permission:delete-project');
        Route::post('status-update', [ProjectController::class, 'updateProjectStatus']);
    });

    Route::prefix('teams')->group(function () {
        Route::get('/', [TeamController::class, 'index'])->middleware('permission:read-project,read-my-project,read-client-project');
        Route::get('show/{id}', [TeamController::class, 'show'])->middleware('permission:read-project,read-my-project,read-client-project');
        Route::post('/', [TeamController::class, 'updateOrCreateTeam'])->middleware('permission:read-project,read-my-project,read-client-project');
        Route::delete('/', [TeamController::class, 'destroy'])->middleware('permission:read-project,read-my-project,read-client-project');
    });

    Route::prefix('tasks')->group(function () {
        Route::get('/', [TaskController::class, 'index'])->middleware('permission:read-task,read-my-task,read-client-task');
        Route::post('/', [TaskController::class, 'store'])->middleware('permission:store-task');
        Route::get('show/{id}', [TaskController::class, 'show'])->middleware('permission:show-task');
        Route::post('reorder', [TaskController::class, 'reorderTask']);
        Route::delete('{id}', [TaskController::class, 'destroy'])->middleware('permission:delete-task');
        Route::delete('/status/{id}', [TaskController::class, 'destroyByStatus']);
    });

    Route::prefix('reminders')->group(function () {
        Route::get('/', [ReminderController::class, 'index'])->middleware('permission:read-reminder');
        Route::post('store', [ReminderController::class, 'store'])->middleware('permission:store-reminder');
        Route::get('show/{id}', [ReminderController::class, 'show'])->middleware('permission:show-reminder');
        Route::patch('update', [ReminderController::class, 'update'])->middleware('permission:update-reminder');
        Route::delete('delete/{id}', [ReminderController::class, 'destroy'])->middleware('permission:delete-reminder');
    });

    Route::prefix('calendar')->group(function () {
        Route::get('developer/{id}', [CalendarController::class, 'developerCalendar']);
        Route::get('project/{id}', [CalendarController::class, 'projectCalendar']);
        Route::get('calender_view', [CalendarController::class, 'calenderView']);
    });

    Route::prefix('dashboard')->group(function () {
        Route::get('index', [CalendarController::class, 'developerCalendar'])->middleware('read-calendar');
    });

    Route::prefix('breaks')->group(function () {
        Route::get('/', [BreaktimeController::class, 'getEmployeeBreaks']);
        Route::get('details', [BreaktimeController::class, 'getEmployeeBreakDetails'])->middleware('permission:read-break');
        Route::post('start', [BreaktimeController::class, 'startingBreak']);
        Route::get('end', [BreaktimeController::class, 'endingBreak']);
        Route::delete('{id}', [BreaktimeController::class, 'deleteBreakTime']);

        Route::post('create', [BreaktimeController::class, 'createBreak'])->middleware('permission:update-break');
    });

    Route::prefix('attendances')->group(function () {
        Route::get('/', [AttendanceController::class, 'getEmployeeAttendances']); //->middleware('permission:read-attendance');
        Route::get('check-in', [AttendanceController::class, 'checkIn']); //->middleware('permission:checkin-attendance');
        Route::get('check-out', [AttendanceController::class, 'checkOut']); //->middleware('permission:checkout-attendance');
        Route::get('delays', [AttendanceController::class, 'getEmployeeDelays']); //->middleware('permission:read-delays');
        Route::post('create', [AttendanceController::class, 'createAttendance']); //->middleware('permission:update-attendance');
        Route::delete('{id}', [AttendanceController::class, 'deleteAttendances']);
    });

    Route::prefix('label-status')->group(function () {
        Route::get('/', [LabelStatusController::class, 'getLabelStatus']);
        Route::post('/', [LabelStatusController::class, 'setLabelStatus']);
        Route::delete('{id}', [LabelStatusController::class, 'deleteLabelStatus']);
        Route::post('reorder', [LabelStatusController::class, 'reorderLabelStatus']);
    });

    // User Notes ...
    // Route::apiResource('user-note', UserNoteController::class);
    Route::prefix('user-note')->group(function () {
        Route::get('/', [UserNoteController::class, 'index'])->middleware('permission:read-user-note');
        Route::get('{userNote}', [UserNoteController::class, 'show'])->middleware('permission:read-user-note');
        Route::post('', [UserNoteController::class, 'store'])->middleware('permission:store-user-note');
        Route::patch('{userNote}', [UserNoteController::class, 'update'])->middleware('permission:update-user-note');
        Route::delete('{userNote}', [UserNoteController::class, 'destroy'])->middleware('permission:delete-user-note');
    });
    Route::get('trash/user-note', [UserNoteController::class, 'trash'])->middleware('permission:read-trashed-user-note');
    Route::get('restore/user-note/{userNote}', [UserNoteController::class, 'restore'])->withTrashed()->middleware('permission:restore-user-note');
    Route::delete('force-delete/user-note/{userNote}', [UserNoteController::class, 'forceDelete'])->withTrashed()->middleware('permission:force-delete-user-note');

    // Project Notes
    // Route::apiResource('project-note', ProjectNoteController::class);
    Route::prefix('project-note')->group(function () {
        Route::get('/', [ProjectNoteController::class, 'index'])->middleware('permission:read-project-note');
        Route::get('{projectNote}', [ProjectNoteController::class, 'show'])->middleware('permission:read-project-note');
        Route::post('', [ProjectNoteController::class, 'store'])->middleware('permission:store-project-note');
        Route::patch('{projectNote}', [ProjectNoteController::class, 'update'])->middleware('permission:update-project-note');
        Route::delete('{projectNote}', [ProjectNoteController::class, 'destroy'])->middleware('permission:delete-project-note');
    });
    Route::get('trash/project-note', [ProjectNoteController::class, 'trash'])->middleware('permission:read-trashed-project-note');
    Route::get('restore/project-note/{projectNote}', [ProjectNoteController::class, 'restore'])->withTrashed()->middleware('permission:restore-project-note');
    Route::delete('force-delete/project-note/{projectNote}', [ProjectNoteController::class, 'forceDelete'])->withTrashed()->middleware('permission:force-delete-project-note');
});
