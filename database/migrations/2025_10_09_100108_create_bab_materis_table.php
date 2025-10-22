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
        Schema::create('bab_materis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('materi_id')->constrained()->cascadeOnDelete();
            $table->string('judul');
            $table->string('konten');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bab_materis');
    }
};
