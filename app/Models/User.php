<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'username',
        'password',
        'role'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // Relasi untuk enrollment mahasiswa
    public function enrollments()
    {
        return $this->hasMany(StudentEnrollment::class);
    }

    // Relasi untuk mendapatkan mata kuliah yang diambil mahasiswa
    public function courses()
    {
        return $this->belongsToMany(Course::class, 'student_enrollments')
                    ->withPivot('semester', 'tahun_akademik', 'status')
                    ->withTimestamps();
    }
}
