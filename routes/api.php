<?php

use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\EmployeeNoteController;
use App\Http\Controllers\LeaveController;
use App\Http\Controllers\ProjectControler;
use App\Http\Controllers\ReminderController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SkillController;
use App\Http\Controllers\UserController;
use App\Models\Leave;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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
        Route::get('index', [RoleController::class, 'index']);
        Route::post('store', [RoleController::class, 'store']);
        Route::get('show/{id}', [RoleController::class, 'show']);
        Route::patch('update', [RoleController::class, 'update']);
        Route::delete('delete/{id}', [RoleController::class, 'destroy']);
        Route::get('all-permissions', [RoleController::class, 'allPermissions']);
        Route::post('permission-store', [RoleController::class, 'permissionStore']);
    });

    Route::prefix('users')->group(function () {
        Route::get('index', [UserController::class, 'index'])->middleware('permission:can-user-index');
        Route::post('store', [UserController::class, 'store'])->middleware('permission:can-user-store');
        Route::get('show/{id}', [UserController::class, 'show'])->middleware('permission:can-user-show');
        Route::post('update', [UserController::class, 'update'])->middleware('permission:can-user-update');
        Route::delete('delete/{id}', [UserController::class, 'destroy'])->middleware('permission:can-user-delete');
        Route::delete('force-delete/{id}', [UserController::class, 'forceDestroy'])->middleware('permission:can-user-delete');
        Route::post('restore', [UserController::class, 'restore']);
        Route::get('details', [UserController::class, 'details']);
    });

    Route::prefix('skills')->group(function () {
        Route::get('index', [SkillController::class, 'index'])->middleware('permission:can-skill-index');
        Route::post('store', [SkillController::class, 'store'])->middleware('permission:can-skill-store');
        Route::get('show/{id}', [SkillController::class, 'show'])->middleware('permission:can-skill-show');
        Route::patch('update', [SkillController::class, 'update'])->middleware('permission:can-skill-update');
        Route::delete('delete/{id}', [SkillController::class, 'destroy'])->middleware('permission:can-skill-delete');
    });

    Route::prefix('leave')->group(function () {
        Route::get('index', [LeaveController::class, 'index'])->middleware('permission:can-leave-index');
        Route::post('store', [LeaveController::class, 'store'])->middleware('permission:can-leave-store');
        Route::get('show/{id}', [LeaveController::class, 'show'])->middleware('permission:can-leave-show');
        Route::patch('update', [LeaveController::class, 'update'])->middleware('permission:can-leave-update');
        Route::delete('delete/{id}', [LeaveController::class, 'destroy'])->middleware('permission:can-leave-delete');
        Route::post('message', [LeaveController::class, 'leaveStatus'])->middleware('permission:can-leave-status');
    });

    Route::prefix('employee-notes')->group(function () {
        Route::get('index', [EmployeeNoteController::class, 'index']);
        Route::post('store', [EmployeeNoteController::class, 'store']);
        Route::get('show/{id}', [EmployeeNoteController::class, 'show']);
        Route::patch('update', [EmployeeNoteController::class, 'update']);
        Route::delete('delete/{id}', [EmployeeNoteController::class, 'destroy']);
    });

    Route::prefix('projects')->group(function () {
        Route::get('index', [ProjectControler::class, 'index'])->middleware('permission:can-project-index');
        Route::post('store', [ProjectControler::class, 'store'])->middleware('permission:can-project-update');
        Route::get('show/{id}', [ProjectControler::class, 'show'])->middleware('permission:can-project-show');
        Route::patch('update', [ProjectControler::class, 'update'])->middleware('permission:can-project-update');
        Route::delete('delete/{id}', [ProjectControler::class, 'destroy'])->middleware('permission:can-project-delete');
        Route::post('message', [ProjectControler::class, 'projectstatus']);
        Route::get('status', [ProjectControler::class, 'getStatus']);
    });

    Route::prefix('reminders')->group(function () {
        Route::get('index', [ReminderController::class, 'index']);
        Route::post('store', [ReminderController::class, 'store']);
        Route::get('show/{id}', [ReminderController::class, 'show']);
        Route::patch('update', [ReminderController::class, 'update']);
        Route::delete('delete/{id}', [ReminderController::class, 'destroy']);
    });

    Route::prefix('calendar')->group(function () {
        Route::get('developer/{id}', [CalendarController::class, 'developerCalendar']);
        Route::get('project/{id}', [CalendarController::class, 'projectCalendar']);
        Route::get('calender_view', [CalendarController::class, 'calenderView']);
    });

    Route::prefix('dashboard')->group(function () {
        Route::get('index', [CalendarController::class, 'developerCalendar'])->middleware('can-dashboard-index');
    });

    Route::prefix('breaks')->group(function () {
        Route::get('/', [AttendanceController::class, 'getEmployeeBreaks']);
        Route::get('details', [AttendanceController::class, 'getEmployeeBreakDetails'])->middleware('permission:read-breaks');
        Route::post('start', [AttendanceController::class, 'startingBreak']);
        Route::get('end', [AttendanceController::class, 'endingBreak']);
    });

    Route::prefix('attendances')->group(function () {
        Route::get('/', [AttendanceController::class, 'getEmployeeAttendances']);
        Route::get('check-in', [AttendanceController::class, 'checkIn']);
        Route::get('check-out', [AttendanceController::class, 'checkOut']);
        Route::get('delays', [AttendanceController::class, 'getEmployeeDelays'])->middleware('permission:read-delays');

        Route::post('create', [AttendanceController::class, 'createAttendance'])->middleware('permission:can-attendance-update');
    });
});
