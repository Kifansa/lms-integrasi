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
        $enrollments = Enrollment::with(['student', 'course'])->get();
        return new EnrollmentResource($enrollments, 'Success', 'List of enrollments');
    }


    public function show(string $id)
    {
        $enrollment = Enrollment::with(['student', 'course'])->find($id);

        if ($enrollment) {
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

        $enrollment = Enrollment::create($request->all());
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
}
