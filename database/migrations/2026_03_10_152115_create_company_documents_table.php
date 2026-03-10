<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_documents', function (Blueprint $table) {
            $table->id();
            $table->string('title'); // Nama Dokumen (Cth: SOP Desain, Aturan Jam Kerja)
            $table->string('category'); // Kategori (SOP, Jobdesk, Peraturan, dll)
            $table->json('target_roles'); // Mesin Sensor Kasta: Akan berisi daftar jabatan yang boleh membaca ini, atau "Semua Karyawan"
            $table->string('file_attachment')->nullable(); // Jika Bos upload file PDF langsung
            $table->string('drive_link')->nullable(); // Jika Bos pakai link Google Drive
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_documents');
    }
};