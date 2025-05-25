<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasFactory;

    protected $table = 'course';

    protected $fillable = [
        'kode',
        'nama',
        'semester',
        'sks'
    ];

    // Relasi many-to-many dengan Lecturer
    public function lecturers()
    {
        return $this->belongsToMany(Lecturer::class, 'lecturer_course', 'course_id', 'lecturer_id');
    }

    // Relasi untuk enrollment mahasiswa
    public function enrollments()
    {
        return $this->hasMany(StudentEnrollment::class);
    }

    // Relasi untuk mendapatkan mahasiswa yang mengambil mata kuliah ini
    public function students()
    {
        return $this->belongsToMany(User::class, 'student_enrollments')
                    ->withPivot('semester', 'tahun_akademik', 'status')
                    ->withTimestamps();
    }
}
