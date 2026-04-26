<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->text('specifications')->nullable()->after('amount');
            $table->text('technologies')->nullable()->after('specifications');
            $table->dateTime('deadline')->nullable()->after('technologies');
            // Update status default or allowed values in documentation
            // Existing statuses: blocked, released, refunded
            // New workflow statuses: pending_freelancer, active (replaces blocked), completed, cancelled
        });
    }

    public function down(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->dropColumn(['specifications', 'technologies', 'deadline']);
        });
    }
};
