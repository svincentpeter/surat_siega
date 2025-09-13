<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JenisTugas extends Model
{
    protected $table = 'jenis_tugas';
    protected $fillable = ['nama'];

    public function subtugas()
{
    return $this->hasMany(SubTugas::class, 'jenis_tugas_id');
}
}

