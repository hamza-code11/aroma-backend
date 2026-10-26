<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hero_cms', function (Blueprint $table) {
            $table->id();
            $table->string('label')->nullable();
            $table->string('title');
            $table->text('description')->nullable();
            
            // Primary Button
            $table->string('primary_btn_label')->nullable();
            $table->string('primary_btn_link')->nullable();
            
            // Secondary Button
            $table->string('secondary_btn_label')->nullable();
            $table->string('secondary_btn_link')->nullable();
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hero_cms');
    }
};