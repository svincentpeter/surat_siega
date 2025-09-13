<?php
//--- AWAL KODE BARU --- (Ganti seluruh isi file)
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\TugasHeader;

class TugasPenerima extends Model
{
    protected $table = 'tugas_penerima';

    // TAMBAH: Kolom baru ditambahkan ke fillable
    // UBAH: Kolom 'posisi' dihapus
    protected $fillable = ['tugas_id', 'pengguna_id', 'nama_penerima', 'jabatan_penerima', 'dibaca'];

    protected $casts = [
        'dibaca' => 'boolean',
    ];
    
    public $timestamps = false;

    /**
     * Surat Tugas yang diterima
     */
    public function tugas()
    {
        return $this->belongsTo(TugasHeader::class, 'tugas_id');
    }

    /**
     * User penerima (jika ada, karena bisa null)
     */
    public function pengguna()
    {
        return $this->belongsTo(User::class, 'pengguna_id');
    }
}
//--- AKHIR KODE BARU ---