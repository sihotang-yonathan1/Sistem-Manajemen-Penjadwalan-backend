<?php

namespace App\Http\Controllers;

use App\Models\Room;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    // Get all rooms
    public function get_all_rooms(Request $request){
        $rooms = Room::all();
        
        // Transform data to match frontend structure
        $transformedRooms = $rooms->map(function($room) {
            return [
                'id' => (string)$room->id,
                'nama' => $room->nama,
                'kapasitas' => (string)$room->kapasitas,
            ];
        });
        
        return response()->json($transformedRooms);
    }

    // Create new room
    public function create_room(Request $request){
        $room = new Room();

        $room->nama = $request->nama;
        $room->kapasitas = $request->kapasitas;

        $room->save();
        
        // Return in the format expected by frontend
        return response()->json([
            'id' => (string)$room->id,
            'nama' => $room->nama,
            'kapasitas' => (string)$room->kapasitas,
        ]);
    }

    // Delete room by ID
    public function delete_room_by_id(int $room_id){
        Room::where("id", "=", $room_id)->delete();
        return response()->json([
            "message" => "success"
        ]);
    }

    // Update room by ID
    public function update_room_by_id(Request $request, int $room_id){
        // Select the room
        $selected_room = Room::find($room_id);
        
        if (!$selected_room) {
            return response()->json(['message' => 'Room not found'], 404);
        }
        
        // Update fields
        $selected_room->nama = $request->nama ?? $selected_room->nama;
        $selected_room->kapasitas = $request->kapasitas ?? $selected_room->kapasitas;

        $selected_room->save();

        // Return in the format expected by frontend
        return response()->json([
            'id' => (string)$selected_room->id,
            'nama' => $selected_room->nama,
            'kapasitas' => (string)$selected_room->kapasitas,
        ]);
    }

    // Get room by ID
    public function get_room_by_id(int $room_id){
        $selected_room = Room::find($room_id);
        
        if (!$selected_room) {
            return response()->json(['message' => 'Room not found'], 404);
        }
        
        // Return in the format expected by frontend
        return response()->json([
            'id' => (string)$selected_room->id,
            'nama' => $selected_room->nama,
            'kapasitas' => (string)$selected_room->kapasitas,
        ]);
    }
}
