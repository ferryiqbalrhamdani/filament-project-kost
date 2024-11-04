<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaksi extends Model
{
    protected $fillable = [
        'pembayaran_id',
        'jenis_transaksi',
        'saldo',
        'catatan',
    ];

    public function pembayaran()
    {
        return $this->belongsTo(Pembayaran::class);
    }
}