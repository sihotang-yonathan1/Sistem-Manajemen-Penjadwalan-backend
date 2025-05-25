<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\StudentEnrollment;
use App\Models\Course;
use App\Models\User;
use Illuminate\Support\Facades\Log;

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

            $enrollment = StudentEnrollment::create([
                'user_id' => $request->user_id,
                'course_id' => $request->course_id,
                'semester' => $request->semester,
                'tahun_akademik' => '2024/2025',
                'status' => 'active'
            ]);

            $enrollment->load(['course', 'course.lecturers']);

            // Transform response data
            $transformedEnrollment = [
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

            return response()->json([
                'message' => 'Successfully enrolled in course',
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
}
