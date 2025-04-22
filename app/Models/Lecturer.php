<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lecturer extends Model
{
    use HasFactory;
    
    protected $table = 'lecturers';
    protected $fillable = [
        "nama",
        "nip"
    ];

    // Tambahkan method ini di dalam class Lecturer
public function courses()
{
    return $this->belongsToMany(Course::class, 'lecturer_course');
}

}