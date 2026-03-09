<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('log_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action'); // CREATE, UPDATE, DELETE
            $table->string('model_type'); // Modul: Invoice, Project, dll
            $table->json('changes')->nullable(); // Detail data yang diketik
            
            // Kolom waktu spesifik untuk analisis tajam
            $table->timestamp('entry_timestamp')->nullable(); 
            $table->timestamp('exit_timestamp')->nullable();
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('log_histories');
    }
};