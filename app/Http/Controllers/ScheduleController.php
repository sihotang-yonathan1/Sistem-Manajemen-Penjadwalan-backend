<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Lecturer;
use App\Models\Room;
use App\Models\Schedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ScheduleController extends Controller
{
    // Daftar hari dan slot waktu yang tersedia
    private $days = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];
    private $timeSlots = [
        ['08:00', '09:40'],
        ['09:50', '11:30'],
        ['13:00', '14:40'],
        ['14:50', '16:30']
    ];

    // Get all schedules
    public function get_all_schedules()
    {
        $schedules = Schedule::with(['lecturer', 'course', 'room'])->get();
        
        // Transform data to match frontend structure
        $transformedSchedules = $schedules->map(function($schedule) {
            return [
                'id' => (string)$schedule->id,
                'namaDosen' => $schedule->lecturer->nama,
                'mataKuliah' => $schedule->course->nama,
                'semester' => (string)$schedule->course->semester,
                'ruangan' => $schedule->room->nama,
                'waktu' => $schedule->start_time . ' - ' . $schedule->end_time,
                'hari' => $schedule->day,
            ];
        });
        
        return response()->json($transformedSchedules);
    }

   public function generate_schedules(Request $request)
{
    try {
        \Log::info('Starting improved schedule generation with better distribution');
        
        // Clear existing schedules if requested
        if ($request->input('clear_existing', true)) {
            Schedule::truncate();
            \Log::info('Cleared existing schedules');
        }
        
        // Get all necessary data
        $courses = Course::orderBy('semester', 'asc')->get();
        $lecturers = Lecturer::all();
        $rooms = Room::all();
        
        \Log::info('Data counts: Courses: ' . $courses->count() . ', Lecturers: ' . $lecturers->count() . ', Rooms: ' . $rooms->count());
        
        if ($courses->isEmpty() || $lecturers->isEmpty() || $rooms->isEmpty()) {
            return response()->json([
                'message' => 'Missing required data (courses, lecturers, or rooms)',
            ], 400);
        }
        
        $days = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];
        $timeSlots = [
            ['08:00', '09:40'],
            ['09:50', '11:30'],
            ['13:00', '14:40'],
            ['14:50', '16:30']
        ];
        
        $generatedSchedules = [];
        $usedSlots = []; // Track used day-time-room combinations
        $usedLecturerSlots = []; // Track used day-time-lecturer combinations
        $courseGroups = []; // Group courses by name
        $dayDistribution = array_fill_keys($days, 0); // Track schedules per day
        
        // Group courses by name
        foreach ($courses as $course) {
            $courseGroups[$course->nama][] = $course;
        }
        
        // Sort course groups by semester of the first course in each group
        uasort($courseGroups, function($a, $b) {
            return $a[0]->semester - $b[0]->semester;
        });
        
        // Process each course group
        foreach ($courseGroups as $courseName => $coursesInGroup) {
            \Log::info('Processing course group: ' . $courseName . ' with ' . count($coursesInGroup) . ' sections');
            
            // Sort courses within group by semester
            usort($coursesInGroup, function($a, $b) {
                return $a->semester - $b->semester;
            });
            
            // Find the day with the least schedules for this group
            $minDay = array_keys($dayDistribution, min($dayDistribution))[0];
            
            // Try to assign all courses in this group to the same time slot if possible
            $groupTimeSlot = null;
            $groupDay = null;
            
            // For each course in the group
            foreach ($coursesInGroup as $course) {
                \Log::info('Processing course: ' . $course->nama . ' (Semester ' . $course->semester . ')');
                
                // Get lecturers for this course
                $courseId = $course->id;
                $lecturersForCourse = DB::table('lecturer_course')
                    ->where('course_id', $courseId)
                    ->join('lecturers', 'lecturers.id', '=', 'lecturer_course.lecturer_id')
                    ->select('lecturers.*')
                    ->get();
                
                \Log::info('Lecturers for course ' . $course->nama . ': ' . $lecturersForCourse->count());
                
                // If no lecturers assigned, use the first lecturer as fallback
                if ($lecturersForCourse->isEmpty()) {
                    $lecturersForCourse = collect([$lecturers->first()]);
                    \Log::info('No lecturers assigned to course, using first lecturer as fallback');
                }
                
                // For each lecturer teaching this course
                foreach ($lecturersForCourse as $lecturer) {
                    \Log::info('Processing lecturer: ' . $lecturer->nama . ' for course: ' . $course->nama);
                    
                    $scheduleCreated = false;
                    
                    // If this is the first course in the group, find a suitable time slot
                    if ($groupTimeSlot === null) {
                        // Try to find an available slot, prioritizing the day with least schedules
                        $orderedDays = $days;
                        // Move the day with least schedules to the front
                        $minDayIndex = array_search($minDay, $orderedDays);
                        if ($minDayIndex !== false) {
                            array_splice($orderedDays, $minDayIndex, 1);
                            array_unshift($orderedDays, $minDay);
                        }
                        
                        foreach ($orderedDays as $day) {
                            if ($scheduleCreated) break;
                            
                            foreach ($timeSlots as $timeSlot) {
                                if ($scheduleCreated) break;
                                
                                // Check if lecturer is available at this time
                                $lecturerSlotKey = $day . '_' . $timeSlot[0] . '_' . $lecturer->id;
                                if (isset($usedLecturerSlots[$lecturerSlotKey])) {
                                    continue; // Lecturer already has a class at this time
                                }
                                
                                // Find an available room
                                foreach ($rooms as $room) {
                                    // Check if this slot is already used
                                    $slotKey = $day . '_' . $timeSlot[0] . '_' . $room->id;
                                    
                                    if (!isset($usedSlots[$slotKey])) {
                                        \Log::info('Found available slot for group: ' . $slotKey);
                                        
                                        // Mark this slot as used
                                        $usedSlots[$slotKey] = true;
                                        $usedLecturerSlots[$lecturerSlotKey] = true;
                                        
                                        // Remember this slot for the group
                                        $groupTimeSlot = $timeSlot;
                                        $groupDay = $day;
                                        
                                        try {
                                            // Create schedule
                                            $schedule = new Schedule();
                                            $schedule->lecturer_id = $lecturer->id;
                                            $schedule->course_id = $course->id;
                                            $schedule->room_id = $room->id;
                                            $schedule->day = $day;
                                            $schedule->start_time = $timeSlot[0];
                                            $schedule->end_time = $timeSlot[1];
                                            $schedule->save();
                                            
                                            \Log::info('Created schedule ID: ' . $schedule->id);
                                            
                                            // Increment day distribution counter
                                            $dayDistribution[$day]++;
                                            
                                            // Add to result
                                            $generatedSchedules[] = [
                                                'id' => (string)$schedule->id,
                                                'namaDosen' => $lecturer->nama,
                                                'mataKuliah' => $course->nama,
                                                'semester' => (string)$course->semester,
                                                'ruangan' => $room->nama,
                                                'waktu' => $timeSlot[0] . ' - ' . $timeSlot[1],
                                                'hari' => $day,
                                            ];
                                            
                                            $scheduleCreated = true;
                                            break;
                                        } catch (\Exception $e) {
                                            \Log::error('Error creating schedule: ' . $e->getMessage());
                                            // Continue to next slot
                                        }
                                    }
                                }
                            }
                        }
                    } else {
                        // For subsequent courses in the group, try to use the same time slot
                        // but check if the lecturer and room are available
                        foreach ($rooms as $room) {
                            // Check if this slot is already used
                            $slotKey = $groupDay . '_' . $groupTimeSlot[0] . '_' . $room->id;
                            $lecturerSlotKey = $groupDay . '_' . $groupTimeSlot[0] . '_' . $lecturer->id;
                            
                            if (!isset($usedSlots[$slotKey]) && !isset($usedLecturerSlots[$lecturerSlotKey])) {
                                \Log::info('Using group slot for course: ' . $slotKey);
                                
                                // Mark this slot as used
                                $usedSlots[$slotKey] = true;
                                $usedLecturerSlots[$lecturerSlotKey] = true;
                                
                                try {
                                    // Create schedule
                                    $schedule = new Schedule();
                                    $schedule->lecturer_id = $lecturer->id;
                                    $schedule->course_id = $course->id;
                                    $schedule->room_id = $room->id;
                                    $schedule->day = $groupDay;
                                    $schedule->start_time = $groupTimeSlot[0];
                                    $schedule->end_time = $groupTimeSlot[1];
                                    $schedule->save();
                                    
                                    \Log::info('Created schedule ID: ' . $schedule->id);
                                    
                                    // Increment day distribution counter
                                    $dayDistribution[$groupDay]++;
                                    
                                    // Add to result
                                    $generatedSchedules[] = [
                                        'id' => (string)$schedule->id,
                                        'namaDosen' => $lecturer->nama,
                                        'mataKuliah' => $course->nama,
                                        'semester' => (string)$course->semester,
                                        'ruangan' => $room->nama,
                                        'waktu' => $groupTimeSlot[0] . ' - ' . $groupTimeSlot[1],
                                        'hari' => $groupDay,
                                    ];
                                    
                                    $scheduleCreated = true;
                                    break;
                                } catch (\Exception $e) {
                                    \Log::error('Error creating schedule: ' . $e->getMessage());
                                    // Continue to next room
                                }
                            }
                        }
                        
                        // If couldn't use the group slot, find another slot
                        if (!$scheduleCreated) {
                            // Try to find an available slot in any day
                            foreach ($days as $day) {
                                if ($scheduleCreated) break;
                                
                                foreach ($timeSlots as $timeSlot) {
                                    if ($scheduleCreated) break;
                                    
                                    // Check if lecturer is available at this time
                                    $lecturerSlotKey = $day . '_' . $timeSlot[0] . '_' . $lecturer->id;
                                    if (isset($usedLecturerSlots[$lecturerSlotKey])) {
                                        continue; // Lecturer already has a class at this time
                                    }
                                    
                                    foreach ($rooms as $room) {
                                        // Check if this slot is already used
                                        $slotKey = $day . '_' . $timeSlot[0] . '_' . $room->id;
                                        
                                        if (!isset($usedSlots[$slotKey])) {
                                            \Log::info('Found alternative slot: ' . $slotKey);
                                            
                                            // Mark this slot as used
                                            $usedSlots[$slotKey] = true;
                                            $usedLecturerSlots[$lecturerSlotKey] = true;
                                            
                                            try {
                                                // Create schedule
                                                $schedule = new Schedule();
                                                $schedule->lecturer_id = $lecturer->id;
                                                $schedule->course_id = $course->id;
                                                $schedule->room_id = $room->id;
                                                $schedule->day = $day;
                                                $schedule->start_time = $timeSlot[0];
                                                $schedule->end_time = $timeSlot[1];
                                                $schedule->save();
                                                
                                                \Log::info('Created schedule ID: ' . $schedule->id);
                                                
                                                // Increment day distribution counter
                                                $dayDistribution[$day]++;
                                                
                                                // Add to result
                                                $generatedSchedules[] = [
                                                    'id' => (string)$schedule->id,
                                                    'namaDosen' => $lecturer->nama,
                                                    'mataKuliah' => $course->nama,
                                                    'semester' => (string)$course->semester,
                                                    'ruangan' => $room->nama,
                                                    'waktu' => $timeSlot[0] . ' - ' . $timeSlot[1],
                                                    'hari' => $day,
                                                ];
                                                
                                                $scheduleCreated = true;
                                                break;
                                            } catch (\Exception $e) {
                                                \Log::error('Error creating schedule: ' . $e->getMessage());
                                                // Continue to next slot
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                    
                    if (!$scheduleCreated) {
                        \Log::warning('Could not find available slot for course ' . $course->nama . ' with lecturer ' . $lecturer->nama);
                    }
                }
            }
            
            // Log the current distribution
            \Log::info('Current day distribution: ' . json_encode($dayDistribution));
        }
        
        // Sort generated schedules by semester, day, and time for better display
        usort($generatedSchedules, function($a, $b) {
            // First sort by semester
            $semesterA = (int)$a['semester'];
            $semesterB = (int)$b['semester'];
            if ($semesterA !== $semesterB) {
                return $semesterA - $semesterB;
            }
            
            // Then by day (using a custom order)
            $dayOrder = ['Senin' => 1, 'Selasa' => 2, 'Rabu' => 3, 'Kamis' => 4, 'Jumat' => 5];
            $dayA = $dayOrder[$a['hari']] ?? 0;
            $dayB = $dayOrder[$b['hari']] ?? 0;
            if ($dayA !== $dayB) {
                return $dayA - $dayB;
            }
            
            // Then by time
            $timeA = explode(' - ', $a['waktu'])[0];
            $timeB = explode(' - ', $b['waktu'])[0];
            return strcmp($timeA, $timeB);
        });
        
                \Log::info('Schedule generation completed. Generated: ' . count($generatedSchedules) . ' schedules');
        \Log::info('Final day distribution: ' . json_encode($dayDistribution));
        
        return response()->json($generatedSchedules);
    } catch (\Exception $e) {
        \Log::error('Schedule generation failed: ' . $e->getMessage());
        \Log::error('Stack trace: ' . $e->getTraceAsString());
        
        return response()->json([
            'message' => 'Failed to generate schedules',
            'error' => $e->getMessage()
        ], 500);
    }
}







    // Delete a schedule
    public function delete_schedule(int $schedule_id)
    {
        try {
            $schedule = Schedule::findOrFail($schedule_id);
            $schedule->delete();
            
            return response()->json([
                'message' => 'Schedule deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete schedule',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Delete all schedules
    public function delete_all_schedules()
    {
        try {
            Schedule::truncate();
            
            return response()->json([
                'message' => 'All schedules deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete schedules',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Filter schedules by day
    public function filter_schedules_by_day(string $day)
    {
        $schedules = Schedule::with(['lecturer', 'course', 'room'])
            ->where('day', $day)
            ->get();
        
        // Transform data to match frontend structure
        $transformedSchedules = $schedules->map(function($schedule) {
            return [
                'id' => (string)$schedule->id,
                'namaDosen' => $schedule->lecturer->nama,
                'mataKuliah' => $schedule->course->nama,
                'semester' => (string)$schedule->course->semester,
                'ruangan' => $schedule->room->nama,
                'waktu' => $schedule->start_time . ' - ' . $schedule->end_time,
                'hari' => $schedule->day,
            ];
        });
        
        return response()->json($transformedSchedules);
    }

    // Search schedules
    public function search_schedules(Request $request)
    {
        $query = $request->input('query', '');
        
        $schedules = Schedule::with(['lecturer', 'course', 'room'])
            ->whereHas('lecturer', function($q) use ($query) {
                $q->where('nama', 'like', "%{$query}%");
            })
            ->orWhereHas('course', function($q) use ($query) {
                $q->where('nama', 'like', "%{$query}%");
            })
            ->orWhereHas('room', function($q) use ($query) {
                $q->where('nama', 'like', "%{$query}%");
            })
            ->get();
        
        // Transform data to match frontend structure
        $transformedSchedules = $schedules->map(function($schedule) {
            return [
                'id' => (string)$schedule->id,
                'namaDosen' => $schedule->lecturer->nama,
                'mataKuliah' => $schedule->course->nama,
                'semester' => (string)$schedule->course->semester,
                'ruangan' => $schedule->room->nama,
                'waktu' => $schedule->start_time . ' - ' . $schedule->end_time,
                'hari' => $schedule->day,
            ];
        });
        
        return response()->json($transformedSchedules);
    }

    // Export schedules to CSV (returns CSV content as string)
    public function export_schedules_to_csv()
    {
        $schedules = Schedule::with(['lecturer', 'course', 'room'])->get();
        
        $headers = [
            'No',
            'Hari',
            'Waktu',
            'Mata Kuliah',
            'Semester',
            'Dosen',
            'Ruangan'
        ];
        
        $csvContent = implode(',', $headers) . "\n";
        
        foreach ($schedules as $index => $schedule) {
            $row = [
                $index + 1,
                $schedule->day,
                $schedule->start_time . ' - ' . $schedule->end_time,
                $schedule->course->nama,
                $schedule->course->semester,
                $schedule->lecturer->nama,
                $schedule->room->nama
            ];
            
            $csvContent .= implode(',', $row) . "\n";
        }
        
        return response($csvContent)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="jadwal_kuliah.csv"');
    }
}
