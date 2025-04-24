<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Lecturer;
use App\Models\LecturerCourse;
use Illuminate\Http\Request;

class LecturerCourseController extends Controller
{
    // Assign lecturer to course
    public function assign_lecturer_to_course(Request $request)
    {
        $request->validate([
            'lecturer_id' => 'required|exists:lecturers,id',
            'course_id' => 'required|exists:course,id',
        ]);

        $assignment = LecturerCourse::firstOrCreate([
            'lecturer_id' => $request->lecturer_id,
            'course_id' => $request->course_id,
        ]);

        return response()->json([
            'message' => 'Lecturer assigned to course successfully',
            'data' => $assignment
        ]);
    }

    // Remove lecturer from course
    public function remove_lecturer_from_course(Request $request)
    {
        $request->validate([
            'lecturer_id' => 'required|exists:lecturers,id',
            'course_id' => 'required|exists:course,id',
        ]);

        $deleted = LecturerCourse::where([
            'lecturer_id' => $request->lecturer_id,
            'course_id' => $request->course_id,
        ])->delete();

        return response()->json([
            'message' => $deleted ? 'Assignment removed successfully' : 'Assignment not found',
        ]);
    }

     // Get all lecturers for a course
     public function get_lecturers_for_course($course_id)
     {
         $course = Course::findOrFail($course_id);
         $lecturers = $course->lecturers;
         
         // Transform data to match frontend structure
         $transformedLecturers = $lecturers->map(function($lecturer) {
             return [
                 'id' => (string)$lecturer->id,
                 'nama' => $lecturer->nama,
                 'nip' => $lecturer->nip,
             ];
         });
         
         return response()->json($transformedLecturers);
     }
 
     // Get all courses for a lecturer
     public function get_courses_for_lecturer($lecturer_id)
     {
         $lecturer = Lecturer::findOrFail($lecturer_id);
         $courses = $lecturer->courses;
         
         // Transform data to match frontend structure
         $transformedCourses = $courses->map(function($course) {
             return [
                 'id' => (string)$course->id,
                 'kode' => $course->kode,
                 'nama' => $course->nama,
                 'semester' => (string)$course->semester,
                 'sks' => (string)$course->sks,
             ];
         });
         
         return response()->json($transformedCourses);
     }
 }
 
