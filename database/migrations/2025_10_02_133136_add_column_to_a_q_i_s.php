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
        Schema::table('a_q_i_s', function (Blueprint $table) {
            $table->enum('type', ['whatsapp', 'email'])->after('range');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('a_q_i_s', function (Blueprint $table) {
            $table->dropColumn('type', ['whatsapp', 'email']);
        });
    }
};
