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
        Schema::create('soal_pertanyaans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('soal_id')->constrained()->cascadeOnDelete();
            $table->string('pertanyaan');
            $table->json('pilihan_json');
            $table->string('jawaban');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('soal_pertanyaans');
    }
};
