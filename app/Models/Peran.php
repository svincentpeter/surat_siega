<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Peran extends Model
{
    protected $table = 'peran';
    public $timestamps = false;

    protected $fillable = [
        'nama',
        'deskripsi',
        'dibuat_pada'
    ];

    // Tambahkan relasi users (optional)
    public function users()
    {
        return $this->hasMany(User::class, 'peran_id');
    }
}
