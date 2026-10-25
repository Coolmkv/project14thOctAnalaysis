<?php

use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AssessmentController;
use App\Http\Controllers\AuthController;
use App\Http\Middleware\EnsurePasswordIsChanged;
use App\Models\User;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $user = request()->user();

    if ($user) {
        if ($user->hasRole(User::ROLE_ADMIN)) {
            return redirect()->route('admin.dashboard');
        }
        if ($user->hasRole(User::ROLE_GROUP_ADMIN)) {
            return redirect()->route('group-admin.dashboard');
        }
        if ($user->hasRole(User::ROLE_COUNSELOR)) {
            return redirect()->route('assessments.index');
        }

        return redirect()->route('student.assessments.index');
    }

    return view('home');
})->name('home');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->defaults('audience', 'student')->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1')->name('login.store');
    Route::get('/counselor/login', [AuthController::class, 'showLogin'])->defaults('audience', 'counselor')->name('counselor.login');
    Route::post('/counselor/login', [AuthController::class, 'login'])->middleware('throttle:10,1')->name('counselor.login.store');
    Route::get('/admin/login', [AuthController::class, 'showLogin'])->defaults('audience', 'admin')->name('admin.login');
    Route::post('/admin/login', [AuthController::class, 'login'])->middleware('throttle:10,1')->name('admin.login.store');
    Route::get('/group-admin/login', [AuthController::class, 'showLogin'])->defaults('audience', 'group_admin')->name('group-admin.login');
    Route::post('/group-admin/login', [AuthController::class, 'login'])->middleware('throttle:10,1')->name('group-admin.login.store');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:5,1')->name('register.store');
});

Route::middleware(['auth', EnsurePasswordIsChanged::class])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/account/password', [AuthController::class, 'showChangePassword'])->name('account.password.edit');
    Route::put('/account/password', [AuthController::class, 'changePassword'])->name('account.password.update');

    Route::prefix('admin')->name('admin.')->middleware('role:admin')->group(function () {
        Route::get('/', [AdminDashboardController::class, 'adminDashboard'])->name('dashboard');
        Route::post('/users', [AdminDashboardController::class, 'createUser'])->name('users.store');
        Route::post('/institutes', [AdminDashboardController::class, 'createInstitute'])->name('institutes.store');
        Route::post('/students/import', [AdminDashboardController::class, 'importStudents'])
            ->middleware('throttle:5,1')->name('students.import');
    });

    Route::prefix('group-admin')->name('group-admin.')->middleware('role:group_admin')->group(function () {
        Route::get('/', [AdminDashboardController::class, 'groupAdminDashboard'])->name('dashboard');
        Route::post('/institutes/{institute}/students/import', [AdminDashboardController::class, 'importGroupStudents'])
            ->middleware('throttle:5,1')->name('students.import');
    });

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
        Route::get('/assessments/{assessment}/results', [AssessmentController::class, 'results'])->name('results');
    });
});
