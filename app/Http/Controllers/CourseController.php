<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    // Get all courses
    public function get_all_course(Request $request){
        $semesterFilter = $request->query("semester", null);
        $courses = Course::all();
        
        // Transform data to match frontend structure
        $transformedCourses = $courses->map(function($course) {
            return [
                'id' => (string)$course->id,
                'kode' => (string)$course->kode,
                'nama' => (string)$course->nama,
                'semester' => (string)$course->semester,
                'sks' => (string)$course->sks,
            ];
        });
        
        return response()->json($transformedCourses);
    }

    // Create new course
    public function create_course(Request $request){
        $course = new Course();

        $course->nama = $request->nama;
        $course->kode = $request->kode; // Add this column to your courses table
        $course->semester = $request->semester;
        $course->sks = $request->sks;

        $course->save();
        
        // Return in the format expected by frontend
        return response()->json([
            'id' => (string)$course->id,
            'kode' => (string)$course->kode,
            'nama' => (string)$course->nama,
            'semester' => (string)$course->semester,
            'sks' => (string)$course->sks,
        ]);
    }

    // Delete course by ID
    public function delete_course_by_id(int $course_id){
        Course::where("id", "=", $course_id)->delete();
        return response()->json([
            "message" => "success"
        ]);
    }

    // Update course by ID
    public function update_course_by_id(Request $request, int $course_id){
        // Select the course
        $selected_course = Course::find($course_id);
        
        if (!$selected_course) {
            return response()->json(['message' => 'Course not found'], 404);
        }
        
        // Update fields
        $selected_course->code = $request->kode ?? $selected_course->code;
        $selected_course->name = $request->nama ?? $selected_course->name;
        $selected_course->semester = $request->semester ?? $selected_course->semester;
        $selected_course->credit = $request->sks ?? $selected_course->credit;

        $selected_course->save();

        // Return in the format expected by frontend
        return response()->json([
            'id' => (string)$selected_course->id,
            'kode' => (string)$selected_course->kode,
            'nama' => (string)$selected_course->nama,
            'semester' => (string)$selected_course->semester,
            'sks' => (string)$selected_course->sks,
        ]);
    }

    // Get course by ID
    public function get_course_by_id(int $course_id){
        $selected_course = Course::find($course_id);
        
        if (!$selected_course) {
            return response()->json(['message' => 'Course not found'], 404);
        }
        
        // Return in the format expected by frontend
        return response()->json([
            'id' => (string)$selected_course->id,
            'kode' => (string)$selected_course->kode ?? '',
            'nama' => (string)$selected_course->nama,
            'semester' => (string)$selected_course->semester,
            'sks' => (string)$selected_course->sks,
        ]);
    }
}
