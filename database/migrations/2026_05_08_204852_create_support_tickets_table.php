<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('support_tickets', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->foreignId('user_id')->constrained()->onDelete('cascade');
            $blueprint->string('subject');
            $blueprint->text('message');
            $blueprint->string('category')->default('general'); // financial, technical, general, rescue_followup
            $blueprint->string('status')->default('pending'); // pending, in_progress, resolved
            $blueprint->text('admin_reply')->nullable();
            $blueprint->timestamp('replied_at')->nullable();
            $blueprint->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('support_tickets');
    }
};
