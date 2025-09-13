<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\KeputusanHeader;
use App\Models\KeputusanVersi;
use App\Models\KeputusanPenerima;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\MasterKopSurat;

class KeputusanController extends Controller
{
    // 1. Tidak ada index, langsung redirect ke mine()
    public function index()
    {
        return redirect()->route('surat_keputusan.mine');
    }

    // 2. Halaman “Surat Keputusan Saya”
    public function mine()
    {
        $user    = Auth::user();
        $peranId = $user->peran_id;

        if ($peranId === 1) {
            // Admin TU: semua SK yang dia buat
            $list = KeputusanHeader::where('dibuat_oleh', $user->id)
                ->orderByDesc('created_at')
                ->get();
        } else {
            // Lainnya: hanya yang sudah disetujui & dia penerima
            $list = KeputusanHeader::where('status_surat', 'disetujui')
                ->whereHas('penerima', function ($q) use ($user) {
                    $q->where('pengguna_id', $user->id);
                })
                ->orderByDesc('created_at')
                ->get();
        }

        $stats = [
            'draft'     => $list->where('status_surat', 'draft')->count(),
            'pending'   => $list->where('status_surat', 'pending')->count(),
            'disetujui' => $list->where('status_surat', 'disetujui')->count(),
        ];

        return view('surat_keputusan.keputusan_saya', compact('list', 'stats'));
    }

    // 3. Halaman Semua Surat Keputusan (hanya Admin TU)
    public function all()
    {
        $user = Auth::user();
        if ($user->peran_id !== 1) {
            return redirect()->route('surat_keputusan.mine')
                ->with('error', 'Anda tidak berhak melihat semua surat.');
        }

        $list = KeputusanHeader::with(['pembuat', 'penerima.pengguna'])
            ->orderByDesc('created_at')
            ->get();

        $stats = [
            'draft'     => $list->where('status_surat', 'draft')->count(),
            'pending'   => $list->where('status_surat', 'pending')->count(),
            'disetujui' => $list->where('status_surat', 'disetujui')->count(),
        ];

        return view('surat_keputusan.index', compact('list', 'stats'));
    }

    // 4. Tampilkan form create
    public function create()
    {
        $admins  = \App\Models\User::where('peran_id', 1)->pluck('nama_lengkap', 'id');
        $pejabat = \App\Models\User::whereIn('peran_id', [2, 3])->get();
        // $users = semua pengguna kecuali Admin TU (peran_id != 1)
        $users   = \App\Models\User::where('peran_id', '!=', 1)->get();

        // Penomoran otomatis (sesuai tahun)
        $tahun = date('Y');
        $max   = KeputusanHeader::whereYear('tanggal_asli', $tahun)
            ->max(DB::raw('CAST(SUBSTRING_INDEX(nomor,"/",1) AS UNSIGNED)')) ?? 0;
        $next  = $max + 1;
        $autoNomor = sprintf('%03d/SK/UNIKA/%s', $next, $tahun);

        return view('surat_keputusan.create', compact(
            'admins',
            'pejabat',
            'users',
            'autoNomor',
            'tahun'
        ));
    }

