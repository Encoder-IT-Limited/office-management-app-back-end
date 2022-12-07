<?php

use App\Http\Controllers\LeaveManageController;
use App\Http\Controllers\SkillController;
use App\Http\Controllers\UserController;
use App\Models\LeaveManagement;
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
    Route::post('update', [UserController::class, 'update']);
    Route::delete('delete/{id}', [UserController::class, 'destroy']);
});

Route::prefix('skills')->group(function () {
    Route::get('index', [SkillController::class, 'index']);
    Route::post('store', [SkillController::class, 'store']);
    Route::get('show/{id}', [SkillController::class, 'show']);
    Route::post('update', [SkillController::class, 'update']);
    Route::delete('delete/{id}', [SkillController::class, 'destroy']);
});

Route::prefix('leave-apply')->group(function () {
    Route::get('index', [LeaveManageController::class, 'index']);
    Route::post('store', [LeaveManageController::class, 'store']);
    Route::get('show/{id}', [LeaveManageController::class, 'show']);
    Route::post('update', [LeaveManageController::class, 'update']);
    Route::delete('delete/{id}', [LeaveManageController::class, 'destroy']);
});
