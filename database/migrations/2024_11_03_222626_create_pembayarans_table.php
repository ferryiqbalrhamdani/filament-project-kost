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
        Schema::create('pembayarans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sewa_kost_id')->constrained('sewa_kosts')->onDelete('cascade');
            $table->foreignId('metode_pembayaran_id')->nullable()->constrained('metode_pembayarans')->onDelete('cascade');
            $table->foreignId('akun_bank_id')->nullable()->constrained('akun_banks')->onDelete('cascade');
            $table->decimal('total_bayar', 10, 2)->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pembayarans');
    }
};
