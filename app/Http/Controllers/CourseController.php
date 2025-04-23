<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Course;

class CourseController extends Controller
{
    public function index()
    {
        $courses = Course::all();
        return response()->json($courses);
    }

    public function show($id)
    {
        $course = Course::find($id);

        if ($course) {
            return response()->json($course);
        }

        return response()->json(['message' => 'Kursus tidak ditemukan'], 404);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
        ]);

        $course = new Course();
        $course->title = $request->title;
        $course->description = $request->description;
        $course->save();

        return response()->json(['message' => 'Kursus berhasil dibuat', 'course' => $course], 201);
    }

    public function update(Request $request, $id)
    {
        $course = Course::find($id);

        if (!$course) {
            return response()->json(['message' => 'Kursus tidak ditemukan'], 404);
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
        ]);

        $course->title = $request->title;
        $course->description = $request->description;
        $course->save();

        return response()->json(['message' => 'Kursus berhasil diperbarui', 'course' => $course]);
    }

    public function destroy($id)
    {
        $course = Course::find($id);

        if (!$course) {
            return response()->json(['message' => 'Kursus tidak ditemukan'], 404);
        }

        $course->delete();

        return response()->json(['message' => 'Kursus berhasil dihapus']);
    }
}
