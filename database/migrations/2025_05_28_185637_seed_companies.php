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
        DB::table('companies')->insert([
            [
                'name' => 'SATEKI TRADING LIMITED',
                'location' => 'Ilala'
            ],
            [
                "name" => 'KIMUJE',
                'location' => 'Jangwani'
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('companies')->where('name', 'SATEKI TRADING LIMITED')->delete();
        DB::table('companies')->where('name', 'KIMUJE')->delete();

    }
};
