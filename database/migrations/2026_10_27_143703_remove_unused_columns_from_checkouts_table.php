<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. checkouts table se 'state' column remove karein
        Schema::table('checkouts', function (Blueprint $table) {
            if (Schema::hasColumn('checkouts', 'state')) {
                $table->dropColumn('state');
            }
        });

        // 2. checkout_items table se 'selected_color' column remove karein
        Schema::table('checkout_items', function (Blueprint $table) {
            if (Schema::hasColumn('checkout_items', 'selected_color')) {
                $table->dropColumn('selected_color');
            }
        });
    }

    public function down(): void
    {
        // Agar rollback karna ho toh columns wapas add ho jayein
        Schema::table('checkouts', function (Blueprint $table) {
            $table->string('state')->nullable();
        });

        Schema::table('checkout_items', function (Blueprint $table) {
            $table->string('selected_color')->nullable();
        });
    }
};
