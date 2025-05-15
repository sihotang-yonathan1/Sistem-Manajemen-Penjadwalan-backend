<?php

use App\Http\Controllers\CourseController;
use App\Http\Controllers\LecturerController;
use App\Http\Controllers\LecturerCourseController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\GeneratedFileController;
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


// Lecturer routes
Route::get('/lecturer', [LecturerController::class, 'get_all_lecturers']);
Route::post('/lecturer', [LecturerController::class, 'create_lecturer']);
Route::get('/lecturer/{lecturer_id}', [LecturerController::class, 'get_lecturer_by_id']);
Route::patch('/lecturer/{lecturer_id}', [LecturerController::class, 'update_lecturer_by_id']);
Route::delete('/lecturer/{lecturer_id}', [LecturerController::class, 'delete_lecturer_by_id']);

// Lecturer-Course relationship routes
Route::post('/lecturer-course', [LecturerCourseController::class, 'assign_lecturer_to_course']);
Route::delete('/lecturer-course', [LecturerCourseController::class, 'remove_lecturer_from_course']);
Route::get('/course/{course_id}/lecturers', [LecturerCourseController::class, 'get_lecturers_for_course']);
Route::get('/lecturer/{lecturer_id}/courses', [LecturerCourseController::class, 'get_courses_for_lecturer']);


// Room routes
Route::get('/rooms', [RoomController::class, 'get_all_rooms']);
Route::post('/rooms', [RoomController::class, 'create_room']);
Route::get('/rooms/{room_id}', [RoomController::class, 'get_room_by_id']);
Route::patch('/rooms/{room_id}', [RoomController::class, 'update_room_by_id']);
Route::delete('/rooms/{room_id}', [RoomController::class, 'delete_room_by_id']);


// Schedule routes
Route::get('/schedules', [ScheduleController::class, 'get_all_schedules']);
Route::post('/schedules/generate', [ScheduleController::class, 'generate_schedules']);
Route::delete('/schedules/{schedule_id}', [ScheduleController::class, 'delete_schedule']);
Route::delete('/schedules', [ScheduleController::class, 'delete_all_schedules']);
Route::get('/schedules/day/{day}', [ScheduleController::class, 'filter_schedules_by_day']);
Route::get('/schedules/search', [ScheduleController::class, 'search_schedules']);
Route::get('/schedules/export', [ScheduleController::class, 'export_schedules_to_csv']);


// Generated Files routes
Route::get('/generated-files', [GeneratedFileController::class, 'getAllFiles']);
Route::get('/generated-files/{id}', [GeneratedFileController::class, 'getFileDetails']);
Route::get('/generated-files/{id}/download', [GeneratedFileController::class, 'downloadFile']);
Route::delete('/generated-files/{id}', [GeneratedFileController::class, 'deleteFile']);


