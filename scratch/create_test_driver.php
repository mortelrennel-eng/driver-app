<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

use App\Models\User;
use App\Models\Driver;
use App\Models\Staff;
use App\Models\Unit;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

try {
    DB::beginTransaction();

    // 1. Find an active unit
    $unit = Unit::where('status', 'active')->first();
    if (!$unit) {
        echo "No active unit found.\n";
        exit;
    }
    echo "Found Active Unit: " . $unit->plate_number . " (" . $unit->brand . ")\n";

    // 2. Create User
    $email = 'juan@driver.com';
    User::where('email', $email)->forceDelete(); // Clean up if exists

    $user = User::create([
        'name' => 'Juan Dela Cruz',
        'username' => 'juan_driver',
        'full_name' => 'Juan Dela Cruz',
        'first_name' => 'Juan',
        'last_name' => 'Dela Cruz',
        'email' => $email,
        'phone' => '09123456789',
        'password' => Hash::make('password123'),
        'password_hash' => Hash::make('password123'),
        'role' => 'driver',
        'is_active' => true,
        'is_verified' => true,
        'approval_status' => 'approved',
    ]);

    // 3. Create Driver
    $driver = Driver::create([
        'user_id' => $user->id,
        'first_name' => 'Juan',
        'last_name' => 'Dela Cruz',
        'license_number' => 'N/A',
        'license_expiry' => now()->addYears(5),
        'hire_date' => now(),
        'address' => 'N/A',
        'emergency_contact' => 'N/A',
        'emergency_phone' => '09000000000',
        'contact_number' => '09123456789',
        'driver_type' => 'regular',
        'driver_status' => 'available',
    ]);

    // 4. Create Staff
    Staff::create([
        'name' => 'Juan Dela Cruz',
        'role' => 'Driver',
        'phone' => '09123456789',
        'status' => 'active'
    ]);

    // 5. Link to Unit
    $unit->driver_id = $driver->id;
    $unit->save();

    DB::commit();

    echo "SUCCESS!\n";
    echo "Login: " . $email . "\n";
    echo "Password: password123\n";
    echo "Linked to Plate: " . $unit->plate_number . "\n";

} catch (\Exception $e) {
    DB::rollBack();
    echo "ERROR: " . $e->getMessage() . "\n";
}
