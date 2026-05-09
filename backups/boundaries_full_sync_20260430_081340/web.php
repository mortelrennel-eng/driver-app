<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\BoundaryController;
use App\Http\Controllers\MaintenanceController;
use App\Http\Controllers\CodingController;
use App\Http\Controllers\DriverBehaviorController;
use App\Http\Controllers\DriverManagementController;
use App\Http\Controllers\OfficeExpenseController;
use App\Http\Controllers\SalaryController;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\LiveTrackingController;
use App\Http\Controllers\UnitProfitabilityController;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\DecisionManagementController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\GitHubAuthController;
use App\Http\Controllers\GitHubIntegrationController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\MyAccountController;
use App\Http\Controllers\ArchiveController;
use App\Http\Controllers\BoundarySettingsController;
use App\Http\Controllers\SparePartController;
use App\Http\Controllers\SuperAdminController;
use App\Http\Controllers\SupplierController;

// ─── Auth Routes ───────────────────────────────────────
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
Route::post('/login/mfa/send', [AuthController::class, 'sendDeviceOtp'])->name('login.mfa.send');
Route::post('/login/mfa/verify', [AuthController::class, 'verifyDeviceOtp'])->name('login.mfa.verify');
Route::post('/register', [AuthController::class, 'register'])->name('register.submit');
Route::get('/logout', [AuthController::class, 'logout'])->name('logout');


// ─── My Account Routes ───────────────────────────────────
Route::middleware(['auth'])->group(function () {
    Route::get('/my-account', [MyAccountController::class, 'index'])->name('my-account');
    Route::post('/my-account/update-profile', [MyAccountController::class, 'updateProfile'])->name('my-account.update-profile');
    Route::post('/my-account/update-profile-image', [MyAccountController::class, 'updateProfileImage'])->name('my-account.update-profile-image');
    Route::post('/my-account/change-password', [MyAccountController::class, 'changePassword'])->name('my-account.change-password');
    Route::post('/my-account/forgot-password', [MyAccountController::class, 'forgotPassword'])->name('my-account.forgot-password');
});

// ─── Forgot Password Routes ────────────────────────────
Route::post('/forgot-password/send-otp', [AuthController::class, 'sendResetOtp'])->name('forgot-password.send-otp');
Route::post('/forgot-password/send-sms-otp', [AuthController::class, 'sendSmsResetOtp'])->name('forgot-password.send-sms-otp');
Route::post('/forgot-password/verify-otp', [AuthController::class, 'verifyResetOtp'])->name('forgot-password.verify-otp');
Route::post('/forgot-password/reset', [AuthController::class, 'resetPassword'])->name('forgot-password.reset');
Route::post('/check-availability', [AuthController::class, 'checkAvailability'])->name('check-availability');
Route::post('/register/verify-otp', [AuthController::class, 'verifyRegistrationOtp'])->name('register.verify-otp');
Route::post('/register/resend-otp', [AuthController::class, 'resendRegistrationOtp'])->name('register.resend-otp');

// ─── GitHub OAuth Routes ───────────────────────────────
Route::get('/auth/github', [GitHubAuthController::class, 'redirectToGitHub'])->name('auth.github');
Route::get('/auth/github/callback', [GitHubAuthController::class, 'handleGitHubCallback'])->name('auth.github.callback');

// Real-time dashboard data
Route::get('/api/dashboard/realtime', [DashboardController::class, 'getRealTimeData'])->middleware('auth');
Route::get('/api/revenue-trend', [DashboardController::class, 'getRevenueTrend'])->middleware('auth');
Route::get('/api/units-overview', [DashboardController::class, 'getUnitsOverview'])->middleware('auth');
Route::get('/api/daily-boundary-collections', [DashboardController::class, 'getDailyBoundaryCollections'])->middleware('auth');
Route::get('/api/net-income-details', [DashboardController::class, 'getNetIncomeDetails'])->middleware('auth');
Route::get('/api/maintenance-units', [DashboardController::class, 'getMaintenanceUnits'])->middleware('auth');
Route::get('/api/active-drivers', [DashboardController::class, 'getActiveDrivers'])->middleware('auth');
Route::get('/api/coding-units', [DashboardController::class, 'getCodingUnits'])->middleware('auth');

    // ─── Protected Routes ──────────────────────────────────
