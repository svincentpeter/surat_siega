<?php

use Carbon\Carbon;

if (! function_exists('formatDatetimeLocal')) {
    function formatDatetimeLocal($value): string
    {
        if (! $value) {
            return '';
        }
        try {
            return Carbon::parse($value)->format('Y-m-d\TH:i');
        } catch (\Exception $e) {
            return '';
        }
    }
}

if (!function_exists('badge_peran')) {
    /**
     * Generate HTML badge peran berdasarkan nama peran.
     *
     * @param string      $nama  Nama peran (misal: 'admin_tu', 'dekan')
     * @param string|null $label Label/tulisan (default null = sama seperti nama)
     * @return string HTML badge
     */
    function badge_peran($nama, $label = null)
    {
        // Otomatis membuat label yang rapi, contoh: 'wakil_dekan' -> 'Wakil Dekan'
        $label = $label ?? ucwords(str_replace('_', ' ', $nama));

        // Palet Warna Pastel dengan Tulisan Hitam
        $colors = [
            'admin_tu'    => 'background-color: #ffffba; color: #212529;', // Abu-abu Pastel
            'dekan'       => 'background-color: #f8d7da; color: #212529;', // Merah Pastel
            'wakil_dekan' => 'background-color: #ffeadb; color: #212529;', // Oranye Pastel
            'kaprodi'     => 'background-color: #d1ecf1; color: #212529;', // Biru Pastel
            'dosen'       => 'background-color: #d4edda; color: #212529;', // Hijau Pastel
            'tendik'      => 'background-color: #e2d9f3; color: #212529;', // Ungu Pastel
        ];

        // Warna default jika peran tidak ada dalam daftar
        $style = $colors[$nama] ?? 'background-color: #f8f9fa; color: #212529;';

        return '<span class="badge" style="' . $style . '">' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '</span>';
    }
}

if (!function_exists('logStatusChange')) {
    /**
     * Catat perubahan status surat ke tabel tugas_log.
     *
     * @param \PDO|\Illuminate\Database\Connection $db      Koneksi PDO atau DB facade
     * @param int                                  $tugasId ID dari tugas_header yang diubah
     * @param string|null                          $old     Status lama (misal: 'draft')
     * @param string                               $new     Status baru (misal: 'pending', 'disetujui')
     * @return void
     */
    function logStatusChange($db, $tugasId, $old, $new)
    {
        // Jika Anda menggunakan DB facade Laravel, Anda bisa memanggil \DB::table(...)
        // Di sini kita cek apakah $db adalah instance PDO atau bukan
        if ($db instanceof \PDO) {
            // Menggunakan PDO
            $stmt = $db->prepare("
                INSERT INTO tugas_log
                    (tugas_id, status_lama, status_baru, user_id, ip_address, user_agent, created_at)
                VALUES
                    (?, ?, ?, ?, ?, ?, NOW())
            ");
            // dapatkan user_id dan info request (fitur Laravel: request())
            $userId    = $_SESSION['user_id'] ?? null;
            $ip        = $_SERVER['REMOTE_ADDR'] ?? null;
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;
            $stmt->execute([
                $tugasId,
                $old,
                $new,
                $userId,
                $ip,
                $userAgent,
            ]);
        } else {
            // Misal kita menerima \Illuminate\Support\Facades\DB
            $userId    = Auth::id() ?? null;
            $ip        = request()->ip();
            $userAgent = request()->userAgent();
            \DB::table('tugas_log')->insert([
                'tugas_id'   => $tugasId,
                'status_lama'=> $old,
                'status_baru'=> $new,
                'user_id'    => $userId,
                'ip_address' => $ip,
                'user_agent' => $userAgent,
                'created_at' => now(),
            ]);
        }
    }
}
