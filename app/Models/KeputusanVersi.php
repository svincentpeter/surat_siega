<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KeputusanVersi extends Model
{
    protected $table = 'keputusan_versi';

    protected $fillable = [
        'header_id',
        'versi',
        'is_final',
        'konten_json',
        'versi_induk',
        'dibuat_pada',
    ];

    // Cast ke array agar mudah akses
    protected $casts = [
        'konten_json' => 'array',
    ];

    // Relasi ke SK Header
    public function header()
    {
        return $this->belongsTo(KeputusanHeader::class, 'header_id');
    }

    // Relasi ke versi induk
    public function induk()
    {
        return $this->belongsTo(KeputusanVersi::class, 'versi_induk');
    }
}
