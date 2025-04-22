<?php

namespace App\Http\Controllers;

use App\Models\Room;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    public function get_all_rooms(){
        $rooms = Room::all();
        return response()->json($rooms);
    }

    public function create_room(Request $request){
        $room = new Room();
        $room->name = $request->name;
        $room->capacity = $request->capacity;
        $room->location = $request->location;
        $room->status = $request->status ?? 'available';

        $room->save();
        // TODO: set the message
        return response()->json($room);
    }

    public function update_room_by_id(Request $request, int $course_id){
        $selected_room = Room::find($course_id);

        $selected_room->name = $request->name ?? $selected_room->name;
        $selected_room->capacity = $request->capacity ?? $selected_room->capacity ?? 0;
        $selected_room->location = $request->location ?? $selected_room->location;
        $selected_room->status = $request->status ?? $selected_room->status ?? 'available';

        $selected_room->save();
        return response()->json($selected_room);
    }
}
