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

        $courses = Course::all();


        $coursesWithStudents = [];
        foreach ($courses as $course) {

            $enrollments = Enrollment::where('course_id', $course->id)->get();


            $studentIds = $enrollments->pluck('student_id')->toArray();


            $students = Student::whereIn('id', $studentIds)->get();


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

            $enrollments = Enrollment::where('course_id', $id)->get();


            $studentIds = $enrollments->pluck('student_id')->toArray();
            $students = Student::whereIn('id', $studentIds)->get();


            $studentsDetail = [];
            foreach ($students as $student) {
                $enrollment = $enrollments->where('student_id', $student->id)->first();
                $studentsDetail[] = [
                    'student' => $student,
                    'enrollment_id' => $enrollment ? $enrollment->id : null,
                    'enrollment_date' => $enrollment ? $enrollment->created_at : null,

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


        $enrolledStudents = [];
        if ($request->has('student_ids') && is_array($request->student_ids)) {
            foreach ($request->student_ids as $studentId) {
                $student = Student::find($studentId);

                if ($student) {

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


        $existingEnrollments = Enrollment::where('course_id', $id)->get();
        $existingStudentIds = $existingEnrollments->pluck('student_id')->toArray();


        $updatedStudents = [];
        $addedStudents = [];
        $removedStudents = [];

        if ($request->has('student_ids') && is_array($request->student_ids)) {

            foreach ($existingEnrollments as $enrollment) {
                if (!in_array($enrollment->student_id, $request->student_ids)) {
                    $student = Student::find($enrollment->student_id);
                    if ($student) {
                        $removedStudents[] = $student;
                    }
                    $enrollment->delete();
                }
            }


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


        $enrollments = Enrollment::where('course_id', $id)->get();
        $studentIds = $enrollments->pluck('student_id')->toArray();
        $enrolledStudents = Student::whereIn('id', $studentIds)->get();


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
