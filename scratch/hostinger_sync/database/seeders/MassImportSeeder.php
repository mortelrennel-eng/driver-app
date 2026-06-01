<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Driver;
use App\Models\Unit;
use App\Models\Maintenance;

class MassImportSeeder extends Seeder
{
    public function run()
    {
        $filePath = "C:\\Users\\bertl\\OneDrive\\Desktop\\Taxi taxi driver (1).csv";
        if (!file_exists($filePath)) {
            $this->command->error("CSV file not found at: {$filePath}");
            return;
        }

        $file = fopen($filePath, 'r');
        $header = fgetcsv($file); // Skip header

        $importedCount = 0;
        $driverMap = []; // Name -> UserID

        DB::beginTransaction();
        try {
            while (($row = fgetcsv($file)) !== FALSE) {
                if (count($row) < 4) continue;

                $setA = trim($row[1]);
                $plate = trim($row[2]);
                $setB = trim($row[3]);
                $remarks = isset($row[5]) ? trim($row[5]) : '';

                if (empty($plate)) continue;

                // 1. Process Drivers (Set A & Set B)
                $driverA_id = $this->getOrCreateDriver($setA, $driverMap);
                $driverB_id = $this->getOrCreateDriver($setB, $driverMap);

                // 2. Determine Unit Status
                $status = 'active';
                if ($setA === 'NAFTM' || $setA === 'NATFM' || strip_tags($setA) === 'NAFTM') {
                    $status = 'maintenance';
                }

                // 3. Create Unit
                $unit = Unit::create([
                    'plate_number' => $plate,
                    'make'         => 'Toyota',
                    'model'        => 'Vios',
                    'year'         => 2018,
                    'status'       => $status,
                    'driver_id'    => ($status === 'active' && $setA !== 'NAD' && $setA !== 'VACANT') ? $driverA_id : null,
                    'secondary_driver_id' => ($status === 'active' && $setA !== 'NAD' && $setA !== 'VACANT' && $driverA_id !== $driverB_id) ? $driverB_id : null,
                    'boundary_rate' => 1100, // Default
                ]);

                // 4. Create Initial Maintenance Record if NAFTM
                if ($status === 'maintenance') {
                    Maintenance::create([
                        'unit_id' => $unit->id,
                        'maintenance_type' => 'preventive', // Must be preventive, corrective, or emergency
                        'description' => 'Initial System Import: ' . ($remarks ?: 'In Shop'),
                        'status' => 'pending',
                        'date_started' => now(),
                        'cost' => 0,
                    ]);
                }

                $importedCount++;
            }

            DB::commit();
            $this->command->info("Successfully imported {$importedCount} units and drivers.");
        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error("Error during import: " . $e->getMessage());
        }

        fclose($file);
    }

    private function getOrCreateDriver($name, &$driverMap)
    {
        $name = trim($name);
        if (empty($name) || in_array($name, ['NAD', 'VACANT', 'NAFTM', 'NATFM'])) {
            return null;
        }

        // Normalize name logic to avoid duplicates
        $normalized = strtolower(str_replace([' ', '.', ','], '', $name));

        if (isset($driverMap[$normalized])) {
            return $driverMap[$normalized];
        }

        // Create Silent User
        $username = 'drv_' . substr(md5($normalized), 0, 8);
        $password_hash = Hash::make('password123');
        $user = User::create([
            'full_name' => $name,
            'name'      => $name,
            'username'  => $username,
            'password'  => $password_hash,
            'password_hash' => $password_hash,
            'role'      => 'driver',
            'is_active' => 1,
        ]);

        // Create Driver Record
        Driver::create([
            'user_id' => $user->id,
            'license_number' => 'IMP-' . strtoupper(substr(md5($name), 0, 10)),
            'license_expiry' => now()->addYear(),
            'contact_number' => 'N/A',
            'address'        => 'N/A',
            'emergency_contact' => 'N/A',
            'emergency_phone'   => 'N/A',
            'hire_date'         => now(),
            'daily_boundary_target' => 1100,
        ]);

        $driverMap[$normalized] = $user->id;
        return $user->id;
    }
}
