<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AssessmentController;
use App\Http\Controllers\AuthController;

Route::get('/', function () {
    $user = request()->user();

    if ($user) {
        return $user->hasRole(\App\Models\User::ROLE_COUNSELOR, \App\Models\User::ROLE_ADMIN)
            ? redirect()->route('assessments.index')
            : redirect()->route('student.assessments.index');
    }

    return view('home');
})->name('home');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->defaults('audience', 'student')->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1')->name('login.store');
    Route::get('/counselor/login', [AuthController::class, 'showLogin'])->defaults('audience', 'counselor')->name('counselor.login');
    Route::post('/counselor/login', [AuthController::class, 'login'])->middleware('throttle:10,1')->name('counselor.login.store');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:5,1')->name('register.store');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::prefix('student')->name('student.')->middleware('role:user')->group(function () {
        Route::get('/assessments', [AssessmentController::class, 'studentIndex'])->name('assessments.index');
        Route::get('/assessments/{assessment}', [AssessmentController::class, 'studentShow'])->name('assessments.show');
        Route::post('/assessments/{assessment}/submit', [AssessmentController::class, 'studentSubmit'])
            ->middleware('throttle:20,1')->name('assessments.submit');
    });

    Route::prefix('counselor')->name('assessments.')->middleware('role:counselor,admin')->group(function () {
        Route::get('/assessments', [AssessmentController::class, 'index'])->name('index');
        Route::get('/assessments/create', [AssessmentController::class, 'create'])->name('create');
        Route::post('/assessments', [AssessmentController::class, 'store'])->name('store');
    });
});
