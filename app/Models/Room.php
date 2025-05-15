<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    use HasFactory;
    
    protected $table = 'rooms';
    protected $fillable = [
        "nama",
        "kapasitas"
    ];

    // Relasi dengan jadwal (jika diperlukan)
    public function schedules()
    {
        return $this->hasMany(Schedule::class);
    }
}
