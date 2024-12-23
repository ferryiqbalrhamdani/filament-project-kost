<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SewaKost extends Model
{
    protected $fillable = [
        'cabang_kost_id',
        'biaya_kost_id',
        'nama_penyewa',
        'tgl_sewa',
        'tgl_sewa_akhir',
        'lama_sewa',
        'total_biaya',
        'status',
        'status_kamar',
    ];

    public function cabangKost()
    {
        return $this->belongsTo(CabangKost::class);
    }

    public function biayaKost()
    {
        return $this->belongsTo(BiayaKost::class);
    }

    public function pembayaran()
    {
        return $this->hasMany(Pembayaran::class); // Correct relationship type
    }
}
