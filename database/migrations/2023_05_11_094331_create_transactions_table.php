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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->timestamp('sold_at');
            $table->decimal('total_amount');
        
            $table->foreignUuid('user_id')->nullable()->references('id')->on('users')->nullOnDelete();
            $table->foreignUuid('cashier_id')->nullable()->references('id')->on('cashiers')->nullOnDelete();
        });        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
