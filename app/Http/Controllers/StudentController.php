<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Student;

class StudentController extends Controller
{
    public function index()
    {
        $students = Student::all();
        return response()->json($students);
    }

    public function show($id)
    {
        $student = Student::find($id);

        if ($student) {
            return response()->json($student);
        }

        return response()->json(['message' => 'Mahasiswa tidak ditemukan'], 404);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nim' => 'required|string|max:255|unique:students',
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:students',
        ]);

        $student = new Student();
        $student->nim = $request->nim;
        $student->name = $request->name;
        $student->email = $request->email;
        $student->save();

        return response()->json(['message' => 'Mahasiswa berhasil dibuat', 'student' => $student], 201);
    }

    public function update(Request $request, $id)
    {
        $student = Student::find($id);

        if (!$student) {
            return response()->json(['message' => 'Mahasiswa tidak ditemukan'], 404);
        }

        $request->validate([
            'nim' => 'required|string|max:255|unique:students,nim,' . $student->id,
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:students,email,' . $student->id,
        ]);

        $student->nim = $request->nim;
        $student->name = $request->name;
        $student->email = $request->email;
        $student->save();

        return response()->json(['message' => 'Data mahasiswa berhasil diperbarui', 'student' => $student]);
    }

    public function destroy($id)
    {
        $student = Student::find($id);

        if (!$student) {
            return response()->json(['message' => 'Mahasiswa tidak ditemukan'], 404);
        }

        $student->delete();

        return response()->json(['message' => 'Mahasiswa berhasil dihapus']);
    }
}
