<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    use HasFactory;
    
    protected $table = 'schedules';
    protected $fillable = [
        'lecturer_id',
        'course_id',
        'room_id',
        'day',
        'start_time',
        'end_time'
    ];

    // Relasi dengan dosen
    public function lecturer()
    {
        return $this->belongsTo(Lecturer::class);
    }

    // Relasi dengan mata kuliah
    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    // Relasi dengan ruangan
    public function room()
    {
        return $this->belongsTo(Room::class);
    }
}
