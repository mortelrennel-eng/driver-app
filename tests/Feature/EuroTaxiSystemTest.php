<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Unit;
use App\Models\Driver;
use App\Models\Expense;
use App\Models\SparePart;
use App\Models\Maintenance;
use App\Models\BoundaryRule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class EuroTaxiSystemTest extends TestCase
{
    use DatabaseTransactions; // Wraps all tests in DB transactions, ensuring actual DB safety!

    protected $admin;
    protected $driverUser;
    protected $driverRecord;
    protected $unit;
    protected $sparePart;

    /**
     * Set up unified programmatic testing assets inside the transaction.
     */
    protected function setUp(): void
    {
        parent::setUp();

        // 1. Programmatically create a Test Super Admin
        $this->admin = User::create([
            'first_name' => 'Test',
            'last_name' => 'Admin',
            'full_name' => 'Test Admin',
            'username' => 'test-admin-' . rand(100, 999),
            'email' => 'testadmin' . rand(100, 999) . '@gmail.com',
            'password' => Hash::make('Secret123!'),
            'password_hash' => Hash::make('Secret123!'),
            'role' => 'super_admin',
            'is_active' => true,
            'is_verified' => true,
            'approval_status' => 'approved',
        ]);

        // 2. Programmatically create a Test Driver User & Record
        $this->driverUser = User::create([
            'first_name' => 'Test',
            'last_name' => 'Driver',
            'full_name' => 'Test Driver',
            'username' => 'test-driver-' . rand(100, 999),
            'email' => 'testdriver' . rand(100, 999) . '@gmail.com',
            'password' => Hash::make('Secret123!'),
            'password_hash' => Hash::make('Secret123!'),
            'role' => 'driver',
            'is_active' => true,
            'is_verified' => true,
            'approval_status' => 'approved',
        ]);

        $this->driverRecord = Driver::create([
            'user_id' => $this->driverUser->id,
            'first_name' => 'Test',
            'last_name' => 'Driver',
            'driver_status' => 'available',
            'license_number' => 'ABC-12345678',
        ]);

        // 3. Programmatically create a Test Unit
        $this->unit = Unit::create([
            'plate_number' => 'TST ' . rand(1000, 9999),
            'make' => 'Toyota',
            'model' => 'Vios',
            'year' => 2022,
            'boundary_rate' => 1500.00,
            'status' => 'active',
            'driver_id' => $this->driverRecord->id,
            'current_turn_driver_id' => $this->driverRecord->id,
        ]);

        // 4. Programmatically create a Spare Part
        $this->sparePart = SparePart::create([
            'name' => 'Test Brake Pad',
            'price' => 450.00,
            'stock_quantity' => 10,
            'supplier' => 'Test Parts Co.',
        ]);
    }

    /**
     * ─── 1. SESSION, AUTHENTICATION & MFA FLOWS ──────────────────────────────────
     */
    public function test_authentication_security_blocklist()
    {
        // A. Test block on disabled account
        $disabledUser = User::create([
            'first_name' => 'Disabled',
            'last_name' => 'User',
            'full_name' => 'Disabled User',
            'username' => 'disabled-user',
            'email' => 'disabled@gmail.com',
            'password' => Hash::make('Secret123!'),
            'password_hash' => Hash::make('Secret123!'),
            'role' => 'staff',
            'is_disabled' => true,
            'disable_reason' => 'Temporarily locked due to audit.',
        ]);

        $response = $this->post(route('login'), [
            'email' => 'disabled@gmail.com',
            'password' => 'Secret123!',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertFalse(auth()->check());

        // B. Test block on pending approval account
        $pendingUser = User::create([
            'first_name' => 'Pending',
            'last_name' => 'User',
            'full_name' => 'Pending User',
            'username' => 'pending-user',
            'email' => 'pending@gmail.com',
            'password' => Hash::make('Secret123!'),
            'password_hash' => Hash::make('Secret123!'),
            'role' => 'staff',
            'approval_status' => 'pending',
        ]);

        $response2 = $this->post(route('login'), [
            'email' => 'pending@gmail.com',
            'password' => 'Secret123!',
        ]);

        $response2->assertStatus(302);
        $this->assertFalse(auth()->check());
    }

    public function test_mfa_new_device_otp_workflow()
    {
        // Simulating login from a new device (no browser cookie)
        $response = $this->post(route('login'), [
            'email' => $this->admin->email,
            'password' => 'Secret123!',
        ]);

        // MFA should be required
        $response->assertJson([
            'mfa_required' => true,
            'email' => $this->admin->email,
        ]);

        // Put user ID temporarily in session like AuthController does
        $this->withSession(['mfa_user_id' => $this->admin->id]);

        // Request Device OTP
        $otpResponse = $this->post(route('login.mfa.send'), [
            'method' => 'email'
        ]);
        $otpResponse->assertJson(['success' => true]);

        // Fetch user from DB and check OTP got generated
        $freshUser = User::find($this->admin->id);
        $this->assertNotNull($freshUser->otp_code);
        $this->assertTrue(now()->lt($freshUser->otp_expires_at));

        // Verify Device OTP with a wrong code (should fail)
        $verifyFail = $this->post(route('login.mfa.verify'), [
            'otp' => '000000'
        ]);
        $verifyFail->assertStatus(422);

        // Verify Device OTP with the correct code (should succeed and authenticate)
        $verifySuccess = $this->post(route('login.mfa.verify'), [
            'otp' => $freshUser->otp_code
        ]);
        $verifySuccess->assertJson(['success' => true, 'redirect' => route('dashboard')]);
        $this->assertTrue(auth()->check());
    }

    public function test_registration_memory_verification_safety()
    {
        $regData = [
            'first_name' => 'New',
            'last_name' => 'Staff',
            'email' => 'newstaff' . rand(100, 999) . '@gmail.com',
            'phone_number' => '9123456789',
            'password' => 'Secret123!',
            'password_confirmation' => 'Secret123!',
            'role' => 'staff',
        ];

        // Register action: should put data in session and send mail, but NOT save to DB yet!
        $response = $this->postJson(route('register.submit'), $regData);
        $response->assertJson(['success' => true]);

        // Ensure user is not yet in the DB
        $dbCount = User::where('email', $regData['email'])->count();
        $this->assertEquals(0, $dbCount);

        // Fetch pending registration data from session
        $pending = Session::get('pending_registration');
        $this->assertNotNull($pending);
        $this->assertEquals($regData['email'], $pending['email']);
        $this->assertNotNull($pending['otp_code']);

        // Verify correct OTP: now it must write to DB as pending approval!
        $verifyResponse = $this->post(route('register.verify-otp'), [
            'email' => $regData['email'],
            'otp' => $pending['otp_code'],
        ]);
        $verifyResponse->assertJson(['success' => true]);

        // Confirm database entry now exists with 'pending' approval status
        $freshUser = User::where('email', $regData['email'])->first();
        $this->assertNotNull($freshUser);
        $this->assertEquals('pending', $freshUser->approval_status);
        $this->assertFalse($freshUser->is_active);
    }

    /**
     * ─── 2. DASHBOARD, ANALYTICS & FORECASTS ─────────────────────────────────────
     */
    public function test_dashboard_and_decision_management_loading()
    {
        $this->actingAs($this->admin);

        // Dashboard page should load successfully (200)
        $response = $this->get(route('dashboard'));
        $response->assertStatus(200);

        // Analytics page should load successfully (200)
        $response2 = $this->get(route('analytics.index'));
        $response2->assertStatus(200);

        // Decision Management page should load successfully (200)
        $response3 = $this->get(route('decision-management.index'));
        $response3->assertStatus(200);
    }

    /**
     * ─── 3. CORE ASSETS CRUD & CASCADE SAFETY ────────────────────────────────────
     */
    public function test_unit_crud_and_cascade_security()
    {
        $this->actingAs($this->admin);

        $newPlate = 'ABC ' . rand(1000, 9999);

        // Create unit
        $response = $this->post(route('units.store'), [
            'plate_number' => $newPlate,
            'make' => 'Nissan',
            'model' => 'Almera',
            'year' => 2021,
            'boundary_rate' => 1400.00,
            'purchase_cost' => 500000.00,
            'purchase_date' => now()->toDateString(),
            'status' => 'active',
            'coding_day' => 'Monday',
            'motor_no' => 'MTR' . rand(1000, 9999),
            'chassis_no' => 'CHS' . rand(1000, 9999),
        ]);

        $unitRecord = Unit::where('plate_number', $newPlate)->first();
        $this->assertNotNull($unitRecord);

        // Soft Delete unit
        $deleteResponse = $this->delete(route('units.destroy', $unitRecord->id));
        $this->assertTrue($unitRecord->fresh()->trashed());

        // Check it is listed in Archives
        $archiveResponse = $this->get(route('archive.index'));
        $archiveResponse->assertSee($newPlate);

        // Restore unit
        $restoreResponse = $this->post(route('archive.restore', ['type' => 'unit', 'id' => $unitRecord->id]));
        $this->assertFalse($unitRecord->fresh()->trashed());
    }

    /**
     * ─── 4. FINANCIALS & AUTOMATED INVENTORY REVERSALS ───────────────────────────
     */
    public function test_financial_shortage_boundary_calculation()
    {
        $this->actingAs($this->admin);

        $date = now()->toDateString();

        // Log a boundary collection with shortage
        $response = $this->post(route('boundaries.store'), [
            'action' => 'add_boundary',
            'boundary_amount' => 1500.00,
            'unit_id' => $this->unit->id,
            'driver_id' => $this->driverRecord->id,
            'date' => $date,
            'actual_boundary' => 1200.00, // Unit rate is 1500.00
            'gas_charge' => 0,
            'shortage' => 300.00,
            'excess' => 0,
        ]);

        // Check boundary record
        $boundary = DB::table('boundaries')
            ->where('unit_id', $this->unit->id)
            ->where('date', $date)
            ->first();

        $this->assertNotNull($boundary);
        $this->assertEquals(1200.00, $boundary->actual_boundary);
        $this->assertEquals(300.00, $boundary->shortage);
    }

    public function test_automated_inventory_expense_reversals()
    {
        $this->actingAs($this->admin);

        // Initial stock quantity is 10
        $this->assertEquals(10, $this->sparePart->stock_quantity);

        // 1. Create a "Spare Parts Purchase" expense record -> should INCREMENT stock
        $response = $this->post(route('office-expenses.store'), [
            'category' => 'Spare Parts Purchase',
            'spare_part_id' => (string) $this->sparePart->id,
            'quantity' => 5,
            'unit_price' => 450.00,
            'description' => 'Inventory RESTOCK: 5 Brake pads',
            'amount' => 2250.00,
            'date' => now()->toDateString(),
        ]);

        // Check stock quantity became 15
        $this->sparePart = $this->sparePart->fresh();
        $this->assertEquals(15, $this->sparePart->stock_quantity);

        $expense = Expense::where('category', 'Spare Parts Purchase')->latest()->first();

        // 2. Update the expense via PUT request to trigger reversal
        $updateResponse = $this->put(route('office-expenses.update', $expense->id), [
            'category' => 'Office Supplies', // Changed category away from parts purchase!
            'description' => 'Changed to office paper',
            'amount' => 1500.00,
            'date' => now()->toDateString(),
        ]);

        // Verify stock decremented back by 5 (reverted to 10!)
        $this->sparePart = $this->sparePart->fresh();
        $this->assertEquals(10, $this->sparePart->stock_quantity);
    }

    /**
     * ─── 5. MAINTENANCE WORKFLOWS & VEHICLE LOCKOUTS ──────────────────────────────
     */
    public function test_stolen_missing_vehicle_maintenance_lockout()
    {
        $this->actingAs($this->admin);

        // Flag the test unit as MISSING
        $this->unit->update(['status' => 'missing']);

        // A. Attempt to log maintenance on a missing vehicle (should be blocked)
        $response = $this->from(route('maintenance.index'))->post(route('maintenance.store'), [
            'unit_id' => $this->unit->id,
            'maintenance_type' => 'Change Oil',
            'labor_cost' => 500,
            'odometer_reading' => 55000,
            'date_started' => now()->toDateString(),
            'status' => 'pending',
            'mechanic_name' => ['Juan Dela Cruz'],
            'cost' => 500,
        ]);

        // Check that a redirect with an 'error' flash message is returned
        $response->assertSessionHas('error');
        
        // Ensure no maintenance record was created
        $maintCount = DB::table('maintenance')->where('unit_id', $this->unit->id)->count();
        $this->assertEquals(0, $maintCount);
    }

    /**
     * ─── 6. ACCESS CONTROL & ROLE RESTRICTIONS ────────────────────────────────────
     */
    public function test_page_access_middleware_restrictions()
    {
        // Create a restricted staff user
        $staffUser = User::create([
            'first_name' => 'Restricted',
            'last_name' => 'Staff',
            'full_name' => 'Restricted Staff',
            'username' => 'restricted-staff',
            'email' => 'restricted@gmail.com',
            'password' => Hash::make('Secret123!'),
            'password_hash' => Hash::make('Secret123!'),
            'role' => 'staff',
            'is_active' => true,
            'is_verified' => true,
            'approval_status' => 'approved',
            'allowed_pages' => ['dashboard', 'announcements.*'], // ONLY allowed these pages!
        ]);

        $this->actingAs($staffUser);

        // Can access allowed pages
        $this->get(route('dashboard'))->assertStatus(200);

        // BLOCKED from unauthorized pages (e.g. SuperAdmin dashboard / Unit Management)
        $restrictedResponse = $this->get(route('super-admin.index'));
        $restrictedResponse->assertStatus(403);
    }

    /**
     * ─── 7. DATABASE INTEGRITY & CONCURRENCY RACE PROTECTION ──────────────────────
     */
    public function test_boundary_unique_integrity_constraint()
    {
        $this->actingAs($this->admin);

        $date = now()->toDateString();

        // 1. Create first boundary log (succeeds)
        $this->post(route('boundaries.store'), [
            'action' => 'add_boundary',
            'boundary_amount' => 1500.00,
            'unit_id' => $this->unit->id,
            'driver_id' => $this->driverRecord->id,
            'date' => $date,
            'actual_boundary' => 1500.00,
        ]);

        $this->assertEquals(1, DB::table('boundaries')->where('unit_id', $this->unit->id)->where('date', $date)->whereNull('deleted_at')->count());

        // 2. Create second boundary log on same date/unit (fails duplicate validation check)
        $response2 = $this->post(route('boundaries.store'), [
            'action' => 'add_boundary',
            'boundary_amount' => 1500.00,
            'unit_id' => $this->unit->id,
            'driver_id' => $this->driverRecord->id,
            'date' => $date,
            'actual_boundary' => 1500.00,
        ]);

        $response2->assertSessionHas('error', 'Boundary record already exists for this unit and date');

        // 3. Bypass controller validation and try inserting directly to DB to simulate race condition.
        // It must throw a QueryException due to our active_date unique index constraint!
        $this->expectException(\Illuminate\Database\QueryException::class);
        DB::table('boundaries')->insert([
            'unit_id' => $this->unit->id,
            'driver_id' => $this->driverRecord->id,
            'date' => $date,
            'boundary_amount' => 1500.00,
            'actual_boundary' => 1500.00,
            'shortage' => 0.00,
            'excess' => 0.00,
            'status' => 'paid',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
