<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KlasifikasiSurat extends Model
{
    use HasFactory;

    /**
     * Nama tabel yang terkait dengan model.
     *
     * @var string
     */
    // AWAL KODE PERBAIKAN
    protected $table = 'klasifikasi_surat';
    // AKHIR KODE PERBAIKAN
}