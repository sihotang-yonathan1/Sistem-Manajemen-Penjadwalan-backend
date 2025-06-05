<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\StudentEnrollment;
use App\Models\Course;
use App\Models\User;
use App\Models\Schedule;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class StudentEnrollmentController extends Controller
{
    // Get available courses for enrollment
    public function getAvailableCourses()
    {
        try {
            $courses = Course::with('lecturers')->get();
            
            // Transform data to match frontend expectations
            $transformedCourses = $courses->map(function ($course) {
                return [
                    'id' => (string) $course->id,
                    'kode' => $course->kode,
                    'nama' => $course->nama,
                    'semester' => $course->semester,
                    'sks' => $course->sks,
                    'lecturers' => $course->lecturers->map(function ($lecturer) {
                        return [
                            'id' => (string) $lecturer->id,
                            'nama' => $lecturer->nama,
                            'nip' => $lecturer->nip,
                        ];
                    })
                ];
            });

            return response()->json($transformedCourses);
        } catch (\Exception $e) {
            Log::error('Error fetching courses: ' . $e->getMessage());
            return response()->json([], 200); // Return empty array instead of error
        }
    }

    // Get student's enrolled courses
    public function getStudentCourses($userId)
    {
        try {
            $user = User::find($userId);
            
            if (!$user) {
                return response()->json([], 200); // Return empty array if user not found
            }

            if ($user->role !== 'mahasiswa') {
                return response()->json([
                    'message' => 'Only students can access this endpoint'
                ], 403);
            }

            $enrollments = StudentEnrollment::with(['course', 'course.lecturers'])
                ->where('user_id', $userId)
                ->where('status', 'active')
                ->get();

            // Transform data to match frontend expectations
            $transformedEnrollments = $enrollments->map(function ($enrollment) {
                return [
                    'id' => (string) $enrollment->id,
                    'user_id' => (string) $enrollment->user_id,
                    'course_id' => (string) $enrollment->course_id,
                    'semester' => $enrollment->semester,
                    'tahun_akademik' => $enrollment->tahun_akademik,
                    'status' => $enrollment->status,
                    'created_at' => $enrollment->created_at->toISOString(),
                    'updated_at' => $enrollment->updated_at->toISOString(),
                    'course' => [
                        'id' => (string) $enrollment->course->id,
                        'kode' => $enrollment->course->kode,
                        'nama' => $enrollment->course->nama,
                        'semester' => $enrollment->course->semester,
                        'sks' => $enrollment->course->sks,
                        'lecturers' => $enrollment->course->lecturers->map(function ($lecturer) {
                            return [
                                'id' => (string) $lecturer->id,
                                'nama' => $lecturer->nama,
                                'nip' => $lecturer->nip,
                            ];
                        })
                    ]
                ];
            });

            return response()->json($transformedEnrollments);
        } catch (\Exception $e) {
            Log::error('Error fetching student courses: ' . $e->getMessage());
            return response()->json([], 200); // Return empty array instead of error
        }
    }

    // Enroll student to course
    public function enrollCourse(Request $request)
{
    try {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'course_id' => 'required|exists:course,id',
            'semester' => 'required|string'
        ]);

        // Check if user is mahasiswa
        $user = User::find($request->user_id);
        if ($user->role !== 'mahasiswa') {
            return response()->json([
                'message' => 'Only students can enroll in courses'
            ], 403);
        }

        // Check if already enrolled
        $existingEnrollment = StudentEnrollment::where('user_id', $request->user_id)
            ->where('course_id', $request->course_id)
            ->where('semester', $request->semester)
            ->where('tahun_akademik', '2024/2025')
            ->where('status', 'active')
            ->first();

        if ($existingEnrollment) {
            return response()->json([
                'message' => 'Student already enrolled in this course'
            ], 409);
        }

        // LOGIKA BARU - Assign dosen secara acak dan merata
        $assignedLecturerId = $this->assignLecturerToStudent($request->course_id);

        if (!$assignedLecturerId) {
            return response()->json([
                'message' => 'No lecturers available for this course'
            ], 400);
        }

        $enrollment = StudentEnrollment::create([
            'user_id' => $request->user_id,
            'course_id' => $request->course_id,
            'semester' => $request->semester,
            'tahun_akademik' => '2024/2025',
            'status' => 'active',
            'lecturer_id' => $assignedLecturerId  // TAMBAHAN BARU
        ]);

        $enrollment->load(['course', 'course.lecturers', 'lecturer']);

        // Transform response data
        $transformedEnrollment = [
            'id' => (string) $enrollment->id,
            'user_id' => (string) $enrollment->user_id,
            'course_id' => (string) $enrollment->course_id,
            'semester' => $enrollment->semester,
            'tahun_akademik' => $enrollment->tahun_akademik,
            'status' => $enrollment->status,
            'lecturer_id' => (string) $enrollment->lecturer_id,  // TAMBAHAN BARU
            'assigned_lecturer' => $enrollment->lecturer->nama,   // TAMBAHAN BARU
            'created_at' => $enrollment->created_at->toISOString(),
            'updated_at' => $enrollment->updated_at->toISOString(),
            'course' => [
                'id' => (string) $enrollment->course->id,
                'kode' => $enrollment->course->kode,
                'nama' => $enrollment->course->nama,
                'semester' => $enrollment->course->semester,
                'sks' => $enrollment->course->sks,
                'lecturers' => $enrollment->course->lecturers->map(function ($lecturer) {
                    return [
                        'id' => (string) $lecturer->id,
                        'nama' => $lecturer->nama,
                        'nip' => $lecturer->nip,
                    ];
                })
            ]
        ];

        return response()->json([
            'message' => 'Successfully enrolled in course with assigned lecturer: ' . $enrollment->lecturer->nama,
            'data' => $transformedEnrollment
        ], 201);

    } catch (\Illuminate\Validation\ValidationException $e) {
        return response()->json([
            'message' => 'Validation failed',
            'errors' => $e->errors()
        ], 422);
    } catch (\Exception $e) {
        Log::error('Error enrolling in course: ' . $e->getMessage());
        return response()->json([
            'message' => 'Error enrolling in course',
            'error' => $e->getMessage()
        ], 500);
    }
}

