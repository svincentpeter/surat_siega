<?php

namespace App\Services;

use App\Models\TugasHeader;
use App\Models\Notifikasi;
use App\Models\User;
use App\Mail\SuratTugasFinal; // PASTIKAN IMPORT INI ADA
use Illuminate\Support\Facades\Mail; // PASTIKAN IMPORT INI ADA

class NotifikasiService
{
    /**
     * Beri notifikasi ke Penandatangan bahwa ada surat baru menunggu.
     */
    public function notifyApprovalRequest(TugasHeader $tugas)
    {
        if (!$tugas->penandatangan) {
            return;
        }

        Notifikasi::create([
            'pengguna_id'  => $tugas->penandatangan,
            'tipe'         => 'surat_tugas',
            'referensi_id' => $tugas->id,
            'pesan'        => "Surat Tugas {$tugas->nomor} menunggu persetujuan Anda.",
        ]);
        
        // (Opsional) Anda juga bisa mengirim email ke penandatangan di sini jika perlu
    }


    /**
     * (INI YANG PENTING)
     * Beri notifikasi ke semua PENERIMA bahwa surat sudah disetujui & terbit.
     */
    public function notifyApproved(TugasHeader $tugas)
    {
        // 1. Beri notifikasi ke PEMBUAT (admin_tu) bahwa suratnya sudah beres
        Notifikasi::create([
            'pengguna_id'  => $tugas->dibuat_oleh,
            'tipe'         => 'surat_tugas',
            'referensi_id' => $tugas->id,
            'pesan'        => "Surat Tugas {$tugas->nomor} telah disetujui.",
        ]);

        // 2. Ambil semua penerima internal (yang punya akun user/pengguna_id)
        $penerimaInternal = $tugas->penerima()
                                 ->whereNotNull('pengguna_id')
                                 ->with('pengguna') // Load relasi pengguna
                                 ->get();

        if ($penerimaInternal->isEmpty()) {
            return; // Tidak ada penerima internal, selesai.
        }

        // 3. Loop dan kirim Notif DB + Email ke setiap penerima
        foreach ($penerimaInternal as $penerima) {
            
            // Cek jika relasi pengguna ada
            if ($penerima->pengguna) {
                
                // A. Buat Notifikasi Database (untuk ikon lonceng di web)
                Notifikasi::create([
                    'pengguna_id'  => $penerima->pengguna_id,
                    'tipe'         => 'surat_tugas',
                    'referensi_id' => $tugas->id,
                    'pesan'        => "Anda terdaftar sebagai penerima pada Surat Tugas {$tugas->nomor}."
                ]);

                // B. Kirim Email (Pastikan antrian/queue Anda berjalan)
                if ($penerima->pengguna->email) {
                    try {
                        Mail::to($penerima->pengguna->email)
                            ->queue(new SuratTugasFinal($tugas)); // Panggil Mailable Anda
                    } catch (\Exception $e) {
                        // Catat error jika email gagal terkirim, tapi jangan hentikan proses
                        \Log::error('Gagal mengirim email Surat Tugas Final ke ' . $penerima->pengguna->email, [
                            'error' => $e->getMessage()
                        ]);
                    }
                }
            }
        }
    }
}