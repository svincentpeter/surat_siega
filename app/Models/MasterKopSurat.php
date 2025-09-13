<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterKopSurat extends Model
{
    protected $table = 'master_kop_surat';
    protected $guarded = [];
    protected $casts = [
        'tampilkan_logo_kiri'  => 'boolean',
        'tampilkan_logo_kanan' => 'boolean',
    ];
}
