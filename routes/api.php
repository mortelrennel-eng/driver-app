<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\UnitController;
use App\Http\Controllers\Api\DriverController;
use App\Http\Controllers\Api\AdditionalModulesController;
use App\Http\Controllers\Api\NotificationController;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Public routes
Route::post('/login', [AuthController::class, 'login']);
Route::post('/verify-device-otp', [AuthController::class, 'verifyDeviceOtp']);
Route::post('/send-device-otp', [AuthController::class, 'sendDeviceOtp']);
Route::post('/forgot-password', [AuthController::class, 'sendResetOtp']);
Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);
Route::post('/driver/register', [\App\Http\Controllers\Api\DriverAppController::class, 'register']);
Route::post('/driver/register/verify-otp', [\App\Http\Controllers\Api\DriverAppController::class, 'verifyRegistrationOtp']);
Route::post('/driver/register/resend-otp', [\App\Http\Controllers\Api\DriverAppController::class, 'resendRegistrationOtp']);
Route::get('/cron/trigger-daily-coding', [NotificationController::class, 'triggerDailyCodingAlerts']);


// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::post('/logout', [AuthController::class, 'logout']);
    
    // Core Resources
    Route::get('/dashboard', [DashboardController::class, 'index']);
    
    // Unified Mobile Notifications & Push Simulations
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::get('/driver/notifications', [NotificationController::class, 'index']); // Mobile app alias
    Route::post('/notifications/dismiss', [NotificationController::class, 'dismiss']);
    Route::post('/notifications/simulate-push', [NotificationController::class, 'simulatePushNotification']);
    Route::post('/notifications/save-token', [NotificationController::class, 'saveToken']);
    Route::post('/driver/notifications/save-token', [NotificationController::class, 'saveToken']); // Mobile app alias

    Route::get('/units', [UnitController::class, 'index']);
    Route::post('/units', [UnitController::class, 'store']);
    Route::get('/units/{id}', [UnitController::class, 'show']);
    Route::put('/units/{id}', [UnitController::class, 'update']);
    Route::delete('/units/{id}', [UnitController::class, 'destroy']);
    Route::get('/drivers', [DriverController::class, 'index']);

    // Archive Management
    Route::get('/archive', [\App\Http\Controllers\Api\ArchiveController::class, 'index']);
    Route::post('/archive/restore/{type}/{id}', [\App\Http\Controllers\Api\ArchiveController::class, 'restore']);
    Route::post('/archive/delete/{type}/{id}', [\App\Http\Controllers\Api\ArchiveController::class, 'forceDelete']);
    
    // Boundary & Financials
    Route::get('/boundaries', [\App\Http\Controllers\Api\BoundaryController::class, 'index']);

    // Live Tracking (GPS)
    Route::get('/live-tracking/units', [\App\Http\Controllers\LiveTrackingController::class, 'getUnitsLive']);
    Route::get('/live-tracking/unit/{id}', [\App\Http\Controllers\LiveTrackingController::class, 'getUnitLocation']);
    Route::post('/live-tracking/engine', [\App\Http\Controllers\LiveTrackingController::class, 'engineControl']);

    // Franchise Case Management
    Route::get('/franchise', [AdditionalModulesController::class, 'franchiseIndex']);
    Route::post('/franchise', [AdditionalModulesController::class, 'franchiseStore']);
    Route::put('/franchise/{id}', [AdditionalModulesController::class, 'franchiseUpdate']);
    Route::delete('/franchise/{id}', [AdditionalModulesController::class, 'franchiseDestroy']);
    Route::post('/franchise/{id}/approve', [AdditionalModulesController::class, 'franchiseApprove']);
    Route::post('/franchise/{id}/reject', [AdditionalModulesController::class, 'franchiseReject']);

    // Office Expenses
    Route::get('/office-expenses', [AdditionalModulesController::class, 'expenseIndex']);
    Route::post('/office-expenses', [AdditionalModulesController::class, 'expenseStore']);
    Route::put('/office-expenses/{id}', [AdditionalModulesController::class, 'expenseUpdate']);
    Route::delete('/office-expenses/{id}', [AdditionalModulesController::class, 'expenseDestroy']);

    // Salaries
    Route::get('/salaries', [AdditionalModulesController::class, 'salaryIndex']);
    Route::post('/salaries', [AdditionalModulesController::class, 'salaryStore']);
    Route::put('/salaries/{id}', [AdditionalModulesController::class, 'salaryUpdate']);
    Route::delete('/salaries/{id}', [AdditionalModulesController::class, 'salaryDestroy']);

    // Staff Records
    Route::get('/staff', [AdditionalModulesController::class, 'staffIndex']);
    Route::post('/staff', [AdditionalModulesController::class, 'staffStore']);
    Route::put('/staff/{id}', [AdditionalModulesController::class, 'staffUpdate']);
    Route::delete('/staff/{id}', [AdditionalModulesController::class, 'staffDestroy']);

    // Coding Management
    Route::get('/coding', [AdditionalModulesController::class, 'codingIndex']);
    Route::post('/coding/update-day', [AdditionalModulesController::class, 'codingUpdateDay']);

    // Unit Profitability & AI DSS
    Route::get('/unit-profitability', [AdditionalModulesController::class, 'profitabilityIndex']);
    Route::get('/unit-profitability/details', [AdditionalModulesController::class, 'profitabilityDetails']);
    Route::get('/unit-profitability/ai-dss', [AdditionalModulesController::class, 'generateAiDss']);

    // Driver Behavior (Incident Management)
    Route::get('/driver-behavior', [AdditionalModulesController::class, 'incidentIndex']);
    Route::post('/driver-behavior', [AdditionalModulesController::class, 'incidentStore']);
    Route::get('/driver-behavior/{id}', [AdditionalModulesController::class, 'incidentShow']);
    Route::put('/driver-behavior/{id}', [AdditionalModulesController::class, 'incidentUpdate']);
    Route::delete('/driver-behavior/{id}', [AdditionalModulesController::class, 'incidentDestroy']);

    // Support Center (Driver Mobile App Endpoints)
    Route::prefix('driver')->group(function () {
        Route::get('/performance', [\App\Http\Controllers\Api\DriverAppController::class, 'performance']);
        Route::get('/vehicle', [\App\Http\Controllers\Api\DriverAppController::class, 'vehicleDetails']);
        Route::get('/earnings', [\App\Http\Controllers\Api\DriverAppController::class, 'earnings']);
        Route::get('/performance-history', [\App\Http\Controllers\Api\DriverAppController::class, 'getPerformanceHistory']);
        Route::get('/boundary-history', [\App\Http\Controllers\Api\DriverAppController::class, 'boundaryHistory']);
        Route::get('/incidents', [\App\Http\Controllers\Api\DriverAppController::class, 'incidents']);
        Route::get('/charges-incentives', [\App\Http\Controllers\Api\DriverAppController::class, 'chargesIncentives']);
        Route::get('/profile', [\App\Http\Controllers\Api\DriverAppController::class, 'getProfile']);
        Route::get('/nearby', [\App\Http\Controllers\Api\DriverAppController::class, 'nearby']);
        Route::get('/feed', [NotificationController::class, 'index']);
        
        Route::post('/rescue', [\App\Http\Controllers\Api\DriverAppController::class, 'requestRescue']);
        Route::post('/save-token', [\App\Http\Controllers\Api\DriverAppController::class, 'saveNotificationToken']);
        Route::post('/location', [\App\Http\Controllers\Api\DriverAppController::class, 'updateLocation']);
        Route::post('/account/delete', [\App\Http\Controllers\Api\DriverAppController::class, 'deleteAccount']);
        Route::post('/change-password', [\App\Http\Controllers\Api\DriverAppController::class, 'changePassword']);
        Route::post('/update-profile', [\App\Http\Controllers\Api\DriverAppController::class, 'updateProfile']);
        Route::post('/upload-document', [\App\Http\Controllers\Api\DriverAppController::class, 'uploadDocuments']);
        
        Route::prefix('notifications')->group(function () {
            Route::post('/save-token', [\App\Http\Controllers\Api\DriverAppController::class, 'saveNotificationToken']);
        });

        Route::prefix('support')->group(function () {
            Route::get('/unread-count', [\App\Http\Controllers\Api\SupportController::class, 'getUnreadCount']);
            Route::get('/tickets', [\App\Http\Controllers\Api\SupportController::class, 'index']);
            Route::post('/tickets', [\App\Http\Controllers\Api\SupportController::class, 'store']);
            Route::get('/messages', [\App\Http\Controllers\Api\SupportController::class, 'getMessages']);
            Route::post('/messages', [\App\Http\Controllers\Api\SupportController::class, 'sendMessage']);
            Route::post('/messages/send', [\App\Http\Controllers\Api\SupportController::class, 'sendMessage']); // Fallback for app
        });
    });


    // Super Admin / Owner Panel
    Route::prefix('super-admin')->group(function () {
        Route::get('/overview', [\App\Http\Controllers\SuperAdminController::class, 'indexJson']);
        Route::get('/audit', [\App\Http\Controllers\SuperAdminController::class, 'loginHistory']);
        Route::post('/staff', [\App\Http\Controllers\SuperAdminController::class, 'storeStaff']);
        Route::post('/users/{id}/approve', [\App\Http\Controllers\SuperAdminController::class, 'approveUser']);
        Route::post('/users/{id}/reject', [\App\Http\Controllers\SuperAdminController::class, 'rejectUser']);
        Route::post('/users/{id}/toggle-disable', [\App\Http\Controllers\SuperAdminController::class, 'toggleDisable']);
        Route::post('/users/{id}/page-access', [\App\Http\Controllers\SuperAdminController::class, 'updatePageAccess']);
        Route::post('/users/{id}/archive', [\App\Http\Controllers\SuperAdminController::class, 'archiveUser']);
        Route::post('/users/{id}/restore', [\App\Http\Controllers\SuperAdminController::class, 'restoreUser']);
        Route::delete('/users/{id}', [\App\Http\Controllers\SuperAdminController::class, 'deleteUser']);
        Route::put('/users/{id}/update', [\App\Http\Controllers\SuperAdminController::class, 'updateUser']);
        Route::post('/archive-password', [\App\Http\Controllers\SuperAdminController::class, 'updateArchivePassword']);
        
        // Roles
        Route::post('/roles', [\App\Http\Controllers\SuperAdminController::class, 'storeRole']);
        Route::delete('/roles/{id}/archive', [\App\Http\Controllers\SuperAdminController::class, 'archiveRole']);
        Route::post('/roles/{id}/restore', [\App\Http\Controllers\SuperAdminController::class, 'restoreRole']);
        Route::delete('/roles/{id}', [\App\Http\Controllers\SuperAdminController::class, 'deleteRole']);
        
        // Incident Classifications
        Route::post('/classifications', [\App\Http\Controllers\SuperAdminController::class, 'storeClassification']);
        Route::delete('/classifications/{id}/archive', [\App\Http\Controllers\SuperAdminController::class, 'archiveClassification']);
        Route::post('/classifications/{id}/restore', [\App\Http\Controllers\SuperAdminController::class, 'restoreClassification']);
        Route::delete('/classifications/{id}', [\App\Http\Controllers\SuperAdminController::class, 'deleteClassification']);
    });
});
