<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pembayaran extends Model
{
    protected $fillable = [
        'sewa_kost_id',
        'metode_pembayaran_id',
        'akun_bank_id',
        'total_bayar',
        'catatan',
    ];

    public function sewaKost()
    {
        return $this->belongsTo(SewaKost::class);
    }

    public function metodePembayaran()
    {
        return $this->belongsTo(MetodePembayaran::class);
    }
    public function akunBank()
    {
        return $this->belongsTo(AkunBank::class);
    }

    public function transaksi()
    {
        return $this->belongsTo(Transaksi::class, 'pembayaran_id');
    }
}
