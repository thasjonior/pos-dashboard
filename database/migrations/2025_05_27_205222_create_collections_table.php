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
        Schema::create('collections', function (Blueprint $table) {
            $table->id();
            $table->string('receipt_id');
            $table->string('date');
            $table->double('amount')->default(0);
            $table->text('notes')->nullable();
            $table->foreignId('machine_id')->constrained('machines')->onDelete('cascade');
            $table->string('client_name')->nullable();
            $table->foreignId('client_id')->nullable()->constrained('clients')->onDelete('set null')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('collections');
    }
};
