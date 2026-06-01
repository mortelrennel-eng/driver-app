<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Adds dynamic behavior metadata to incident_classifications
     * and sub_classification storage to driver_behavior.
     */
    public function up()
    {
        // ── incident_classifications: Add behavior_mode, sub_options, auto_ban ──
        Schema::table('incident_classifications', function (Blueprint $table) {
            // Controls which form sections appear in the modal
            // 'narrative'  → only narrative text (e.g. Absent, Rude behavior)
            // 'complaint'  → narrative + sub-classification picker (Passenger Complaint)
            // 'traffic'    → narrative + fine amount input (Traffic Violation)
            // 'damage'     → full damage assessment (Accident / Vehicle Damage)
            $table->enum('behavior_mode', ['narrative', 'complaint', 'traffic', 'damage'])
                  ->default('narrative')
                  ->after('icon');

            // JSON array of sub-options (for 'complaint' and 'traffic' modes)
            // e.g. ["Contracting", "Discourtesy", "Overcharging"]
            $table->json('sub_options')->nullable()->after('behavior_mode');

            // If true AND sub_classification matches a contracting/specific sub, auto-ban driver
            $table->boolean('auto_ban_trigger')->default(false)->after('sub_options');

            // Which sub_options value triggers the auto-ban (e.g. "Contracting")
            $table->string('ban_trigger_value')->nullable()->after('auto_ban_trigger');
        });

        // ── driver_behavior: Add sub_classification and traffic_fine_amount ──
        Schema::table('driver_behavior', function (Blueprint $table) {
            $table->string('sub_classification')->nullable()->after('incident_type');
            $table->decimal('traffic_fine_amount', 10, 2)->nullable()->after('sub_classification');
        });
    }

    public function down()
    {
        Schema::table('incident_classifications', function (Blueprint $table) {
            $table->dropColumn(['behavior_mode', 'sub_options', 'auto_ban_trigger', 'ban_trigger_value']);
        });
        Schema::table('driver_behavior', function (Blueprint $table) {
            $table->dropColumn(['sub_classification', 'traffic_fine_amount']);
        });
    }
};

