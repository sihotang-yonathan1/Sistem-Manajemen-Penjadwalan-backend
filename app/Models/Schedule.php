<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_id',
        'lecturer_id', 
        'room_id',
        'day',
        'start_time',
        'end_time',
        'semester',
        'academic_year'
    ];

    // Relationship with Course
    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id');
    }

    // Relationship with Lecturer  
    public function lecturer()
    {
        return $this->belongsTo(Lecturer::class, 'lecturer_id');
    }

    // Relationship with Room
    public function room()
    {
        return $this->belongsTo(Room::class, 'room_id');
    }

    // Scope for filtering by day
    public function scopeByDay($query, $day)
    {
        return $query->where('day', $day);
    }

    // Scope for filtering by semester
    public function scopeBySemester($query, $semester)
    {
        return $query->where('semester', $semester);
    }
}
