<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\TugasHeader;

class TugasLog extends Model
{
    protected $table = 'tugas_log';

    // Karena tabel tidak punya kolom updated_at, kita matikan timestamps otomatis
    public $timestamps = false;

    // Kolom-kolom yang bisa mass assign
    protected $fillable = [
        'tugas_id',
        'status_lama',
        'status_baru',
        'user_id',
        'ip_address',
        'user_agent',
        'created_at',
    ];

    /**
     * Relasi ke Surat Tugas
     */
    public function tugas()
    {
        return $this->belongsTo(TugasHeader::class, 'tugas_id');
    }

    /**
     * Relasi ke User yang mengubah status
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
