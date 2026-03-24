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
        Schema::create('kid_user', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->enum('access_type', ['full', 'readonly'])->default('full');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
        
            // clé étrangére kid_id et user_id
            $table->foreignId('kid_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // Contrainte d'unicité
            $table->unique(['kid_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kid_user');
    }
};
