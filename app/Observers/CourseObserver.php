<?php

namespace App\Observers;

use App\Models\Course;
use App\Models\Notification;

class CourseObserver
{
    /**
     * Handle the Course "created" event.
     */
    public function created(Course $course): void
    {
        Notification::create([
            'title' => 'Mata Kuliah Baru',
            'message' => "Mata kuliah baru {$course->nama} telah ditambahkan ke sistem.",
            'type' => 'info',
            'related_model' => 'course',
            'related_id' => $course->id,
        ]);
    }

    /**
     * Handle the Course "updated" event.
     */
    public function updated(Course $course): void
    {
        Notification::create([
            'title' => 'Mata Kuliah Diperbarui',
            'message' => "Data mata kuliah {$course->nama} telah diperbarui.",
            'type' => 'info',
            'related_model' => 'course',
            'related_id' => $course->id,
        ]);
    }

    /**
     * Handle the Course "deleted" event.
     */
    public function deleted(Course $course): void
    {
        Notification::create([
            'title' => 'Mata Kuliah Dihapus',
            'message' => "Mata kuliah {$course->nama} telah dihapus dari sistem.",
            'type' => 'peringatan',
            'related_model' => 'course',
            'related_id' => $course->id,
        ]);
    }
}
