<?php

namespace App\Http\Controllers;

use App\Models\Lecturer;
use Illuminate\Http\Request;

class LecturerController extends Controller
{
    // Get all lecturers
    public function get_all_lecturers(Request $request){
        $lecturers = Lecturer::all();
        
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

    // Create new lecturer
    public function create_lecturer(Request $request){
        $lecturer = new Lecturer();

        $lecturer->nama = $request->nama;
        $lecturer->nip = $request->nip;

        $lecturer->save();
        
        // Return in the format expected by frontend
        return response()->json([
            'id' => (string)$lecturer->id,
            'nama' => $lecturer->nama,
            'nip' => $lecturer->nip,
        ]);
    }

    // Delete lecturer by ID
    public function delete_lecturer_by_id(int $lecturer_id){
        Lecturer::where("id", "=", $lecturer_id)->delete();
        return response()->json([
            "message" => "success"
        ]);
    }

    // Update lecturer by ID
    public function update_lecturer_by_id(Request $request, int $lecturer_id){
        // Select the lecturer
        $selected_lecturer = Lecturer::find($lecturer_id);
        
        if (!$selected_lecturer) {
            return response()->json(['message' => 'Lecturer not found'], 404);
        }
        
        // Update fields
        $selected_lecturer->nama = $request->nama ?? $selected_lecturer->nama;
        $selected_lecturer->nip = $request->nip ?? $selected_lecturer->nip;

        $selected_lecturer->save();

        // Return in the format expected by frontend
        return response()->json([
            'id' => (string)$selected_lecturer->id,
            'nama' => $selected_lecturer->nama,
            'nip' => $selected_lecturer->nip,
        ]);
    }

    // Get lecturer by ID
    public function get_lecturer_by_id(int $lecturer_id){
        $selected_lecturer = Lecturer::find($lecturer_id);
        
        if (!$selected_lecturer) {
            return response()->json(['message' => 'Lecturer not found'], 404);
        }
        
        // Return in the format expected by frontend
        return response()->json([
            'id' => (string)$selected_lecturer->id,
            'nama' => $selected_lecturer->nama,
            'nip' => $selected_lecturer->nip,
        ]);
    }
}