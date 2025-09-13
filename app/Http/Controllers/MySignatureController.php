<?php

namespace App\Http\Controllers;

use App\Models\UserSignature;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class MySignatureController extends Controller
{
    public function edit()
    {
        $user = Auth::user();
        $this->authorizeRole($user->peran_id); // hanya 2/3
        $sig = $user->signature;
        return view('kop_surat.ttd_saya', compact('sig'));
    }

    public function update(Request $r)
    {
        $user = Auth::user();
        $this->authorizeRole($user->peran_id);

        $data = $r->validate([
            'file' => 'required|image|mimes:png|max:512', // png transparan disarankan, maks 512KB
            'default_width_mm' => 'nullable|integer|min:20|max:80',
            'default_height_mm' => 'nullable|integer|min:10|max:30',
        ]);

        // Simpan privat
        $path = "private/ttd/{$user->id}.png";
        Storage::disk('local')->put($path, file_get_contents($r->file('file')->getRealPath()));

        UserSignature::updateOrCreate(
            ['pengguna_id' => $user->id],
            [
                'ttd_path' => $path,
                'default_width_mm' => $data['default_width_mm'] ?? 35,
                'default_height_mm' => $data['default_height_mm'] ?? 15,
            ]
        );

        return back()->with('ok', 'TTD berhasil diperbarui.');
    }

    private function authorizeRole($peranId)
    {
        if (!in_array((int)$peranId, [2,3], true)) {
            abort(403, 'Hanya Dekan/Wakil Dekan.');
        }
    }
}
