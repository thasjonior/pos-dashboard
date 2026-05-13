<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('machines', function (Blueprint $table) {
            $table->foreignId('location_id')->nullable()->after('company_id')->constrained('locations')->nullOnDelete();
            $table->enum('status', ['active', 'inactive', 'maintenance'])->default('active')->after('is_active');
            $table->enum('type', ['terminal', 'mobile'])->default('mobile')->after('status');
        });

        // Backfill status from existing is_active
        DB::table('machines')->where('is_active', true)->update(['status' => 'active']);
        DB::table('machines')->where('is_active', false)->update(['status' => 'inactive']);
    }

    public function down(): void
    {
        Schema::table('machines', function (Blueprint $table) {
            $table->dropForeign(['location_id']);
            $table->dropColumn(['location_id', 'status', 'type']);
        });
    }
};
