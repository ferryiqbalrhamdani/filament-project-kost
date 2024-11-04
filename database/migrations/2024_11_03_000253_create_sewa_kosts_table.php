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
        Schema::create('sewa_kosts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cabang_kost_id')->constrained('cabang_kosts')->onDelete('cascade');
            $table->foreignId('biaya_kost_id')->constrained('biaya_kosts')->onDelete('cascade');
            $table->string('nama_penyewa')->nullable();
            $table->date('tgl_sewa')->nullable();
            $table->date('tgl_sewa_akhir')->nullable();
            $table->string('lama_sewa')->nullable();
            $table->decimal('total_biaya', 10, 2)->nullable();
            $table->string('status')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sewa_kosts');
    }
};
