<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentEnrollment extends Model
{
    use HasFactory;

    protected $table = 'student_enrollments';
    
    protected $fillable = [
        'user_id',
        'course_id',
        'semester',
        'tahun_akademik',
        'status',
        'enrollment_date'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'enrollment_date' => 'datetime',
    ];

// Relationship with User (Student)
    public function student()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Relationship with Course
    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id');
    }

    // Scope for active enrollments
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    // Scope for specific student
    public function scopeForStudent($query, $userId)
    {
        return $query->where('user_id', $userId);
    }
}
