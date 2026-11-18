<?php

use App\Http\Controllers\AuditController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FacilityController;
use App\Http\Controllers\InmateController;
use App\Http\Controllers\PersonnelController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\TransferController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VisitorController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| PMS Web Routes — Phase B2: Authentication, Account Provisioning, and RBAC
|--------------------------------------------------------------------------
*/

Route::get('/', fn () => redirect()->route('dashboard'));

// -----------------------------------------------------------------------
// Authentication routes
// -----------------------------------------------------------------------
Route::prefix('auth')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('/login', [AuthController::class, 'loginForm'])->name('login');
        Route::post('/login', [AuthController::class, 'login']);

        Route::get('/forgot-password', [AuthController::class, 'forgotPasswordForm'])->name('password.request');
        Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])->name('password.email');

        Route::get('/reset-password', [AuthController::class, 'resetPasswordForm'])->name('password.reset');
        Route::get('/reset-password/{token}', [AuthController::class, 'resetPasswordForm'])->name('password.reset.token');
        Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');
    });

    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');
});

// -----------------------------------------------------------------------
// Protected Application Routes (Requires Authenticated & Active Account)
// -----------------------------------------------------------------------
Route::middleware(['auth', 'active'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::post('/profile', [ProfileController::class, 'update'])->name('profile.update')->middleware('throttle:10,1');
    Route::post('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password')->middleware('throttle:5,1');

    // Inmates — static routes MUST come before the {id} wildcard
    Route::prefix('inmates')->name('inmates.')->group(function () {
        Route::get('/', [InmateController::class, 'index'])->name('index');
        Route::post('/', [InmateController::class, 'store'])->name('store');
        Route::get('/new', [InmateController::class, 'create'])->name('create');
        Route::get('/export', [InmateController::class, 'export'])->name('export')->middleware('throttle:10,1');
        Route::get('/discharges', [InmateController::class, 'discharges'])->name('discharges');

        Route::get('/{id}', [InmateController::class, 'show'])->name('show')->whereNumber('id');
        Route::get('/{id}/photo', [InmateController::class, 'photo'])->name('photo')->whereNumber('id');
        Route::get('/{id}/edit', [InmateController::class, 'edit'])->name('edit')->whereNumber('id');
        Route::post('/{id}/edit', [InmateController::class, 'update'])->name('update')->whereNumber('id');
        Route::get('/{id}/admissions', [InmateController::class, 'admissions'])->name('admissions')->whereNumber('id');
        Route::get('/{id}/discharge', [InmateController::class, 'discharge'])->name('discharge')->whereNumber('id');
        Route::post('/{id}/discharge', [InmateController::class, 'dischargeStore'])->name('discharge.store')->whereNumber('id');
    });

    // Transfers — static routes before {id} wildcard
    Route::prefix('transfers')->name('transfers.')->group(function () {
        Route::get('/', [TransferController::class, 'index'])->name('index');
        Route::post('/', [TransferController::class, 'store'])->name('store');
        Route::get('/new', [TransferController::class, 'create'])->name('create');
        Route::get('/history', [TransferController::class, 'history'])->name('history');

        Route::get('/{id}', [TransferController::class, 'show'])->name('show')->whereNumber('id');
        Route::get('/{id}/review', [TransferController::class, 'review'])->name('review')->whereNumber('id');
        Route::post('/{id}/review', [TransferController::class, 'reviewStore'])->name('review.store')->whereNumber('id');
        Route::get('/{id}/dispatch', [TransferController::class, 'dispatch'])->name('dispatch')->whereNumber('id');
        Route::post('/{id}/dispatch', [TransferController::class, 'dispatchStore'])->name('dispatch.store')->whereNumber('id');
        Route::get('/{id}/receive', [TransferController::class, 'receive'])->name('receive')->whereNumber('id');
        Route::post('/{id}/receive', [TransferController::class, 'receiveStore'])->name('receive.store')->whereNumber('id');
        Route::post('/{id}/cancel', [TransferController::class, 'cancel'])->name('cancel')->whereNumber('id');
    });

    // Personnel — static routes before {id} wildcard
    Route::prefix('personnel')->name('personnel.')->group(function () {
        Route::get('/', [PersonnelController::class, 'index'])->name('index');
        Route::get('/new', [PersonnelController::class, 'create'])->name('create');
        Route::post('/', [PersonnelController::class, 'store'])->name('store');
        Route::get('/assignments', [PersonnelController::class, 'assignments'])->name('assignments');
        Route::get('/assignments/new', [PersonnelController::class, 'assignmentCreate'])->name('assignment.create');
        Route::post('/assignments', [PersonnelController::class, 'assignmentStore'])->name('assignment.store');
        Route::post('/assignments/{id}/end', [PersonnelController::class, 'assignmentEnd'])->name('assignment.end')->whereNumber('id');

        Route::get('/{id}', [PersonnelController::class, 'show'])->name('show')->whereNumber('id');
        Route::get('/{id}/edit', [PersonnelController::class, 'edit'])->name('edit')->whereNumber('id');
        Route::post('/{id}/edit', [PersonnelController::class, 'update'])->name('update')->whereNumber('id');
        Route::get('/{id}/assignments', [PersonnelController::class, 'assignmentHistory'])->name('assignment.history')->whereNumber('id');
    });

    // Prison Facilities — static routes before {id} wildcard
    Route::prefix('facilities')->name('facilities.')->group(function () {
        Route::get('/', [FacilityController::class, 'index'])->name('index');
        Route::get('/new', [FacilityController::class, 'create'])->name('create');
        Route::post('/', [FacilityController::class, 'store'])->name('store');
        Route::get('/capacity', [FacilityController::class, 'capacity'])->name('capacity');

        Route::get('/{id}', [FacilityController::class, 'show'])->name('show')->whereNumber('id');
        Route::get('/{id}/edit', [FacilityController::class, 'edit'])->name('edit')->whereNumber('id');
        Route::post('/{id}/edit', [FacilityController::class, 'update'])->name('update')->whereNumber('id');
        Route::post('/{id}/capacity', [FacilityController::class, 'capacityStore'])->name('capacity.store')->whereNumber('id');
        Route::get('/{id}/personnel', [FacilityController::class, 'personnelList'])->name('personnel')->whereNumber('id');
    });

    // Visitors — static routes MUST come before the {id} wildcard
    Route::prefix('visitors')->name('visitors.')->group(function () {
        Route::get('/', [VisitorController::class, 'index'])->name('index');
        Route::post('/', [VisitorController::class, 'store'])->name('store');
        Route::get('/new', [VisitorController::class, 'create'])->name('create');
        Route::get('/records', [VisitorController::class, 'records'])->name('records');
        Route::get('/schedule', [VisitorController::class, 'schedule'])->name('schedule');
        Route::post('/schedule', [VisitorController::class, 'visitStore'])->name('visits.store');

        Route::get('/visits/{id}', [VisitorController::class, 'visitShow'])->name('visits.show')->whereNumber('id');
        Route::get('/visits/{id}/review', [VisitorController::class, 'review'])->name('visits.review')->whereNumber('id');
        Route::post('/visits/{id}/review', [VisitorController::class, 'transitionStore'])->name('visits.review.store')->whereNumber('id');
        Route::post('/visits/{id}/schedule', [VisitorController::class, 'transitionStore'])->name('visits.schedule.store')->whereNumber('id');
        Route::get('/visits/{id}/check-in', [VisitorController::class, 'checkIn'])->name('visits.check-in')->whereNumber('id');
        Route::post('/visits/{id}/check-in', [VisitorController::class, 'transitionStore'])->name('visits.check-in.store')->whereNumber('id');
        Route::get('/visits/{id}/check-out', [VisitorController::class, 'checkOut'])->name('visits.check-out')->whereNumber('id');
        Route::post('/visits/{id}/check-out', [VisitorController::class, 'transitionStore'])->name('visits.check-out.store')->whereNumber('id');
        Route::get('/visits/{id}/cancel', [VisitorController::class, 'cancel'])->name('visits.cancel')->whereNumber('id');
        Route::post('/visits/{id}/cancel', [VisitorController::class, 'transitionStore'])->name('visits.cancel.store')->whereNumber('id');

        Route::get('/{id}', [VisitorController::class, 'show'])->name('show')->whereNumber('id');
        Route::get('/{id}/edit', [VisitorController::class, 'edit'])->name('edit')->whereNumber('id');
        Route::post('/{id}/edit', [VisitorController::class, 'update'])->name('update')->whereNumber('id');
        Route::post('/{id}/redact', [VisitorController::class, 'redact'])->name('redact')->whereNumber('id');
    });

    // Reports & Analytics — static routes before any parameterised ones
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::get('/population', [ReportController::class, 'population'])->name('population');
        Route::get('/movement', [ReportController::class, 'movement'])->name('movement');
        Route::get('/transfers', [ReportController::class, 'transfers'])->name('transfers');
        Route::get('/visitation', [ReportController::class, 'visitation'])->name('visitation');
        Route::get('/personnel', [ReportController::class, 'personnel'])->name('personnel');
        Route::get('/occupancy', [ReportController::class, 'occupancy'])->name('occupancy');
        Route::get('/{report}/print', [ReportController::class, 'print'])->name('print')
            ->whereIn('report', ['population', 'movement', 'transfers', 'visitation', 'personnel', 'occupancy']);
        Route::get('/{report}/export', [ReportController::class, 'export'])->name('export')
            ->whereIn('report', ['population', 'movement', 'transfers', 'visitation', 'personnel', 'occupancy'])
            ->middleware('throttle:10,1');
    });

    // Audit Logs — read-only
    Route::prefix('audit')->name('audit.')->group(function () {
        Route::get('/', [AuditController::class, 'index'])->name('index');
        Route::get('/{id}', [AuditController::class, 'show'])->name('show')->whereNumber('id');
    });

    // User Management & Account Provisioning
    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('index');
        Route::post('/', [UserController::class, 'store'])->name('store');
        Route::get('/roles', [UserController::class, 'roles'])->name('roles.index');
        Route::get('/new', [UserController::class, 'create'])->name('create');
        Route::get('/{id}', [UserController::class, 'show'])->name('show')->whereNumber('id');
        Route::get('/{id}/status', [UserController::class, 'status'])->name('status')->whereNumber('id');
        Route::post('/{id}/status', [UserController::class, 'updateStatus'])->name('status.update')->whereNumber('id');
        Route::get('/{id}/assign-role', [UserController::class, 'assignRole'])->name('assign-role')->whereNumber('id');
        Route::post('/{id}/assign-role', [UserController::class, 'updateRole'])->name('role.update')->whereNumber('id');
    });

    // Settings
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
});
