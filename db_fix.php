<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Database\Schema\Blueprint;

echo "Clearing Cache...\n";
Artisan::call('config:clear');
Artisan::call('cache:clear');

echo "Current DB Name: " . DB::connection()->getDatabaseName() . "\n";

if (!Schema::hasTable('login_audit')) {
    echo "Table 'login_audit' missing. Creating...\n";
    Schema::create('login_audit', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('user_id')->nullable();
        $table->string('user_name')->nullable();
        $table->string('user_email')->nullable();
        $table->string('user_role')->nullable();
        $table->string('action', 255);
        $table->string('ip_address', 45)->nullable();
        $table->text('user_agent')->nullable();
        $table->string('notes')->nullable();
        $table->timestamp('created_at')->useCurrent();

        $table->index(['user_id', 'action']);
        $table->index('created_at');
    });
    echo "Table 'login_audit' created successfully.\n";
} else {
    echo "Table 'login_audit' already exists.\n";
}
