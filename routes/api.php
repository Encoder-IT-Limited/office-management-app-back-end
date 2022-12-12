<?php

use App\Http\Controllers\EmployeeNoteController;
use App\Http\Controllers\LeaveController;
use App\Http\Controllers\ProjectControler;
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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('users')->group(function () {
    Route::get('index', [UserController::class, 'index']);
    Route::post('store', [UserController::class, 'store']);
    Route::get('show/{id}', [UserController::class, 'show']);
    Route::patch('update', [UserController::class, 'update']);
    Route::delete('delete/{id}', [UserController::class, 'destroy']);
});

Route::prefix('skills')->group(function () {
    Route::get('index', [SkillController::class, 'index']);
    Route::post('store', [SkillController::class, 'store']);
    Route::get('show/{id}', [SkillController::class, 'show']);
    Route::patch('update', [SkillController::class, 'update']);
    Route::delete('delete/{id}', [SkillController::class, 'destroy']);
});

Route::prefix('leave-apply')->group(function () {
    Route::get('index', [LeaveController::class, 'index']);
    Route::post('store', [LeaveController::class, 'store']);
    Route::get('show/{id}', [LeaveController::class, 'show']);
    Route::patch('update', [LeaveController::class, 'update']);
    Route::delete('delete/{id}', [LeaveController::class, 'destroy']);
});

// Route::prefix('employee-notes')->group(function () {
//     Route::get('index', [EmployeeNoteController::class, 'index']);
//     Route::post('store', [EmployeeNoteController::class, 'store']);
//     Route::get('show/{id}', [EmployeeNoteController::class, 'show']);
//     Route::patch('update', [EmployeeNoteController::class, 'update']);
//     Route::delete('delete/{id}', [EmployeeNoteController::class, 'destroy']);
// });

Route::prefix('projects')->group(function () {
    Route::get('index', [ProjectControler::class, 'index']);
    Route::post('store', [ProjectControler::class, 'store']);
    Route::get('show/{id}', [ProjectControler::class, 'show']);
    Route::patch('update', [ProjectControler::class, 'update']);
    Route::delete('delete/{id}', [ProjectControler::class, 'destroy']);
});
