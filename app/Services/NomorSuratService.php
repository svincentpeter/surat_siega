<?php
namespace App\Services;

use Illuminate\Support\Facades\DB;

class NomorSuratService
{
    public function reserve(string $kodeUnit, string $kodeKlasifikasi, string $bulanRomawi, int $tahun): array
{
    return DB::transaction(function () use ($kodeUnit, $kodeKlasifikasi, $bulanRomawi, $tahun) {
        $row = DB::table('nomor_surat_counters')
            ->lockForUpdate()
            ->where('kode_surat', $kodeKlasifikasi) // Kunci pencarian sekarang adalah kode klasifikasi
            ->where('unit', $kodeUnit)
            ->where('bulan_romawi', $bulanRomawi)
            ->where('tahun', $tahun)
            ->first();

        if (!$row) {
            DB::table('nomor_surat_counters')->insert([
                'kode_surat'   => $kodeKlasifikasi,
                'unit'         => $kodeUnit,
                'bulan_romawi' => $bulanRomawi,
                'tahun'        => $tahun,
                'last_number'  => 0,
                'created_at'   => now(), 'updated_at' => now(),
            ]);
            $row = DB::table('nomor_surat_counters')
                ->where('kode_surat', $kodeKlasifikasi)
                ->where('unit', $kodeUnit)
                ->where('bulan_romawi', $bulanRomawi)
                ->where('tahun', $tahun)
                ->first();
        }

        $next = $row->last_number + 1;
        DB::table('nomor_surat_counters')
          ->where('id', $row->id)
          ->update(['last_number' => $next, 'updated_at' => now()]);

        // format: 001/B.1.1/TG/UNIKA/VIII/2025
        $noUrut = str_pad((string)$next, 3, '0', STR_PAD_LEFT);
        // Kode klasifikasi (misal: B.1.1) sekarang menjadi bagian dari format
        $nomor  = "{$noUrut}/{$kodeKlasifikasi}/{$kodeUnit}/UNIKA/{$bulanRomawi}/{$tahun}";

        return ['no_urut' => $noUrut, 'nomor' => $nomor];
    });
}
}
