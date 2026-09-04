<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rentals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('car_id')->constrained('cars');
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('daily_rate', 18, 2);
            $table->integer('total_days');
            $table->decimal('estimated_cost', 18, 2);
            $table->string('status', 20)->default('ACTIVE');
            $table->timestamps();

            $table->index(['car_id', 'status', 'start_date', 'end_date']);
            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rentals');
    }
};
