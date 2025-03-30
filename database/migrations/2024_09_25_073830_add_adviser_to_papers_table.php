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
        if (!Schema::hasColumn('papers', 'adviser')) {
            Schema::table('papers', function (Blueprint $table) {
                $table->string('adviser')->after('title');
            });
        }
    }

    public function down()
    {
        Schema::table('papers', function (Blueprint $table) {
            $table->dropColumn('adviser');
        });
    }
};
