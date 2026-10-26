<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contact_cms', function (Blueprint $table) {
            $table->id();
            $table->json('hero');       // eyebrow, title, titleHighlight, lead
            $table->string('banner_image')->nullable(); // Image path
            $table->json('info');       // Array of { id, label, value, icon }
            $table->json('faq');        // { eyebrow, title, items: [{ id, question, answer }] }
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_cms');
    }
};
