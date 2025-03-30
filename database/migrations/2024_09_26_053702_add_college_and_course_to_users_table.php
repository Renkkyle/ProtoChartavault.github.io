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
        Schema::table('users', function (Blueprint $table) {
            // Check if columns already exist before adding to avoid duplicate errors
            if (!Schema::hasColumn('users', 'college')) {
                $table->string('college')->after('role');
            }
            if (!Schema::hasColumn('users', 'course')) {
                $table->string('course')->after('college');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['college', 'course']);
        });
    }
};