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
}