    // 5. Store Surat Keputusan Baru
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nomor'         => 'required|unique:keputusan_header,nomor',
            'tanggal_asli'  => 'required|date',
            'tentang'       => 'required|string',
            'menimbang'     => 'required|array|min:1',
            'mengingat'     => 'required|array|min:1',
            'menetapkan'    => 'required|array|min:1',
            'tembusan'      => 'nullable|string',
            'penandatangan' => 'nullable|exists:pengguna,id',
            'penerima'      => 'required|array',
            'penerima.*'    => 'exists:pengguna,id',
        ]);

        $mode   = $request->input('mode');
        $status = $mode === 'terkirim' ? 'pending' : 'draft';

        DB::beginTransaction();
        try {
            // 1) Buat header SK
            $sk = KeputusanHeader::create([
                'nomor'         => $validated['nomor'],
                'tanggal_asli'  => $validated['tanggal_asli'],
                'tentang'       => $validated['tentang'],
                'menimbang'     => json_encode(array_values($validated['menimbang'])),
                'mengingat'     => json_encode(array_values($validated['mengingat'])),
                'memutuskan' => array_values($validated['menetapkan']),
                'tembusan'      => $validated['tembusan'] ?? null,
                'status_surat'  => $status,
                'dibuat_oleh'   => Auth::id(),
                'penandatangan' => $validated['penandatangan'] ?? null,
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);

            // 2) Buat versi pertama (backup)
            $sk->versi()->create([
                'versi'       => 1,
                'konten_json' => json_encode([
                    'menimbang'  => $validated['menimbang'],
                    'mengingat'  => $validated['mengingat'],
                    'menetapkan' => $validated['menetapkan'],
                ]),
                'is_final'    => 0,
                'dibuat_pada' => now(),
            ]);

            // 3) Sinkronisasi penerima (masukkan semua ID penerima)
            foreach ($validated['penerima'] as $pid) {
                $sk->penerima()->create([
                    'pengguna_id' => $pid,
                    'dibaca'      => false,
                ]);
            }

            DB::commit();
            return redirect()->route('surat_keputusan.index')
                ->with('success', 'Surat Keputusan berhasil disimpan!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Terjadi kesalahan saat menyimpan.');
        }
    }

    // 6. Show (detail)
    public function show($id)
    {
        $user    = Auth::user();
        $peranId = $user->peran_id;
        $keputusan = KeputusanHeader::with([
            'pembuat',
            'penandatanganUser',
            'penerima.pengguna',
            'versi'
        ])->findOrFail($id);

        // 6a) Jika Admin TU hanya boleh melihat SK yang dia buat
        if ($peranId === 1 && $keputusan->dibuat_oleh !== $user->id) {
            return redirect()->route('surat_keputusan.index')
                ->with('error', 'Anda tidak berhak melihat detail ini.');
        }

        // 6b) Jika Dekan/Wakil Dekan, hanya yang status pending & ditujukan ke dia
        if (
            in_array($peranId, [2, 3]) &&
            !($keputusan->status_surat === 'pending' &&
                $keputusan->penandatangan == $user->id)
        ) {
            return redirect()->route('surat_keputusan.index')
                ->with('error', 'Anda hanya dapat melihat surat yang menunggu persetujuan Anda.');
        }

        // 6c) Jika User biasa (peran = 4), hanya yang sudah disetujui & dia penerima
        if ($peranId === 4) {
            $isPenerima = $keputusan->penerima->contains('pengguna_id', $user->id);
            if (!($keputusan->status_surat === 'disetujui' && $isPenerima)) {
                return redirect()->route('surat_keputusan.index')
                    ->with('error', 'Anda hanya dapat melihat surat yang sudah disetujui untuk Anda.');
            }
            // Tandai sudah dibaca
            KeputusanPenerima::where('keputusan_id', $id)
                ->where('pengguna_id', $user->id)
                ->update(['dibaca' => 1]);
        }

        $penerimaList = $keputusan->penerima->pluck('pengguna.nama_lengkap')->all();
        $versList     = $keputusan->versi()->orderBy('versi', 'desc')->get();

        return view('surat_keputusan.show', compact('keputusan', 'penerimaList', 'versList'));
    }

    // 7. Edit SK
    public function edit($id)
    {
        $user    = Auth::user();
        $peranId = $user->peran_id;
        $sk      = KeputusanHeader::with(['penerima.pengguna', 'versi'])->findOrFail($id);

        // Hak akses seperti sebelumnya…
        if ($peranId === 1) {
            if (!($sk->dibuat_oleh === $user->id && $sk->status_surat === 'draft')) {
                return redirect()->route('surat_keputusan.index')
                    ->with('error', 'Anda tidak berhak mengedit surat ini.');
            }
        } elseif (in_array($peranId, [2, 3])) {
            if (!($sk->status_surat === 'pending' && $sk->penandatangan == $user->id)) {
                return redirect()->route('surat_keputusan.index')
                    ->with('error', 'Anda hanya dapat merevisi surat yang menunggu persetujuan Anda.');
            }
        } else {
            return redirect()->route('surat_keputusan.index')
                ->with('error', 'Anda tidak berhak mengakses form edit ini.');
        }

        $admins   = \App\Models\User::where('peran_id', 1)->pluck('nama_lengkap', 'id');
        $pejabat  = \App\Models\User::whereIn('peran_id', [2, 3])->get();
        $users    = \App\Models\User::where('peran_id', '!=', 1)->get();

        // Pastikan hanya melakukan json_decode jika nilainya string
        if (is_string($sk->menimbang)) {
            $sk->menimbang = json_decode($sk->menimbang, true) ?? [];
        }
        if (is_string($sk->mengingat)) {
            $sk->mengingat = json_decode($sk->mengingat, true) ?? [];
        }
if (is_string($sk->memutuskan)) {
    // jika disimpan JSON: decode; jika plain text: bungkus jadi array sederhana
    $decoded = json_decode($sk->memutuskan, true);
    $sk->menetapkan = is_array($decoded) ? $decoded : [['judul' => 'KESATU', 'isi' => $sk->memutuskan]];
} else {
    $sk->menetapkan = is_array($sk->memutuskan) ? $sk->memutuskan : [];
}


        return view('surat_keputusan.edit', compact(
            'sk',
            'admins',
            'pejabat',
            'users'
        ));
    }


    // 8. Update SK
    public function update(Request $request, $id)
    {
        $user    = Auth::user();
        $peranId = $user->peran_id;
        $sk      = KeputusanHeader::with(['penerima', 'versi'])->findOrFail($id);

        // 8a) Validasi hak akses sama seperti edit()
        if ($peranId === 1) {
            if (!($sk->dibuat_oleh === $user->id && $sk->status_surat === 'draft')) {
                return redirect()->route('surat_keputusan.index')
                    ->with('error', 'Anda tidak berhak mengedit surat ini.');
            }
        } elseif (in_array($peranId, [2, 3])) {
            if (!($sk->status_surat === 'pending' && $sk->penandatangan == $user->id)) {
                return redirect()->route('surat_keputusan.index')
                    ->with('error', 'Anda hanya dapat merevisi surat yang menunggu persetujuan Anda.');
            }
        } else {
            return redirect()->route('surat_keputusan.index')
                ->with('error', 'Anda tidak berhak melakukan update.');
        }

        // 8b) Validasi input mirip store()
        $rules = [
            'tanggal_asli'  => 'required|date',
            'tanggal_surat'  => 'nullable|date',
            'tentang'       => 'required|string',
            'menimbang'     => 'required|array|min:1',
            'mengingat'     => 'required|array|min:1',
            'menetapkan'    => 'required|array|min:1',
            'tembusan'      => 'nullable|string',
            'penandatangan' => 'nullable|exists:pengguna,id',
            'penerima'      => 'required|array',
            'penerima.*'    => 'exists:pengguna,id',
        ];
        // Jika nomor diubah, wajib unique (kecuali record sendiri)
        if ($request->input('nomor') !== $sk->nomor) {
            $rules['nomor'] = "required|unique:keputusan_header,nomor,{$id}";
        }

        $validated = $request->validate($rules);

        DB::beginTransaction();
        try {
            // 1) Update header
            $sk->update([
                'nomor'         => $validated['nomor'] ?? $sk->nomor,
                'tanggal_asli'  => $validated['tanggal_asli'],
                'tanggal_surat'  => $request->input('tanggal_surat'),
                'tentang'       => $validated['tentang'],
                'menimbang'     => json_encode(array_values($validated['menimbang'])),
                'mengingat'     => json_encode(array_values($validated['mengingat'])),
                'memutuskan'    => array_values($validated['menetapkan']),
                'tembusan'      => $validated['tembusan'] ?? null,
                'penandatangan' => $validated['penandatangan'] ?? null,
                'updated_at'    => now(),
            ]);


            // 2) Buat versi baru (increment versi)
            $lastVersiNumber = $sk->versi()->max('versi') ?? 0;
            $sk->versi()->create([
                'versi'       => $lastVersiNumber + 1,
                'konten_json' => json_encode([
                    'menimbang'  => $validated['menimbang'],
                    'mengingat'  => $validated['mengingat'],
                    'menetapkan' => $validated['menetapkan'], // ini tidak masalah
                ]),
                'is_final'    => 0,
                'versi_induk' => $lastVersiNumber,
                'dibuat_pada' => now(),
            ]);


            // 3) Sinkronisasi penerima (hapus dulu, lalu insert ulang)
            $sk->penerima()->delete();
            foreach ($validated['penerima'] as $pid) {
                $sk->penerima()->create([
                    'pengguna_id' => $pid,
                    'dibaca'      => false,
                ]);
            }

            DB::commit();
            return redirect()->route('surat_keputusan.show', $sk->id)
                ->with('success', 'Surat Keputusan berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Terjadi kesalahan saat update.');
        }
    }

    // 9. Approve SK (Dekan/Wakil Dekan)
    public function approve($id)
    {
        $user    = Auth::user();
        $peranId = $user->peran_id;

        if (!in_array($peranId, [2, 3])) {
            return redirect()->route('surat_keputusan.mine')
                ->with('error', 'Anda tidak berhak meng‐approve surat ini.');
        }

        $sk = KeputusanHeader::with(['penerima.pengguna', 'versi'])->findOrFail($id);
        if (!($sk->status_surat === 'pending' && $sk->penandatangan == $user->id)) {
            return redirect()->route('surat_keputusan.mine')
                ->with('error', 'Surat ini tidak sedang menunggu persetujuan Anda.');
        }

        DB::beginTransaction();
        try {
            // Ubah status jadi disetujui
            $sk->update([
    'status_surat'  => 'disetujui',
    'tanggal_surat' => $sk->tanggal_surat ?: now(), // ⟵ tambahkan ini
    'updated_at'    => now(),
]);


            // Tandai versi terakhir jadi final
            $lastVers = $sk->versi()->orderByDesc('versi')->first();
            if ($lastVers) {
                $lastVers->update(['is_final' => 1]);
            }

            DB::commit();
            return redirect()->route('surat_keputusan.show', $sk->id)
                ->with('success', 'Surat Keputusan berhasil disetujui dan terkunci.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('surat_keputusan.mine')
                ->with('error', 'Terjadi kesalahan saat approve.');
        }
    }

    // 10. Highlight/Preview (untuk iframe)
    public function highlight($id)
    {
        $keputusan = KeputusanHeader::with([
            'pembuat',
            'penandatanganUser',
            'penerima.pengguna',
            'versi'
        ])->findOrFail($id);

        $penerimaList = $keputusan->penerima->pluck('pengguna.nama_lengkap')->all();
        $versList     = $keputusan->versi()->orderBy('versi', 'desc')->get();

        $response = response()->view('surat_keputusan.highlight', compact('keputusan', 'penerimaList', 'versList'));
        $response->header('X-Frame-Options', 'ALLOWALL');
        return $response;
    }

    // 11. Download PDF
    public function downloadPdf($id)
{
    $keputusan = \App\Models\KeputusanHeader::with(['penerima.pengguna'])
                    ->findOrFail($id);

    // Opsional: kalau kamu punya versi SK
    $versList = method_exists($keputusan, 'versi')
        ? $keputusan->versi()->orderBy('versi', 'desc')->get()
        : collect();

    $penerimaList = $keputusan->penerima->pluck('pengguna.nama_lengkap')->all();

    // [TAMBAHAN] Ambil master kop/cap
    $kop = MasterKopSurat::first();

    // Kirim $kop ke view
    $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('surat_keputusan.surat_pdf', compact('keputusan', 'penerimaList', 'versList', 'kop'))
        ->setPaper('A4', 'portrait');

    $safeNomor = str_replace(['/', '\\'], '-', $keputusan->nomor);
    $filename  = "SuratKeputusan_{$safeNomor}.pdf";

    return $pdf->stream($filename, [
        'Attachment'      => false,
        'X-Frame-Options' => 'ALLOWALL',
    ]);
}

}
