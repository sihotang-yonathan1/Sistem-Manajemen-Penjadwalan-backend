<?php

namespace App\Observers;

use App\Models\Lecturer;
use App\Models\Notification;

class LecturerObserver
{
    /**
     * Handle the Lecturer "created" event.
     */
    public function created(Lecturer $lecturer): void
    {
        Notification::create([
            'title' => 'Dosen Baru Ditambahkan',
            'message' => "Dosen baru dengan nama {$lecturer->nama} telah ditambahkan ke sistem.",
            'type' => 'info',
            'related_model' => 'lecturer',
            'related_id' => $lecturer->id,
        ]);
    }

    /**
     * Handle the Lecturer "updated" event.
     */
    public function updated(Lecturer $lecturer): void
    {
        Notification::create([
            'title' => 'Data Dosen Diperbarui',
            'message' => "Data dosen {$lecturer->nama} telah diperbarui.",
            'type' => 'info',
            'related_model' => 'lecturer',
            'related_id' => $lecturer->id,
        ]);
    }

    /**
     * Handle the Lecturer "deleted" event.
     */
    public function deleted(Lecturer $lecturer): void
    {
        Notification::create([
            'title' => 'Dosen Dihapus',
            'message' => "Dosen dengan nama {$lecturer->nama} telah dihapus dari sistem.",
            'type' => 'peringatan',
            'related_model' => 'lecturer',
            'related_id' => $lecturer->id,
        ]);
    }
}
