<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'message',
        'type',
        'read',
        'related_model',
        'related_id',
        'time',
        'date'
    ];

    protected $casts = [
        'read' => 'boolean',
        'date' => 'date',
    ];

    // Mengupdate field 'time' secara otomatis berdasarkan created_at
    public static function boot()
    {
        parent::boot();

        static::creating(function ($notification) {
            $notification->date = $notification->date ?? Carbon::now()->toDateString();
            $notification->time = 'Baru saja';
        });

        static::created(function ($notification) {
            // Update time field untuk semua notifikasi
            self::updateTimeFields();
        });
    }

    // Method untuk mengupdate semua time field
    public static function updateTimeFields()
    {
        $notifications = self::all();
        foreach ($notifications as $notification) {
            $notification->time = $notification->getTimeAgo();
            $notification->save();
        }
    }

    // Method untuk mendapatkan waktu dalam format "x waktu yang lalu"
    public function getTimeAgo()
    {
        $now = Carbon::now();
        $created = Carbon::parse($this->created_at);
        $diff = $created->diffInSeconds($now);

        if ($diff < 60) {
            return 'Baru saja';
        } elseif ($diff < 3600) {
            $minutes = floor($diff / 60);
            return $minutes . ' menit yang lalu';
        } elseif ($diff < 86400) {
            $hours = floor($diff / 3600);
            return $hours . ' jam yang lalu';
        } elseif ($diff < 604800) {
            $days = floor($diff / 86400);
            return $days . ' hari yang lalu';
        } else {
            $weeks = floor($diff / 604800);
            return $weeks . ' minggu yang lalu';
        }
    }
}
