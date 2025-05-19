<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Carbon\Carbon;

class NotificationController extends Controller
{
    // Get all notifications
    public function getAllNotifications()
    {
        $notifications = Notification::orderBy('created_at', 'desc')->get();
        
        return response()->json($this->transformNotifications($notifications));
    }
    
    // Get unread notifications
    public function getUnreadNotifications()
    {
        $notifications = Notification::where('read', false)
            ->orderBy('created_at', 'desc')
            ->get();
        
        return response()->json($this->transformNotifications($notifications));
    }
    
    // Mark notification as read
    public function markAsRead($id)
    {
        $notification = Notification::findOrFail($id);
        $notification->read = true;
        $notification->save();
        
        return response()->json([
            'message' => 'Notification marked as read',
            'notification' => $this->transformNotification($notification)
        ]);
    }
    
    // Mark all notifications as read
    public function markAllAsRead()
    {
        Notification::where('read', false)->update(['read' => true]);
        
        return response()->json([
            'message' => 'All notifications marked as read'
        ]);
    }
    
    // Delete notification
    public function deleteNotification($id)
    {
        $notification = Notification::findOrFail($id);
        $notification->delete();
        
        return response()->json([
            'message' => 'Notification deleted successfully'
        ]);
    }
    
    // Delete all notifications
    public function deleteAllNotifications()
    {
        Notification::truncate();
        
        return response()->json([
            'message' => 'All notifications deleted successfully'
        ]);
    }
    
    // Create a notification for schedule generation
    public function createScheduleNotification()
    {
        $notification = new Notification();
        $notification->title = 'Jadwal Baru Telah Digenerate';
        $notification->message = 'Silahkan download file atau lihat perincian di halaman Generate Jadwal';
        $notification->type = 'jadwal';
        $notification->read = false;
        $notification->related_model = 'schedule';
        $notification->time = $this->getTimeAgo(Carbon::now());
        $notification->date = Carbon::now()->toDateString();
        $notification->save();
        
        return response()->json($this->transformNotification($notification));
    }
    
    // Helper method to transform notifications for frontend
    private function transformNotifications($notifications)
    {
        return $notifications->map(function($notification) {
            return $this->transformNotification($notification);
        });
    }
    
    // Helper method to transform a single notification
    private function transformNotification($notification)
    {
        // Update the time ago string
        if ($notification->created_at) {
            $notification->time = $this->getTimeAgo($notification->created_at);
        }
        
        return [
            'id' => (string)$notification->id,
            'title' => $notification->title,
            'message' => $notification->message,
            'type' => $notification->type,
            'read' => (bool)$notification->read,
            'time' => $notification->time,
            'date' => $notification->date,
            'related_model' => $notification->related_model,
            'related_id' => $notification->related_id ? (string)$notification->related_id : null,
        ];
    }
    
    // Helper method to get time ago string
    private function getTimeAgo($dateTime)
    {
        $now = Carbon::now();
        $diff = $now->diffInSeconds($dateTime);
        
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
            return $dateTime->format('d M Y');
        }
    }
}
