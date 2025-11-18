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
        Schema::table('whatsapp_recipients', function (Blueprint $table) {
            // Check if columns don't exist before adding
            if (!Schema::hasColumn('whatsapp_recipients', 'phone')) {
                $table->string('phone')->unique()->after('id');
            }
            if (!Schema::hasColumn('whatsapp_recipients', 'name')) {
                $table->string('name')->nullable()->after('phone');
            }
            if (!Schema::hasColumn('whatsapp_recipients', 'active')) {
                $table->boolean('active')->default(true)->after('name');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('whatsapp_recipients', function (Blueprint $table) {
            if (Schema::hasColumn('whatsapp_recipients', 'phone')) {
                $table->dropColumn('phone');
            }
            if (Schema::hasColumn('whatsapp_recipients', 'name')) {
                $table->dropColumn('name');
            }
            if (Schema::hasColumn('whatsapp_recipients', 'active')) {
                $table->dropColumn('active');
            }
        });
    }
};
