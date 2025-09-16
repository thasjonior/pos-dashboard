<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
DB::table('clients')->insert([
    [
        'name' => "unknown-client-sateki",
        'phone' => "0700000000",
        'address' => "Ilala",
        'description' => "all receipts generated without clients name or phone number Sateki",
        'created_at' => now(),
        'updated_at' => now()
    ],
    [
        'name' => "unknown-client-kimuje", 
        'phone' => "0711111111",
        'address' => "Ukonga",
        'description' => "all receipts generated without clients name or phone number Kimuje",
        'created_at' => now(),
        'updated_at' => now()
    ]
]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
    }
};
