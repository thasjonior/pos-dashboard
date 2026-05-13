<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('audit_logs', function (Blueprint $table) {
            // Remove old minimal columns
            $table->dropColumn(['old_value', 'new_value', 'datetime']);

            // Make user_id nullable (system actions have no user)
            $table->foreignId('user_id')->nullable()->change();

            // Add rich polymorphic + diff columns
            $table->string('auditable_type')->nullable()->after('action');
            $table->unsignedBigInteger('auditable_id')->nullable()->after('auditable_type');
            $table->json('changes')->nullable()->after('auditable_id');
            $table->string('ip_address', 45)->nullable()->after('changes');
            $table->string('user_agent')->nullable()->after('ip_address');
        });

        // Index on created_at for fast date-range filtering
        Schema::table('audit_logs', function (Blueprint $table) {
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::table('audit_logs', function (Blueprint $table) {
            $table->dropIndex(['created_at']);
            $table->dropColumn(['auditable_type', 'auditable_id', 'changes', 'ip_address', 'user_agent']);
            $table->foreignId('user_id')->nullable(false)->change();
            $table->string('old_value')->nullable();
            $table->string('new_value')->nullable();
            $table->string('datetime')->default(now());
        });
    }
};
