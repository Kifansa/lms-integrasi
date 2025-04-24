<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\EnrollmentController;
<<<<<<< HEAD
=======

>>>>>>> 43540afa8babee2acbed192eac8efb9347232b89


Route::get('/', function () {
    return view('welcome');
});


Route::get('/enrollments', [EnrollmentController::class, 'index']);
Route::get('/enrollments/{id}', [EnrollmentController::class, 'show']);
Route::post('/enrollments', [EnrollmentController::class, 'store']);
Route::put('/enrollments/{id}', [EnrollmentController::class, 'update']);
Route::delete('/enrollments/{id}', [EnrollmentController::class, 'destroy']);

Route::get('/students', [StudentController::class, 'index']);
Route::get('/students/{id}', [StudentController::class, 'show']);
Route::post('/students', [StudentController::class, 'store']);
Route::put('/students/{id}', [StudentController::class, 'update']);
Route::delete('/students/{id}', [StudentController::class, 'destroy']);

Route::get('/courses', [CourseController::class, 'index']);
Route::get('/courses/{id}', [CourseController::class, 'show']);
Route::post('/courses', [CourseController::class, 'store']);
Route::put('/courses/{id}', [CourseController::class, 'update']);
Route::delete('/courses/{id}', [CourseController::class, 'destroy']);

Route::resource('courses', CourseController::class);
Route::resource('enrollments', EnrollmentController::class);

