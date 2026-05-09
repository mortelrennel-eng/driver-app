<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        $email = 'robertgarcia.owner@gmail.com';

        if (DB::table('users')->where('email', $email)->exists()) {
            // Update to super_admin if already exists
            DB::table('users')->where('email', $email)->update([
                'role'            => 'super_admin',
                'approval_status' => 'approved',
                'is_active'       => 1,
                'is_verified'     => 1,
            ]);
            $this->command->info('Super Admin account already exists — role updated to super_admin.');
            return;
        }

        DB::table('users')->insert([
            'full_name'       => 'Robert Garcia',
            'first_name'      => 'Robert',
            'middle_name'     => null,
            'last_name'       => 'Garcia',
            'suffix'          => null,
            'email'           => $email,
            'username'        => 'super_admin-robert',
            'password'        => Hash::make('@RobertOwner2026'),
            'password_hash'   => Hash::make('@RobertOwner2026'),
            'role'            => 'super_admin',
            'approval_status' => 'approved',
            'is_active'       => 1,
            'is_verified'     => 1,
            'phone_number'    => '09000000000',
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);

        $this->command->info('Super Admin account created: ' . $email);
    }
}
