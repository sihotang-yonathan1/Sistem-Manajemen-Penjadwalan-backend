<?php

use App\Http\Controllers\CourseController;
use App\Http\Controllers\RoomController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


// Course
Route::get('/course', [CourseController::class, 'get_all_course']);
Route::post('/course', [CourseController::class, 'create_course']);

// Course by id
Route::get('/course/{course_id}', [CourseController::class, 'get_course_by_id']);
Route::delete('/course/{course_id}', [CourseController::class, 'delete_course_by_id']);
Route::patch('/course/{course_id}', [CourseController::class, 'update_course_by_id']);

// Room
Route::get('/room', [RoomController::class, 'get_all_rooms']);
Route::post('/room', [RoomController::class, 'create_room']);

// Room by is
Route::patch('/room/{room_id}', [RoomController::class, 'update_room_by_id']);
Route::delete('/room/{room_id}', [RoomController::class, 'delete_room_by_id']);

