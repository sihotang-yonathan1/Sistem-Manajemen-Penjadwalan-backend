<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasFactory;
    //
    protected $table = 'course';
    protected $fillable = [
        "kode",
        "nama",
        "semester",
        "sks"
    ];
    public function lecturers()
    {
        return $this->belongsToMany(Lecturer::class, 'lecturer_course');
    }
}