Route::middleware(['auth', 'page_access'])->group(function () {
    // ─── NEW: Incident Management (High Priority Routes) ────────────────
    Route::get('/api/incidents/{id}/details', [DriverBehaviorController::class, 'show'])->name('driver-behavior.show');
    Route::match(['post', 'put'], '/api/incidents/{id}/update', [DriverBehaviorController::class, 'update'])->name('driver-behavior.update');
    Route::match(['post', 'delete'], '/api/incidents/{id}/archive', [DriverBehaviorController::class, 'destroy'])->name('driver-behavior.archive');

    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Units - Specific routes MUST come before resource (to prevent /units/{id} catching them)
    Route::get('/units/flagged', [UnitController::class, 'getFlaggedUnits'])->name('units.flagged');
    Route::get('/units/details', [UnitController::class, 'getDetails'])->name('units.details');
    Route::get('/units/details-html', [UnitController::class, 'getDetailsHtml'])->name('units.detailsHtml');
    Route::post('/units/toggle-status', [UnitController::class, 'toggleStatus'])->name('units.toggle-status');
    Route::get('/units/quick-stats', [UnitController::class, 'quickStats'])->name('units.quick-stats');
    Route::get('/units/print', [UnitController::class, 'printPdf'])->name('units.print');
    Route::resource('units', UnitController::class);

    // Boundaries Resource Routes
    Route::resource('boundaries', BoundaryController::class);

    // Maintenance Resource Routes
    Route::resource('maintenance', MaintenanceController::class);
    Route::get('/maintenance/{id}/parts', [MaintenanceController::class, 'getParts'])->name('maintenance.parts');
    Route::post('/maintenance/{id}/toggle-complete', [MaintenanceController::class, 'toggleComplete'])->name('maintenance.toggle-complete');
    Route::post('/maintenance/{id}/toggle-in-progress', [MaintenanceController::class, 'toggleInProgress'])->name('maintenance.toggle-in-progress');

    // Coding Management
    Route::get('/coding', [CodingController::class, 'index'])->name('coding.index');
    Route::get('/coding/violations', [CodingController::class, 'violations'])->name('coding.violations');
    Route::get('/coding/suggestions', [CodingController::class, 'suggestions'])->name('coding.suggestions');
    Route::resource('coding-rules', CodingController::class)->except(['show', 'edit']);
    Route::post('/coding/update-day', [CodingController::class, 'updateCodingDay'])->name('coding.update-day');

    // Driver Behavior Dashboard & Incidents
    Route::get('/driver-behavior/statistics', [DriverBehaviorController::class, 'getStatistics'])->name('driver-behavior.statistics');
    Route::get('/driver-behavior/driver/{id}', [DriverBehaviorController::class, 'getDriverPerformance'])->name('driver-behavior.driver-performance');
    Route::post('/driver-behavior/release-incentive', [DriverBehaviorController::class, 'releaseIncentive'])->name('driver-behavior.release-incentive');
    
    Route::get('/driver-behavior', [DriverBehaviorController::class, 'index'])->name('driver-behavior.index');
    Route::post('/driver-behavior', [DriverBehaviorController::class, 'store'])->name('driver-behavior.store');

    // Driver Management Resource Routes
    Route::resource('driver-management', DriverManagementController::class);
    Route::post('/driver-management/upload-documents/{id}', [DriverManagementController::class, 'uploadDocuments'])->name('driver-management.upload-documents');

    // Office Expenses Resource Routes
    Route::resource('office-expenses', OfficeExpenseController::class);
    Route::post('/office-expenses/approve/{id}', [OfficeExpenseController::class, 'approve'])->name('office-expenses.approve');
    Route::post('/office-expenses/reject/{id}', [OfficeExpenseController::class, 'reject'])->name('office-expenses.reject');

    // Salary Management
    Route::get('/salary', [SalaryController::class, 'index'])->name('salary.index');
    Route::resource('salaries', SalaryController::class)->only(['store', 'update', 'destroy']);
    Route::get('/salary/report', [SalaryController::class, 'monthlyReport'])->name('salary.report');

    // Analytics
    Route::get('/analytics', [AnalyticsController::class, 'index'])->name('analytics.index');

    // Activity Logs
    Route::get('/activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index');

    // Live Tracking
    Route::get('/live-tracking', [LiveTrackingController::class, 'index'])->name('live-tracking.index');
    Route::get('/live-tracking/unit/{id}', [LiveTrackingController::class, 'getUnitLocation'])->name('live-tracking.unit-location');
    Route::get('/live-tracking/units-live', [LiveTrackingController::class, 'getUnitsLive'])->name('live-tracking.units-live');
    Route::get('/live-tracking/unit-mileage/{id}', [LiveTrackingController::class, 'getUnitMileage'])->name('live-tracking.mileage');
    Route::post('/live-tracking/engine-control', [LiveTrackingController::class, 'engineControl'])->name('live-tracking.engine-control');

    // Unit Profitability
    Route::get('/unit-profitability', [UnitProfitabilityController::class, 'index'])->name('unit-profitability.index');
    Route::get('/unit-profitability/details', [UnitProfitabilityController::class, 'getDetails'])->name('unit-profitability.details');

    // Staff Records
    Route::resource('staff', StaffController::class);

    // Decision Management Resource Routes
    Route::resource('decision-management', DecisionManagementController::class);
    Route::post('/decision-management/approve/{id}', [DecisionManagementController::class, 'approve'])->name('decision-management.approve');
    Route::post('/decision-management/reject/{id}', [DecisionManagementController::class, 'reject'])->name('decision-management.reject');

    // Notifications (AJAX)
    Route::post('/notifications/dismiss', [NotificationController::class, 'dismissAlert'])->name('notifications.dismiss');

    // ─── GitHub Integration Routes ─────────────────────
    Route::get('/github', [GitHubIntegrationController::class, 'index'])->name('github.index');
    Route::get('/api/github/stats', [GitHubIntegrationController::class, 'getStats'])->name('github.stats');
    Route::get('/api/github/commits', [GitHubIntegrationController::class, 'getCommits'])->name('github.commits');
    Route::get('/api/github/pulls', [GitHubIntegrationController::class, 'getPullRequests'])->name('github.pulls');
    Route::get('/api/github/issues', [GitHubIntegrationController::class, 'getIssues'])->name('github.issues');
    Route::post('/api/github/issue', [GitHubIntegrationController::class, 'createIssue'])->name('github.create-issue');
    Route::get('/api/github/contributors', [GitHubIntegrationController::class, 'getContributors'])->name('github.contributors');
    Route::get('/api/github/workflow/{workflowId}', [GitHubIntegrationController::class, 'getWorkflowStatus'])->name('github.workflow-status');
    Route::post('/api/github/workflow/trigger', [GitHubIntegrationController::class, 'triggerWorkflow'])->name('github.trigger-workflow');

    // Archive System
    Route::get('/archive', [ArchiveController::class, 'index'])->name('archive.index');
    Route::post('/archive/restore/{type}/{id}', [ArchiveController::class, 'restore'])->name('archive.restore');
    Route::delete('/archive/force-delete/{type}/{id}', [ArchiveController::class, 'forceDelete'])->name('archive.forceDelete');

    // ─── System Settings - Boundary Rules ───────────────────
    Route::get('/boundary-rules', [BoundarySettingsController::class, 'index'])->name('boundary-rules.index');
    Route::post('/boundary-rules', [BoundarySettingsController::class, 'store'])->name('boundary-rules.store');
    Route::put('/boundary-rules/{id}', [BoundarySettingsController::class, 'update'])->name('boundary-rules.update');
    Route::delete('/boundary-rules/{id}', [BoundarySettingsController::class, 'destroy'])->name('boundary-rules.destroy');

    // ─── Spare Parts Management ───────────────────────────
    Route::get('/spare-parts', [SparePartController::class, 'index'])->name('spare-parts.index');
    Route::get('/spare-parts/archived', [SparePartController::class, 'archived'])->name('spare-parts.archived');
    Route::get('/spare-parts/history', [SparePartController::class, 'history'])->name('spare-parts.history');
    Route::post('/spare-parts', [SparePartController::class, 'store'])->name('spare-parts.store');
    Route::post('/spare-parts/restore/{id}', [SparePartController::class, 'restore'])->name('spare-parts.restore');
    Route::delete('/spare-parts/permanent/{id}', [SparePartController::class, 'forceDelete'])->name('spare-parts.forceDelete');
    Route::delete('/spare-parts/{id}', [SparePartController::class, 'destroy'])->name('spare-parts.destroy');

    // ─── Supplier Management ─────────────────────────────
    Route::get('/suppliers', [SupplierController::class, 'index'])->name('suppliers.index');
    Route::post('/suppliers', [SupplierController::class, 'store'])->name('suppliers.store');
    Route::delete('/suppliers/{id}', [SupplierController::class, 'destroy'])->name('suppliers.destroy');
});

