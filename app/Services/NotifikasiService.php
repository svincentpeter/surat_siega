<?php

namespace App\Services;

use App\Models\TugasHeader;
use Illuminate\Support\Facades\DB;

class NotifikasiService
{
    public function notifyApprovalRequest(TugasHeader $tugas): void
    {
        if (!$tugas->penandatangan) return;

        DB::table('notifikasi')->insert([
            'pengguna_id'  => $tugas->penandatangan,
            'tipe'         => 'surat_tugas',
            'referensi_id' => $tugas->id,
            'pesan'        => "Surat Tugas {$tugas->nomor} menunggu persetujuan Anda.",
            'dibaca'       => 0,
            // kolom timestamp di tabel ini adalah 'dibuat_pada'
            'dibuat_pada'  => now(),
        ]);
    }

    public function notifyApproved(TugasHeader $tugas): void
    {
        // pembuat
        $rows = [[
            'pengguna_id'  => $tugas->dibuat_oleh,
            'tipe'         => 'surat_tugas',
            'referensi_id' => $tugas->id,
            'pesan'        => "Surat Tugas {$tugas->nomor} telah disetujui.",
            'dibaca'       => 0,
            'dibuat_pada'  => now(),
        ]];

        // penerima internal
        $userIds = $tugas->penerima()
            ->whereNotNull('pengguna_id')
            ->pluck('pengguna_id')
            ->unique()
            ->values()
            ->all();

        foreach ($userIds as $uid) {
            $rows[] = [
                'pengguna_id'  => $uid,
                'tipe'         => 'surat_tugas',
                'referensi_id' => $tugas->id,
                'pesan'        => "Anda terdaftar sebagai penerima pada Surat Tugas {$tugas->nomor}.",
                'dibaca'       => 0,
                'dibuat_pada'  => now(),
            ];
        }

        DB::table('notifikasi')->insert($rows);
    }
}
