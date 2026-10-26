<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('banner_cms', function (Blueprint $table) {
            $table->id();
            $table->string('image')->nullable();
            $table->json('cards'); // Array of { id, label, title, description }
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('banner_cms');
    }
};
