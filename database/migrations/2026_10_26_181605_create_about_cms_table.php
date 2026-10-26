<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('about_cms', function (Blueprint $table) {
            $table->id();
            $table->json('hero');       // eyebrow, title, lead, ctas
            $table->json('stats');      // array of {value, label}
            $table->string('banner_image')->nullable();
            $table->json('story');      // heading, body[], story_image
            $table->json('mission_vision');
            $table->json('cta');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('about_cms');
    }
};
