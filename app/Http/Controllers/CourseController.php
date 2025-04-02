<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    //
    public function get_all_course(Request $request){
        $semesterFilter = $request->query("semester", null);
        $course = Course::all();
        return response()->json($course);
    }

    public function create_course(Request $request){
        $course = new Course();
        $course->name = $request->name;
        $course->description = $request->description;

        $course->save();
        return response()->json($course);
    }

    public function delete_course_by_id(int $course_id){
        $selected_course = Course::where("id", "=", $course_id)->delete();
        return response()->json(Course::all());
    }

    public function update_course_by_id(Request $request, int $course_id){
        // Select the course
        $selected_course = Course::find($course_id);
        
        // Update field
        $selected_course->name = $request->name ?? $selected_course->name;
        $selected_course->description = $request->description ?? $selected_course->description;
        $selected_course->semester = $request->semester ?? $selected_course->semester;
        $selected_course->credit = $request->credit ?? $selected_course->credit;

        $selected_course->save();

        return response()->json($selected_course);
    }

    public function get_course_by_id(int $course_id){
        $selected_course = Course::where("id", "=", $course_id);
        return response()->json($selected_course->get());
    }
}
