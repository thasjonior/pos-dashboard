<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Step 1: Widen the enum to accept all values (old + new) so backfill succeeds
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['admin', 'super_admin', 'collector', 'other'])->default('admin')->change();
        });

        // Step 2: Backfill — collectors keep 'collector'; all others become 'super_admin'
        DB::table('users')
            ->whereNotNull('machine_name')
            ->where('machine_name', '!=', '')
            ->update(['role' => 'collector']);

        DB::table('users')
            ->where(function ($q) {
                $q->whereNull('machine_name')->orWhere('machine_name', '');
            })
            ->update(['role' => 'super_admin']);

        // Step 3: Remove 'other' from the enum now that no rows use it
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['admin', 'super_admin', 'collector'])->default('admin')->change();
        });
    }

    public function down(): void
    {
        DB::table('users')->where('role', 'super_admin')->update(['role' => 'admin']);

        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['admin', 'collector', 'other'])->default('collector')->change();
        });
    }
};
