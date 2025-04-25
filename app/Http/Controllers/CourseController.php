<?php

namespace App\Http\Controllers;

use App\Http\Resources\CourseResource;
use Illuminate\Http\Request;
use App\Models\Course;
use App\Models\Student;
use App\Models\Enrollment;
use Illuminate\Support\Facades\Validator;

class CourseController extends Controller
{
    public function index()
    {
        // Mengambil semua course dan melakukan eager loading relasi student
        $courses = Course::all();

        // Mengambil data student untuk setiap course
        $coursesWithStudents = [];
        foreach ($courses as $course) {
            // Mendapatkan semua enrollment untuk course ini
            $enrollments = Enrollment::where('course_id', $course->id)->get();

            // Mendapatkan student_ids dari enrollment
            $studentIds = $enrollments->pluck('student_id')->toArray();

            // Mendapatkan data student
            $students = Student::whereIn('id', $studentIds)->get();

            // Menambahkan course dengan students ke array
            $coursesWithStudents[] = [
                'course' => $course,
                'students' => $students
            ];
        }

        return new CourseResource($coursesWithStudents, 'Success', 'List of courses with enrolled students');
    }

    public function show(string $id)
    {
        $course = Course::find($id);

        if ($course) {
            // Mendapatkan semua enrollment untuk course ini
            $enrollments = Enrollment::where('course_id', $id)->get();

            // Mendapatkan semua student yang mengambil course ini
            $studentIds = $enrollments->pluck('student_id')->toArray();
            $students = Student::whereIn('id', $studentIds)->get();

            // Data detail untuk setiap student (bisa ditambahkan informasi tambahan jika perlu)
            $studentsDetail = [];
            foreach ($students as $student) {
                $enrollment = $enrollments->where('student_id', $student->id)->first();
                $studentsDetail[] = [
                    'student' => $student,
                    'enrollment_id' => $enrollment ? $enrollment->id : null,
                    'enrollment_date' => $enrollment ? $enrollment->created_at : null,
                    // Tambahkan data enrollment lain jika diperlukan
                ];
            }

            return new CourseResource([
                'course' => $course,
                'students' => $studentsDetail,
                'total_students' => count($students)
            ], 'Success', 'Course found with enrolled students');
        } else {
            return new CourseResource(null, 'Failed', 'Course not found');
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'description' => 'required',
        ]);

        if ($validator->fails()) {
            return new CourseResource(null, 'Failed', $validator->errors());
        }

        $course = Course::create($request->all());

        // Jika ada student_ids dalam request, tambahkan mereka ke course ini
        $enrolledStudents = [];
        if ($request->has('student_ids') && is_array($request->student_ids)) {
            foreach ($request->student_ids as $studentId) {
                $student = Student::find($studentId);

                if ($student) {
                    // Buat enrollment untuk student ini
                    Enrollment::create([
                        'student_id' => $student->id,
                        'course_id' => $course->id
                    ]);

                    $enrolledStudents[] = $student;
                }
            }
        }

        return new CourseResource([
            'course' => $course,
            'enrolled_students' => $enrolledStudents,
            'total_enrolled' => count($enrolledStudents)
        ], 'Success', 'Course created successfully');
    }

    public function update(Request $request, string $id)
    {
        $course = Course::find($id);

        if (!$course) {
            return new CourseResource(null, 'Failed', 'Course not found');
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'description' => 'required',
        ]);

        if ($validator->fails()) {
            return new CourseResource(null, 'Failed', $validator->errors());
        }

        $course->update($request->all());

        // Mendapatkan semua enrollment yang ada untuk course ini
        $existingEnrollments = Enrollment::where('course_id', $id)->get();
        $existingStudentIds = $existingEnrollments->pluck('student_id')->toArray();

        // Update enrollment jika ada student_ids dalam request
        $updatedStudents = [];
        $addedStudents = [];
        $removedStudents = [];

        if ($request->has('student_ids') && is_array($request->student_ids)) {
            // Hapus enrollment yang tidak ada di request baru
            foreach ($existingEnrollments as $enrollment) {
                if (!in_array($enrollment->student_id, $request->student_ids)) {
                    $student = Student::find($enrollment->student_id);
                    if ($student) {
                        $removedStudents[] = $student;
                    }
                    $enrollment->delete();
                }
            }

            // Tambah enrollment baru
            foreach ($request->student_ids as $studentId) {
                if (!in_array($studentId, $existingStudentIds)) {
                    $student = Student::find($studentId);
                    if ($student) {
                        Enrollment::create([
                            'student_id' => $student->id,
                            'course_id' => $course->id
                        ]);
                        $addedStudents[] = $student;
                    }
                } else {
                    $student = Student::find($studentId);
                    if ($student) {
                        $updatedStudents[] = $student;
                    }
                }
            }
        }

        // Dapatkan semua student yang terdaftar setelah update
        $enrollments = Enrollment::where('course_id', $id)->get();
        $studentIds = $enrollments->pluck('student_id')->toArray();
        $currentStudents = Student::whereIn('id', $studentIds)->get();

        return new CourseResource([
            'course' => $course,
            'current_students' => $currentStudents,
            'added_students' => $addedStudents,
            'removed_students' => $removedStudents,
            'unchanged_students' => $updatedStudents,
            'total_students' => count($currentStudents)
        ], 'Success', 'Course updated successfully');
    }

    public function destroy(string $id)
    {
        $course = Course::find($id);

        if (!$course) {
            return new CourseResource(null, 'Failed', 'Course not found');
        }

        // Dapatkan semua student yang terdaftar sebelum menghapus course
        $enrollments = Enrollment::where('course_id', $id)->get();
        $studentIds = $enrollments->pluck('student_id')->toArray();
        $enrolledStudents = Student::whereIn('id', $studentIds)->get();

        // Data detail student yang akan kehilangan enrollment
        $affectedStudentsDetail = [];
        foreach ($enrolledStudents as $student) {
            $enrollment = $enrollments->where('student_id', $student->id)->first();
            $affectedStudentsDetail[] = [
                'student_id' => $student->id,
                'student_name' => $student->name,
                'enrollment_id' => $enrollment ? $enrollment->id : null,
                'enrollment_date' => $enrollment ? $enrollment->created_at : null
            ];
        }

        // Hapus semua enrollment terkait course ini
        foreach ($enrollments as $enrollment) {
            $enrollment->delete();
        }

        $course->delete();

        return new CourseResource([
            'deleted_course' => [
                'id' => $id,
                'title' => $course->title
            ],
            'affected_students' => $affectedStudentsDetail,
            'total_affected_students' => count($affectedStudentsDetail)
        ], 'Success', 'Course and related enrollments deleted successfully');
    }
}
