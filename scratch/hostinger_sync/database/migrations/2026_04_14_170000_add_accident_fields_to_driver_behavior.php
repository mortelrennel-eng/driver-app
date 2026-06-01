<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('driver_behavior', function (Blueprint $table) {
            $table->date('incident_date')->nullable()->after('timestamp');
            $table->string('third_party_name')->nullable()->after('description');
            $table->string('third_party_vehicle')->nullable()->after('third_party_name');
            $table->decimal('own_unit_damage_cost', 10, 2)->default(0)->after('third_party_vehicle');
            $table->decimal('third_party_damage_cost', 10, 2)->default(0)->after('own_unit_damage_cost');
            $table->boolean('is_driver_fault')->default(false)->after('third_party_damage_cost');
            $table->decimal('total_charge_to_driver', 10, 2)->default(0)->after('is_driver_fault');
            $table->enum('charge_status', ['none', 'pending', 'paid', 'waived'])->default('none')->after('total_charge_to_driver');
            $table->string('updated_at')->nullable()->after('created_at');
        });
    }

    public function down()
    {
        Schema::table('driver_behavior', function (Blueprint $table) {
            $table->dropColumn([
                'incident_date', 'third_party_name', 'third_party_vehicle',
                'own_unit_damage_cost', 'third_party_damage_cost', 'is_driver_fault',
                'total_charge_to_driver', 'charge_status', 'updated_at'
            ]);
        });
    }
};
