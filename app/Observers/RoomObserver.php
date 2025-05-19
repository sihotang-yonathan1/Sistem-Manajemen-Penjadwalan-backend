<?php

namespace App\Observers;

use App\Models\Room;
use App\Models\Notification;

class RoomObserver
{
    /**
     * Handle the Room "created" event.
     */
    public function created(Room $room): void
    {
        Notification::create([
            'title' => 'Ruangan Baru Ditambahkan',
            'message' => "Ruangan baru {$room->nama} dengan kapasitas {$room->kapasitas} orang telah ditambahkan ke sistem.",
            'type' => 'info',
            'related_model' => 'room',
            'related_id' => $room->id,
        ]);
    }

    /**
     * Handle the Room "updated" event.
     */
    public function updated(Room $room): void
    {
        Notification::create([
            'title' => 'Data Ruangan Diperbarui',
            'message' => "Data ruangan {$room->nama} telah diperbarui.",
            'type' => 'info',
            'related_model' => 'room',
            'related_id' => $room->id,
        ]);
    }

    /**
     * Handle the Room "deleted" event.
     */
    public function deleted(Room $room): void
    {
        Notification::create([
            'title' => 'Ruangan Dihapus',
            'message' => "Ruangan {$room->nama} telah dihapus dari sistem.",
            'type' => 'peringatan',
            'related_model' => 'room',
            'related_id' => $room->id,
        ]);
    }
}
