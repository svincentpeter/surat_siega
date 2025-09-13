<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MasterKopSurat; // pastikan ini ada

class MasterKopSuratController extends Controller
{
    public function index()
    {
        $kop = MasterKopSurat::first();
        return view('pengaturan.kop_surat', compact('kop'));
    }

    // ======== GANTI METHOD INI =========
    public function update(Request $r)
    {
        // 1) Validasi
        $data = $r->validate([
            // composed fields
            'mode'       => ['required', 'in:image,composed'],
            'judul_atas' => ['nullable','string','max:255'],
            'subjudul'   => ['nullable','string','max:255'],
            'alamat'     => ['nullable','string','max:255'],
            'telepon'    => ['nullable','string','max:255'],
            'fax'        => ['nullable','string','max:255'],
            'email'      => ['nullable','email','max:255'],
            'website'    => ['nullable','string','max:255'],

            // upload files (jangan masukkan ke kolom langsung)
            'logo_kiri'  => ['sometimes','file','image','max:1024'],
            'logo_kanan' => ['sometimes','file','image','max:1024'],
            'header'     => ['sometimes','file','image','max:2048'],
            'footer'     => ['sometimes','file','image','max:2048'],
            'cap'        => ['sometimes','file','image','max:1024'],

            // checkbox tampilkan logo
            'tampilkan_logo_kiri'  => ['nullable'],
            'tampilkan_logo_kanan' => ['nullable'],
        ]);

        $kop = MasterKopSurat::firstOrCreate([]);

        // 2) Simpan file → isikan ke kolom *_path
        $map = [
            'logo_kiri'  => 'logo_kiri_path',
            'logo_kanan' => 'logo_kanan_path',
            'header'     => 'header_path',
            'footer'     => 'footer_path',
            'cap'        => 'cap_path',
        ];
        foreach ($map as $input => $col) {
            if ($r->hasFile($input)) {
                // simpan ke storage/app/public/kop/...
                $data[$col] = $r->file($input)->store('kop', 'public');
            }
        }

        // 3) Normalisasi checkbox
        $data['tampilkan_logo_kiri']  = $r->boolean('tampilkan_logo_kiri');
        $data['tampilkan_logo_kanan'] = $r->boolean('tampilkan_logo_kanan');

        // 4) Hapus key file upload agar tidak dikirim ke update() sebagai kolom
        unset($data['logo_kiri'], $data['logo_kanan'], $data['header'], $data['footer'], $data['cap']);

        // 5) Audit user (jika kolom ada)
        if (\Schema::hasColumn('master_kop_surat', 'updated_by')) {
            $data['updated_by'] = auth()->id();
        }

        // 6) Simpan
        $kop->update($data);

        return back()->with('success', 'Kop surat diperbarui.');
    }
    // ======== /END GANTI =========
}
