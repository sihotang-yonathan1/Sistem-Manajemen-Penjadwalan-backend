<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Lecturer;
use App\Models\Course;
use App\Models\Room;
use App\Models\Schedule;
use App\Observers\LecturerObserver;
use App\Observers\CourseObserver;
use App\Observers\RoomObserver;
use App\Observers\ScheduleObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
      public function boot(): void
    {
        Lecturer::observe(LecturerObserver::class);
        Course::observe(CourseObserver::class);
        Room::observe(RoomObserver::class);
        Schedule::observe(ScheduleObserver::class);
    }
}
