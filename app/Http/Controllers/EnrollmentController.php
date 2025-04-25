<?php

namespace App\Http\Controllers;

use App\Http\Resources\EnrollmentResource;
use Illuminate\Http\Request;
use App\Models\Enrollment;
use App\Models\Student;
use App\Models\Course;
use Illuminate\Support\Facades\Validator;

class EnrollmentController extends Controller
{
    public function index()
    {
        $enrollments = Enrollment::all(); 
        return new EnrollmentResource($enrollments, 'Success', 'List of enrollments');
    }

    public function show(string $id)
    {
        $enrollment = Enrollment::find($id);

        if ($enrollment) {
            return new EnrollmentResource($enrollment, 'Success', 'Enrollment found');
        } else {
            return new EnrollmentResource(null, 'Failed', 'Enrollment not found');
        }
    }

    public function store(Request $request)
    {
        $student = Student::find($request->student_id);
        $course = Course::find($request->course_id);

        if (!$student || !$course) {
            return new EnrollmentResource(null, 'Failed', 'Student or Course not found');
        }

        $validator = Validator::make($request->all(), [
            'student_id' => 'required|exists:students,id',
            'course_id' => 'required|exists:courses,id',
        ]);

        if ($validator->fails()) {
            return new EnrollmentResource(null, 'Failed', $validator->errors());
        }

        $enrollment = Enrollment::create([
            'student_id' => $request->student_id,
            'course_id' => $request->course_id,
        ]);

        return new EnrollmentResource($enrollment, 'Success', 'Enrollment created successfully');
    }

    public function update(Request $request, string $id)
    {
        $enrollment = Enrollment::find($id);

        if (!$enrollment) {
            return new EnrollmentResource(null, 'Failed', 'Enrollment not found');
        }

        $validator = Validator::make($request->all(), [
            'student_id' => 'required|exists:students,id',
            'course_id' => 'required|exists:courses,id',
        ]);

        if ($validator->fails()) {
            return new EnrollmentResource(null, 'Failed', $validator->errors());
        }

        $enrollment->update($request->all());

        return new EnrollmentResource($enrollment, 'Success', 'Enrollment updated successfully');
    }

    public function destroy(string $id)
    {
        $enrollment = Enrollment::find($id);

        if (!$enrollment) {
            return new EnrollmentResource(null, 'Failed', 'Enrollment not found');
        }

        $enrollment->delete();

        return new EnrollmentResource(null, 'Success', 'Enrollment deleted successfully');
    }

    public function getStudentCourses(string $student_id)
    {
        $student = Student::find($student_id);

        if (!$student) {
            return new EnrollmentResource(null, 'Failed', 'Student not found');
        }

        $enrollments = Enrollment::where('student_id', $student_id)
            ->with('course')
            ->get();

        return new EnrollmentResource($enrollments, 'Success', 'Courses for student');
    }

    public function getCourseStudents(string $course_id)
    {
        $course = Course::find($course_id);

        if (!$course) {
            return new EnrollmentResource(null, 'Failed', 'Course not found');
        }

        $enrollments = Enrollment::where('course_id', $course_id)
            ->with('student')
            ->get();

        return new EnrollmentResource($enrollments, 'Success', 'Students for course');
    }
}
