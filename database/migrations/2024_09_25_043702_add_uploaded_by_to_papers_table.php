<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('papers', function (Blueprint $table) {
            $table->unsignedBigInteger('uploaded_by')->nullable(); // Add the uploaded_by column
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('papers', function (Blueprint $table) {
            $table->dropColumn('uploaded_by'); // Drop the column if rolling back
        });
    }
};