// ─── Super Admin Routes (Owner Only) ─────────────────────────────────────────
Route::middleware(['auth', 'super_admin'])->prefix('super-admin')->name('super-admin.')->group(function () {
    Route::get('/', [SuperAdminController::class, 'index'])->name('index');
    Route::post('/approve/{id}', [SuperAdminController::class, 'approveUser'])->name('approve');
    Route::post('/reject/{id}', [SuperAdminController::class, 'rejectUser'])->name('reject');
    Route::post('/toggle-active/{id}', [SuperAdminController::class, 'toggleActive'])->name('toggle-active');
    Route::post('/page-access/{id}', [SuperAdminController::class, 'updatePageAccess'])->name('page-access');
    Route::get('/login-history', [SuperAdminController::class, 'loginHistory'])->name('login-history');
    Route::delete('/users/{id}', [SuperAdminController::class, 'deleteUser'])->name('delete-user');
    Route::post('/users/{id}/restore', [SuperAdminController::class, 'restoreUser'])->name('restore-user');
    Route::post('/users/{id}/reset-password', [SuperAdminController::class, 'resetPassword'])->name('reset-password');
    Route::post('/users/{id}/update-role', [SuperAdminController::class, 'updateRole'])->name('update-role');
});

// ─── Temporary System Sync Route ───────────────────────────
Route::get('/force-sync-db-2026', function() {
    try {
        Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
        return "<h1>Migration Success!</h1><pre>" . Illuminate\Support\Facades\Artisan::output() . "</pre><br><a href='/'>Go to Dashboard</a>";
    } catch (\Exception $e) {
        return "<h1>Migration Failed!</h1><pre>" . $e->getMessage() . "</pre>";
    }
});
