<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notifikasi extends Model
{
    protected $table = 'notifikasi';
    public $timestamps = false; // tabel pakai 'dibuat_pada' bukan created_at/updated_at

    protected $fillable = [
        'pengguna_id', 'tipe', 'referensi_id', 'pesan', 'dibaca', 'dibuat_pada',
    ];

    protected $casts = [
        'dibaca'      => 'boolean',
        'dibuat_pada' => 'datetime', // <-- bikin otomatis jadi Carbon
    ];
}
