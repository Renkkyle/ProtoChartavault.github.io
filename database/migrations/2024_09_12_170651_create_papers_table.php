<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePapersTable extends Migration
{
    public function up()
    {
        Schema::create('papers', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('adviser'); // Add adviser column
            $table->string('author');
            $table->text('emails'); // Add emails column
            $table->longText('abstract');
            $table->string('file_path'); // Path to the file in storage
            $table->string('authors')->nullable(); // Ensure 'authors' is nullable
            // Add new columns for status and remarks
            $table->string('status')->default('Pending'); // Status column
            $table->text('remarks')->nullable(); // Remarks column
            
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('papers');
    }
}
