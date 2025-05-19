<?php

namespace App\Observers;

use App\Models\Schedule;
use App\Models\Notification;

class ScheduleObserver
{


    /**
     * Handle the Schedule "updated" event.
     */
    public function updated(Schedule $schedule): void
    {
        // Ambil data terkait
        $course = $schedule->course;
        $lecturer = $schedule->lecturer;
        $room = $schedule->room;
        
        Notification::create([
            'title' => 'Perubahan Jadwal',
            'message' => "Jadwal untuk mata kuliah {$course->nama} dengan dosen {$lecturer->nama} telah diubah menjadi hari {$schedule->day} pukul {$schedule->start_time}-{$schedule->end_time} di ruangan {$room->nama}.",
            'type' => 'jadwal',
            'related_model' => 'schedule',
            'related_id' => $schedule->id,
        ]);
    }

    /**
     * Handle the Schedule "deleted" event.
     */
    public function deleted(Schedule $schedule): void
    {
        // Ambil data terkait
        $course = $schedule->course;
        
        Notification::create([
            'title' => 'Jadwal Dihapus',
            'message' => "Jadwal untuk mata kuliah {$course->nama} telah dihapus dari sistem.",
            'type' => 'peringatan',
            'related_model' => 'schedule',
            'related_id' => $schedule->id,
        ]);
    }
}
