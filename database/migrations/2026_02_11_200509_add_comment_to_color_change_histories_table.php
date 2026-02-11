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
        Schema::table('color_change_histories', function (Blueprint $table) {
            $table->string('comment', 100)->default('Sin comentarios')->after('new_color');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('color_change_histories', function (Blueprint $table) {
            $table->dropColumn('comment');
        });
    }
};
