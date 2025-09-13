<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class KeputusanPenerima extends Model
{
    protected $table = 'keputusan_penerima';

    protected $fillable = [
        'keputusan_id',
        'pengguna_id',
        'dibaca',
    ];

    public $timestamps = false;

    // Relasi ke SK
    public function keputusan()
    {
        return $this->belongsTo(KeputusanHeader::class, 'keputusan_id');
    }

    // Relasi ke User
    public function pengguna()
    {
        return $this->belongsTo(User::class, 'pengguna_id');
    }
}
