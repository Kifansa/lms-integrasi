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
    public function index(): EnrollmentResource
    {

        $enrollments = Enrollment::with(['student', 'course'])->get();


        $students = Student::all();
        $courses = Course::all();


        return new EnrollmentResource($enrollments, 'Success', 'List of enrollments');
    }

    public function show(string $id)
    {
        $enrollment = Enrollment::with(['student', 'course'])->find($id);

        if ($enrollment) {

            $student = Student::find($enrollment->student_id);
            $course = Course::find($enrollment->course_id);

            return new EnrollmentResource($enrollment, 'Success', 'Enrollment found');
        } else {
            return new EnrollmentResource(null, 'Failed', 'Enrollment not found');
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'student_id' => 'required|exists:students,id',
            'course_id' => 'required|exists:courses,id',
        ]);

        if ($validator->fails()) {
            return new EnrollmentResource(null, 'Failed', $validator->errors());
        }


        $student = Student::find($request->student_id);
        $course = Course::find($request->course_id);


        if (!$student || !$course) {
            return new EnrollmentResource(null, 'Failed', 'Student or Course not found');
        }

        $enrollment = Enrollment::create([
            'student_id' => $student->id,
            'course_id' => $course->id,

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


        $student = Student::find($request->student_id);
        $course = Course::find($request->course_id);


        if (!$student || !$course) {
            return new EnrollmentResource(null, 'Failed', 'Student or Course not found');
        }

        $enrollment->update([
            'student_id' => $student->id,
            'course_id' => $course->id,

        ]);


        $enrollment->load(['student', 'course']);

        return new EnrollmentResource($enrollment, 'Success', 'Enrollment updated successfully');
    }

    public function destroy(string $id)
    {
        $enrollment = Enrollment::find($id);

        if (!$enrollment) {
            return new EnrollmentResource(null, 'Failed', 'Enrollment not found');
        }


        $student = Student::find($enrollment->student_id);
        $course = Course::find($enrollment->course_id);


        $deletedEnrollmentInfo = [
            'id' => $enrollment->id,
            'student_name' => $student ? $student->name : null,
            'course_title' => $course ? $course->title : null
        ];

        $enrollment->delete();

        return new EnrollmentResource(null, 'Success', 'Enrollment deleted successfully');
    }
}
