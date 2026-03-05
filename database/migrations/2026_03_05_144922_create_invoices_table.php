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
    Schema::create('invoices', function (Blueprint $table) {
        $table->id();
        $table->foreignId('client_id')->constrained()->cascadeOnDelete();
        $table->string('invoice_number')->unique();
        $table->decimal('amount', 15, 2);
        $table->date('issue_date');
        $table->date('due_date'); // Kunci utama radar AR Aging (Piutang Jatuh Tempo)
        $table->enum('status', ['Unpaid', 'Paid', 'Overdue'])->default('Unpaid');
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
