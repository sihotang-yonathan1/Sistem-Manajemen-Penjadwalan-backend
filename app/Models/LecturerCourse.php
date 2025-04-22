<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LecturerCourse extends Model
{
    use HasFactory;
    
    protected $table = 'lecturer_course';
    protected $fillable = [
        'lecturer_id',
        'course_id'
    ];
}