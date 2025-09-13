<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class KeputusanHeader extends Model
{
    protected $table = 'keputusan_header';

    protected $fillable = [
        'nomor',             // Nomor SK
        'tanggal_asli',      // Tanggal SK
        'tentang',           // Judul ringkas SK
        'menimbang',         // array json (a, b, c, ...)
        'mengingat',         // array json (1, 2, 3, ...)
        'menetapkan',        // array json ("KESATU", "KEDUA", ...)
        'memutuskan',
        'tembusan',          // string/text, tembusan SK
        'status_surat',      // draft|pending|disetujui
        'dibuat_oleh',       // user_id pembuat
        'penandatangan',     // user_id dekan/WD
    ];

    // Cast supaya json langsung array
    protected $casts = [
        'menimbang'   => 'array',
        'mengingat'   => 'array',
        'menetapkan'  => 'array',   // Tambahan: jika ingin MENETAPKAN per KESATU dst
        'tanggal_surat' => 'date',
        'memutuskan'    => 'array',
    ];

    // Pembuat SK
    public function pembuat()
    {
        return $this->belongsTo(User::class, 'dibuat_oleh');
    }

    // Penandatangan SK (Dekan/Wakil Dekan)
    public function penandatanganUser()
    {
        return $this->belongsTo(User::class, 'penandatangan');
    }

    // Semua versi revisi SK
    public function versi()
    {
        return $this->hasMany(KeputusanVersi::class, 'header_id');
    }

    // Semua penerima SK
    public function penerima()
    {
        return $this->hasMany(KeputusanPenerima::class, 'keputusan_id');
    }
}
