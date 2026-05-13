<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        // Add slug as nullable first so the backfill can run before NOT NULL is enforced
        Schema::table('companies', function (Blueprint $table) {
            $table->string('slug')->nullable()->after('name');
        });

        // Backfill: canonical short slugs for the two known companies, generic Str::slug for the rest
        $canonicalSlugs = [
            'SATEKI TRADING LIMITED' => 'sateki',
            'KIMUJE'                 => 'kimuje',
        ];

        foreach (DB::table('companies')->get() as $company) {
            $slug = $canonicalSlugs[$company->name] ?? Str::slug($company->name);
            DB::table('companies')->where('id', $company->id)->update(['slug' => $slug]);
        }

        // Now enforce NOT NULL and uniqueness
        Schema::table('companies', function (Blueprint $table) {
            $table->string('slug')->nullable(false)->unique()->change();
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn('slug');
        });
    }
};
