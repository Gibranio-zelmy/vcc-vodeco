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
    Schema::create('employees', function (Blueprint $table) {
        $table->id();
        $table->string('name'); 
        $table->string('role'); // Misal: 'UI/UX Designer', 'Web Developer', 'SEO Specialist'
        $table->decimal('base_salary', 15, 2); // Kunci utama menghitung Burn Rate bulanan
        $table->date('join_date')->nullable();
        $table->date('contract_end_date')->nullable(); // Radar retensi personel
        $table->enum('status', ['Active', 'Resigned', 'Terminated'])->default('Active');
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
