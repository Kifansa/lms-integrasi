<?php

namespace App\Http\Controllers;

use App\Http\Resources\StudentResource;
use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\Enrollment;
use App\Models\Course;
use Illuminate\Support\Facades\Validator;

class StudentController extends Controller
{
    public function index()
    {

        $students = Student::all();


        $allEnrollments = Enrollment::all();


        $studentsWithEnrollments = [];
        foreach ($students as $student) {
            $studentEnrollments = $allEnrollments->where('student_id', $student->id);
            $studentsWithEnrollments[] = [
                'student' => $student,
                'enrollments' => $studentEnrollments
            ];
        }

        return new StudentResource($studentsWithEnrollments, 'Success', 'List of students with their enrollments');
    }

    public function show(string $id)
    {

        $student = Student::find($id);

        if (!$student) {
            return new StudentResource(null, 'Failed', 'Student not found');
        }


        $enrollments = Enrollment::where('student_id', $id)->get();


        $enrollmentsWithCourses = [];
        foreach ($enrollments as $enrollment) {
            $course = Course::find($enrollment->course_id);
            $enrollmentsWithCourses[] = [
                'enrollment' => $enrollment,
                'course' => $course
            ];
        }

        return new StudentResource([
            'student' => $student,
            'enrollments_with_courses' => $enrollmentsWithCourses
        ], 'Success', 'Student found with enrollments');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nim' => 'required',
            'name' => 'required',
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return new StudentResource(null, 'Failed', $validator->errors());
        }


        $student = Student::create($request->all());


        $enrollmentModel = new Enrollment();


        $createdEnrollments = [];
        if ($request->has('course_ids') && is_array($request->course_ids)) {
            foreach ($request->course_ids as $courseId) {
                $enrollment = Enrollment::create([
                    'student_id' => $student->id,
                    'course_id' => $courseId
                ]);
                $createdEnrollments[] = $enrollment;
            }
        }

        return new StudentResource([
            'student' => $student,
            'created_enrollments' => $createdEnrollments
        ], 'Success', 'Student created successfully');
    }

    public function update(Request $request, string $id)
    {

        $student = Student::find($id);

        if (!$student) {
            return new StudentResource(null, 'Failed', 'Student not found');
        }

        $validator = Validator::make($request->all(), [
            'nim' => 'required',
            'name' => 'required',
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return new StudentResource(null, 'Failed', $validator->errors());
        }

        $student->update($request->all());


        $existingEnrollments = Enrollment::where('student_id', $id)->get();


        $updatedEnrollments = [];
        $deletedEnrollments = [];

        if ($request->has('course_ids') && is_array($request->course_ids)) {

            foreach ($existingEnrollments as $enrollment) {
                if (!in_array($enrollment->course_id, $request->course_ids)) {
                    $enrollment->delete();
                    $deletedEnrollments[] = $enrollment->id;
                } else {
                    $updatedEnrollments[] = $enrollment;
                }
            }


            $existingCourseIds = $existingEnrollments->pluck('course_id')->toArray();
            foreach ($request->course_ids as $courseId) {
                if (!in_array($courseId, $existingCourseIds)) {
                    $newEnrollment = Enrollment::create([
                        'student_id' => $student->id,
                        'course_id' => $courseId
                    ]);
                    $updatedEnrollments[] = $newEnrollment;
                }
            }
        } else {
            $updatedEnrollments = $existingEnrollments->toArray();
        }

        return new StudentResource([
            'student' => $student,
            'updated_enrollments' => $updatedEnrollments,
            'deleted_enrollments' => $deletedEnrollments
        ], 'Success', 'Student updated successfully');
    }

    public function destroy(string $id)
    {

        $student = Student::find($id);

        if (!$student) {
            return new StudentResource(null, 'Failed', 'Student not found');
        }


        $enrollmentsToDelete = Enrollment::where('student_id', $id)->get();


        $deletedEnrollmentData = [];
        foreach ($enrollmentsToDelete as $enrollment) {
            $course = Course::find($enrollment->course_id);
            $deletedEnrollmentData[] = [
                'enrollment_id' => $enrollment->id,
                'course_id' => $enrollment->course_id,
                'course_name' => $course ? $course->name : 'Unknown'
            ];


            $enrollment->delete();
        }


        $student->delete();

        return new StudentResource([
            'deleted_student_id' => $id,
            'deleted_student_name' => $student->name,
            'deleted_enrollments' => $deletedEnrollmentData
        ], 'Success', 'Student and related enrollments deleted successfully');
    }
}
