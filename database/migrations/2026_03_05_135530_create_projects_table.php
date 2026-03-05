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
    Schema::create('projects', function (Blueprint $table) {
        $table->id();
        $table->string('name'); // Misal: "Redesign Sahitya Law Firm"
        $table->string('client_name'); // Kita pakai nama dulu sementara sebelum ada modul Inventory
        $table->enum('type', ['Web Dev', 'SEO', 'UI/UX', 'Retainer']); // Kunci untuk metrik: Profit per Project Type
        $table->enum('status', ['Pipeline', 'Ongoing', 'Completed', 'Canceled']); // Kunci untuk metrik: Pipeline Value & Project Health
        $table->decimal('project_value', 15, 2); // Nilai kontrak
        $table->date('start_date')->nullable();
        $table->date('deadline')->nullable();
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
