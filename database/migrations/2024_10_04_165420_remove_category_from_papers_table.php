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
        if (Schema::hasColumn('papers', 'category')) {
            Schema::table('papers', function (Blueprint $table) {
                $table->dropColumn('category');
            });
        }
    }
    
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('papers', function (Blueprint $table) {
            $table->string('category')->nullable();
        });
    }
};
