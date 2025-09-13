<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserSignature extends Model
{
    protected $table = 'user_signatures';
    protected $fillable = ['pengguna_id','ttd_path','default_width_mm','default_height_mm'];

    public function pengguna()
    {
        return $this->belongsTo(Pengguna::class, 'pengguna_id');
    }
}
