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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('message');
            $table->enum('type', ['jadwal', 'peringatan', 'sistem', 'info']);
            $table->boolean('read')->default(false);
            $table->string('related_model')->nullable(); // Misalnya 'lecturer', 'course', 'schedule'
            $table->unsignedBigInteger('related_id')->nullable(); // ID dari model terkait
            $table->string('time')->nullable(); // Untuk tampilan frontend (5 menit yang lalu, dll)
            $table->date('date'); // Tanggal notifikasi
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
