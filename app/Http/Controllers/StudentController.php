<?php

namespace App\Http\Controllers;

use App\Http\Resources\StudentResource;
use Illuminate\Http\Request;
use App\Models\Student;
use Illuminate\Support\Facades\Validator;
use App\Models\Enrollment;  

class StudentController extends Controller
{
    public function index()
    {
        $students = Student::all();
        return new StudentResource($students, 'Success', 'List of students');
    }

    public function show(string $id)
    {
        $student = Student::find($id);

        $enrollments = Enrollment::where('student_id', $id)->with('course')->get();

        if ($student) {
            return new StudentResource([
                'student' => $student,
                'enrollments' => $enrollments
            ], 'Success', 'Student found');
        } else {
            return new StudentResource(null, 'Failed', 'Student not found');
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nim' => 'required',
            'name' => 'required',
        ]);

        if ($validator->fails()) {
            return new StudentResource(null, 'Failed', $validator->errors());
        }

        $student = Student::create($request->all());
        return new StudentResource($student, 'Success', 'Student created successfully');
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

        return new StudentResource($student, 'Success', 'Student updated successfully');
    }

    public function destroy(string $id)
    {
        $student = Student::find($id);

        if (!$student) {
            return new StudentResource(null, 'Failed', 'Student not found');
        }

        $student->delete();

        return new StudentResource(null, 'Success', 'Student deleted successfully');
    }
}
