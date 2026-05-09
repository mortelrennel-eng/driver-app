<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $role) {
            $role->id();
            $role->string('name')->unique(); // e.g. 'secretary'
            $role->string('label');         // e.g. 'Secretary'
            $role->string('description')->nullable();
            $role->timestamps();
            $role->softDeletes();
        });

        // Seed initial roles
        $initialRoles = [
            ['name' => 'manager',    'label' => 'Manager'],
            ['name' => 'dispatcher', 'label' => 'Dispatcher'],
            ['name' => 'secretary',  'label' => 'Secretary'],
            ['name' => 'cashier',    'label' => 'Cashier'],
            ['name' => 'encoder',    'label' => 'Encoder'],
        ];

        foreach ($initialRoles as $r) {
            \Illuminate\Support\Facades\DB::table('roles')->insert(array_merge($r, [
                'created_at' => now(),
                'updated_at' => now()
            ]));
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
