<?php

namespace App\Http\Controllers;

use App\Models\JenisTugas;
use Illuminate\Http\Request;

class JenisTugasController extends Controller
{
    public function index()
    {
        $list = JenisTugas::orderBy('nama')->get();
        // ✏️ Ubah view path:
        return view('jenis_surat_tugas.index', compact('list'));
    }

    public function create()
    {
        // ✏️ Ubah view path:
        return view('jenis_surat_tugas.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|unique:jenis_tugas,nama'
        ]);
        JenisTugas::create([
            'nama' => $request->nama
        ]);
        // ✏️ Ubah route name:
        return redirect()
            ->route('jenis_surat_tugas.index')
            ->with('success', 'Jenis Surat Tugas baru disimpan.');
    }

    public function edit(JenisTugas $jenis_tugas)
    {
        // ✏️ Ubah view path:
        return view('jenis_surat_tugas.edit', compact('jenis_tugas'));
    }

    public function update(Request $request, JenisTugas $jenis_tugas)
    {
        $request->validate([
            'nama' => "required|unique:jenis_tugas,nama,{$jenis_tugas->id}"
        ]);
        $jenis_tugas->update(['nama' => $request->nama]);
        // ✏️ Ubah route name:
        return redirect()
            ->route('jenis_surat_tugas.index')
            ->with('success', 'Jenis Surat Tugas diperbarui.');
    }

    public function destroy(JenisTugas $jenis_tugas)
    {
        $jenis_tugas->delete();
        // tetap gunakan back() karena menghapus dari halaman index
        return back()->with('success', 'Jenis Surat Tugas dihapus.');
    }
}