// TAMBAHAN BARU - Method untuk assign dosen secara acak dan merata
private function assignLecturerToStudent($courseId)
{
    // Get all lecturers for this course
    $lecturers = DB::table('lecturer_course')
        ->where('course_id', $courseId)
        ->pluck('lecturer_id')
        ->toArray();

    if (empty($lecturers)) {
        return null;
    }

    // Count current enrollments for each lecturer in this course
    $lecturerCounts = [];
    foreach ($lecturers as $lecturerId) {
        $count = StudentEnrollment::where('course_id', $courseId)
            ->where('lecturer_id', $lecturerId)
            ->where('status', 'active')
            ->count();
        $lecturerCounts[$lecturerId] = $count;
    }

    // Find lecturer(s) with minimum students
    $minCount = min($lecturerCounts);
    $availableLecturers = array_keys(array_filter($lecturerCounts, function($count) use ($minCount) {
        return $count === $minCount;
    }));

    // Randomly select from lecturers with minimum students
    $selectedLecturerId = $availableLecturers[array_rand($availableLecturers)];

    Log::info("Assigned lecturer {$selectedLecturerId} to course {$courseId}. Current distribution: " . json_encode($lecturerCounts));

    return $selectedLecturerId;
}

    // Remove enrollment
    public function unenrollCourse(Request $request)
    {
        try {
            $request->validate([
                'user_id' => 'required|exists:users,id',
                'course_id' => 'required|exists:course,id'
            ]);

            $enrollment = StudentEnrollment::where('user_id', $request->user_id)
                ->where('course_id', $request->course_id)
                ->where('status', 'active')
                ->first();

            if (!$enrollment) {
                return response()->json([
                    'message' => 'Enrollment not found'
                ], 404);
            }

            $enrollment->delete();

            return response()->json([
                'message' => 'Successfully unenrolled from course'
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error unenrolling from course: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error unenrolling from course',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Get student's schedule based on enrolled courses
    public function getStudentSchedule($userId)
{
    try {
        Log::info('Getting schedule for student ID: ' . $userId);
        
        $user = User::find($userId);
        
        if (!$user) {
            Log::warning('User not found with ID: ' . $userId);
            return response()->json([]);
        }

        if ($user->role !== 'mahasiswa') {
            Log::warning('User is not a student: ' . $user->role);
            return response()->json([
                'message' => 'Only students can access this endpoint'
            ], 403);
        }

        // PERUBAHAN UTAMA - Get enrolled courses dengan lecturer yang ditugaskan
        $enrollments = StudentEnrollment::with(['course', 'lecturer'])
            ->where('user_id', $userId)
            ->where('status', 'active')
            ->get();

        Log::info('Found enrollments: ' . $enrollments->count());

        if ($enrollments->isEmpty()) {
            Log::info('No enrollments found for student');
            return response()->json([]);
        }

        $schedules = collect();

        foreach ($enrollments as $enrollment) {
            // PERUBAHAN UTAMA - Cari jadwal berdasarkan course_id DAN lecturer_id yang ditugaskan
            $courseSchedules = Schedule::with(['lecturer', 'course', 'room'])
                ->where('course_id', $enrollment->course_id)
                ->where('lecturer_id', $enrollment->lecturer_id)  // TAMBAHAN BARU
                ->get();

            $schedules = $schedules->merge($courseSchedules);
        }

        Log::info('Found schedules count: ' . $schedules->count());

        $transformedSchedules = $schedules->map(function($schedule) {
            return [
                'id' => (string) $schedule->id,
                'hari' => $schedule->day,
                'waktu' => $schedule->start_time . ' - ' . $schedule->end_time,
                'mataKuliah' => $schedule->course->nama,
                'namaDosen' => $schedule->lecturer->nama,
                'ruangan' => $schedule->room->nama,
                'semester' => (string) $schedule->course->semester,
                'kode' => $schedule->course->kode ?? '',
            ];
        });

        $result = $transformedSchedules->toArray();
        Log::info('Returning ' . count($result) . ' schedules');
        
        return response()->json($result, 200, [
            'Content-Type' => 'application/json',
            'Cache-Control' => 'no-cache, no-store, must-revalidate'
        ]);
        
    } catch (\Exception $e) {
        Log::error('Error in getStudentSchedule: ' . $e->getMessage());
        Log::error('Stack trace: ' . $e->getTraceAsString());
        
        return response()->json([], 200, [
            'Content-Type' => 'application/json'
        ]);
    }
}

}
