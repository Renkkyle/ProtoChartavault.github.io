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
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');  
            $table->enum('category', ['technical_support', 'account_management', 'documents', 'general', 'other'])->default('other');
            $table->text('question');  
            $table->text('issue');
            $table->enum('status', ['pending', 'in_progress', 'resolved','responded'])->default('pending'); 
            $table->text('response')->nullable(); 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
