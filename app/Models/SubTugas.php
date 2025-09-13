<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubTugas extends Model
{
    protected $table = 'sub_tugas';
    protected $fillable = ['jenis_tugas_id', 'nama'];

    /**
     * Jenis tugas induk
     */
    public function jenisTugas()
    {
        return $this->belongsTo(JenisTugas::class, 'jenis_tugas_id');
    }

    /**
     * Daftar detail tugas
     */
    public function detail()
    {
        return $this->hasMany(TugasDetail::class, 'sub_tugas_id');
    }
}
