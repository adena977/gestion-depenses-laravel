<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('budgets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->decimal('amount', 10, 2);
            $table->enum('period', ['monthly', 'weekly', 'yearly'])->default('monthly');
            $table->date('start_date');
            $table->date('end_date');
            $table->boolean('notifications_enabled')->default(true);
            $table->integer('threshold_percentage')->default(80);
            $table->timestamps();
            
            $table->unique(['user_id', 'category_id', 'period', 'start_date']);
            $table->index(['user_id', 'start_date', 'end_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('budgets');
    }
};