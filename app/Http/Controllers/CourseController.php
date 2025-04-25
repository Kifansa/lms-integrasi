<?php

namespace App\Http\Controllers;

use App\Http\Resources\CourseResource;
use Illuminate\Http\Request;
use App\Models\Course;
use Illuminate\Support\Facades\Validator;

class CourseController extends Controller
{

    public function index()
    {
        $courses = Course::all();
        return new CourseResource($courses, 'Success', 'List of courses');
    }


    public function show(string $id)
    {
        $course = Course::find($id);
        if ($course) {
            return new CourseResource($course, 'Success', 'Course found');
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
        return new CourseResource($course, 'Success', 'Course created successfully');
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

        return new CourseResource($course, 'Success', 'Course updated successfully');
    }


    public function destroy(string $id)
    {
        $course = Course::find($id);

        if (!$course) {
            return new CourseResource(null, 'Failed', 'Course not found');
        }

        $course->delete();

        return new CourseResource(null, 'Success', 'Course deleted successfully');
    }
}
