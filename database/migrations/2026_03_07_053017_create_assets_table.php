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
    Schema::create('assets', function (Blueprint $table) {
        $table->id();
        $table->foreignId('client_id')->constrained()->cascadeOnDelete();
        $table->string('name'); // Contoh: "Logo Rebranding" / "SEO Monthly v1"
        $table->string('category'); // SEO, Design, Ads, Web, dll.
        $table->string('platform')->nullable(); // Meta Ads, Google Ads, WordPress, Figma
        $table->date('start_date')->nullable();
        $table->date('end_date')->nullable(); // Deadline atau Expiry
        $table->decimal('value', 15, 2)->nullable(); // Nilai Kontrak
        $table->string('status'); // Active, Completed, Expired
        $table->text('credentials_link')->nullable(); // Link Drive/Dashboard
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assets');
    }
};

