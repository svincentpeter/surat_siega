<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TugasHeader;
use App\Models\TugasPenerima;
use App\Models\TugasDetail;
use App\Models\SubTugas;
use App\Models\JenisTugas;
use App\Models\Notifikasi;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Services\NomorSuratService;
use Illuminate\Database\QueryException;
use App\Http\Requests\StoreTugasPenerimaRequest;
use Illuminate\Validation\Rule;
use App\Services\NotifikasiService;
use Illuminate\Support\Facades\Schema;
use App\Mail\SuratTugasFinal;
use App\Models\MasterKopSurat;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Storage;




class TugasController extends Controller
{
    // ------------------ Helpers ------------------

    private function buildSignAssets($tugas, $kop = null): array
{
    $ttdCfg = is_array($tugas->ttd_config) ? $tugas->ttd_config : (json_decode($tugas->ttd_config ?? '[]', true) ?: []);
    $capCfg = is_array($tugas->cap_config) ? $tugas->cap_config : (json_decode($tugas->cap_config ?? '[]', true) ?: []);

    // BASE (fallback kalau tidak tersimpan)
    $ttdBaseL = (int)($ttdCfg['base_left_mm'] ?? 108);
    $ttdBaseT = (int)($ttdCfg['base_top_mm']  ?? 205);
    $capBaseL = (int)($capCfg['base_left_mm'] ?? 125);
    $capBaseT = (int)($capCfg['base_top_mm']  ?? 185);

    // OFFSET (Δ)
    $ttdOffX = (int)($ttdCfg['offset_x'] ?? 0);
    $ttdOffY = (int)($ttdCfg['offset_y'] ?? 0);
    $capOffX = (int)($capCfg['offset_x'] ?? 0);
    $capOffY = (int)($capCfg['offset_y'] ?? 0);

    // FINAL posisi = BASE + OFFSET
    $ttdLeft = $ttdBaseL + $ttdOffX;
    $ttdTop  = $ttdBaseT + $ttdOffY;
    $capLeft = $capBaseL + $capOffX;
    $capTop  = $capBaseT + $capOffY;

    $ttdW = (int)($ttdCfg['width_mm']  ?? 42);
    $ttdH = (int)($ttdCfg['height_mm'] ?? 22);
    $capW = (int)($capCfg['width_mm']  ?? ($kop->cap_default_width_mm ?? 35));
    $capOpacity = max(0, min(1, (($capCfg['opacity'] ?? $kop->cap_opacity ?? 85) / 100)));

    // base64 gambar (support local & public)
    $ttdImageB64 = null;
    if (!empty($ttdCfg['show'])) {
        $ttdImageB64 = $this->b64FromStorage($ttdCfg['path'] ?? $tugas->ttd_path ?? null);
    }

    $capImageB64 = null;
    if (!empty($capCfg['show'])) {
        $capImageB64 = $this->b64FromStorage($capCfg['path'] ?? ($tugas->cap_path ?? ($kop->cap_path ?? null)));
    }

    return compact(
        'ttdImageB64','capImageB64',
        'ttdLeft','ttdTop','ttdW','ttdH',
        'capLeft','capTop','capW','capOpacity'
    );
}



    private function toRoman($number)
    {
        $map = ['M' => 1000, 'CM' => 900, 'D' => 500, 'CD' => 400, 'C' => 100, 'XC' => 90, 'L' => 50, 'XL' => 40, 'X' => 10, 'IX' => 9, 'V' => 5, 'IV' => 4, 'I' => 1];
        $returnValue = '';
        while ($number > 0) {
            foreach ($map as $roman => $int) {
                if ($number >= $int) {
                    $number -= $int;
                    $returnValue .= $roman;
                    break;
                }
            }
        }
        return $returnValue;
    }

    private function getFormDependencies(): array
    {
        $admins      = \App\Models\User::where('peran_id', 1)->pluck('nama_lengkap', 'id');
        $pejabat     = \App\Models\User::whereIn('peran_id', [2, 3])->get();
        $users       = \App\Models\User::where('peran_id', '!=', 1)->get();
        $taskMaster  = JenisTugas::with('subtugas.detail')->orderBy('nama')->get();
        $klasifikasi = \App\Models\KlasifikasiSurat::orderBy('kode')->get();
        return compact('admins', 'pejabat', 'users', 'taskMaster', 'klasifikasi');
    }

    private function resolveMode(Request $request): string
    {
        $raw = $request->input('action') ?? $request->input('mode');
        if ($raw === 'terkirim') $raw = 'submit';
        $mode = is_array($raw) ? end($raw) : ($raw ?? 'draft');
        return in_array($mode, ['draft', 'submit']) ? $mode : 'draft';
    }

    // ------------------ CRUD / Business ------------------

    public function index()
    {
        return redirect()->route('surat_tugas.mine');
    }

    public function mine()
    {
        $user    = Auth::user();
        $peranId = $user->peran_id;

        if ($peranId === 1) {
            $list = TugasHeader::with(['penerima.pengguna', 'pembuat', 'penandatanganUser'])
                ->where('dibuat_oleh', $user->id)
                ->orderByDesc('created_at')->get();
        } else {
            $list = TugasHeader::with(['penerima.pengguna', 'pembuat', 'penandatanganUser'])
                ->where('status_surat', 'disetujui')
                ->whereHas('penerima', function ($q) use ($user) {
                    $q->where('pengguna_id', $user->id);
                })
                ->orderByDesc('created_at')->get();
        }

        $stats = [
            'draft'     => $list->where('status_surat', 'draft')->count(),
            'pending'   => $list->where('status_surat', 'pending')->count(),
            'disetujui' => $list->where('status_surat', 'disetujui')->count(),
        ];

        return view('surat_tugas.tugas_saya', compact('list', 'stats'));
    }

    public function all()
    {
        $user    = Auth::user();
        $peranId = $user->peran_id;

        if ($peranId !== 1) {
            return redirect()->route('surat_tugas.mine')
                ->with('error', 'Anda tidak berhak melihat semua surat.');
        }

        $list = TugasHeader::with(['pembuat', 'penerima.pengguna'])
            ->orderByDesc('created_at')->get();

        $stats = [
            'draft'     => $list->where('status_surat', 'draft')->count(),
            'pending'   => $list->where('status_surat', 'pending')->count(),
            'disetujui' => $list->where('status_surat', 'disetujui')->count(),
        ];

        return view('surat_tugas.index', compact('list', 'stats'));
    }

    public function create()
    {
        $deps = $this->getFormDependencies();
        extract($deps);

        $tahun        = date('Y');
        $semester     = (date('n') >= 8 || date('n') <= 1) ? 'Ganjil' : 'Genap';
        $bulanRomawi  = $this->toRoman(date('n'));
        // Perbaikan: pratinjau nomor pakai BULAN romawi (bukan romawi semester)
        $autoNomor    = sprintf('/TG/UNIKA/%s/%s', $bulanRomawi, $tahun);

        return view('surat_tugas.create', compact(
            'admins',
            'pejabat',
            'users',
            'taskMaster',
            'autoNomor',
            'tahun',
            'semester',
            'klasifikasi',
            'bulanRomawi'
        ));
    }

    public function store(Request $request)
{
    \Log::info('Proses store Surat Tugas dimulai.', $request->all());
    $mode = $this->resolveMode($request);

    $rules = [
        'klasifikasi_surat_id'   => 'required|exists:klasifikasi_surat,id',
        'nama_umum'              => 'required|string|max:255',
        'jenis_tugas'            => 'required|string',
        'tugas'                  => 'required|string',
        'detail_tugas'           => 'nullable|string|max:65000',
        'redaksi_pembuka'        => 'nullable|string|max:2000',
        'penutup'                => 'nullable|string|max:1000',

        'penerima_internal'      => 'sometimes|array',
        'penerima_internal.*'    => 'exists:pengguna,id',

        'penerima_eksternal'                 => 'sometimes|array',
        'penerima_eksternal.*.nama'          => 'required_with:penerima_eksternal|string|max:255',
        'penerima_eksternal.*.instansi'      => 'nullable|string|max:255',
        'penerima_eksternal.*.jabatan'       => 'nullable|string|max:255',

        'status_penerima'         => 'nullable|string|max:50',

        'tahun'                   => 'required|string',
        'semester'                => 'required|string',
        'bulan'                   => 'required|string',
        'nomor'                   => 'required|string|max:255',
        'no_surat_manual'         => 'nullable|string|max:255',

        'asal_surat'              => 'required',
        'nama_pembuat'            => 'required',
        'penandatangan'           => 'required|exists:pengguna,id',

        'waktu_mulai'             => 'required|date',
        'waktu_selesai'           => 'required|date|after_or_equal:waktu_mulai',
        'tempat'                  => 'required|string|max:255',
    ];

    $validated = $request->validate($rules);
    // Normalisasi penerima_internal: jika terkirim sebagai "9,11" (string CSV), ubah menjadi array
if (isset($validated['penerima_internal']) && is_string($validated['penerima_internal'])) {
    $validated['penerima_internal'] = array_filter(array_map('trim', explode(',', $validated['penerima_internal'])));
}
    // (1) Nomor & status nomor: TETAP 'reserved' walaupun pakai nomor manual (hindari ENUM DB error)
    $nomorSurat  = $validated['nomor'];
    $nomorStatus = 'reserved';
    if ($request->filled('no_surat_manual')) {
        $nomorSurat = $request->input('no_surat_manual');
        // $nomorStatus = 'manual'; // ← JANGAN pakai ini, ENUM DB tidak mengenal 'manual'
    }

    // (2) Detail tugas ID wajib terpetakan
    $detailId = $this->resolveDetailTugasId(
    $validated['tugas'] ?? '',
    $validated['jenis_tugas'] ?? null
);

    if (empty($detailId)) {
        return back()
            ->withInput()
            ->with('error', 'Mapping detail tugas tidak ditemukan untuk: ' . ($validated['tugas'] ?? '(kosong)') . '. Silakan pilih ulang jenis tugas atau hubungi admin untuk melengkapi master.');
    }

    // (3) Normalisasi status_penerima agar cocok ENUM DB (dosen|tendik|mahasiswa)
    $allowedSegmen = ['dosen', 'tendik', 'mahasiswa'];
    $rawSegmen     = strtolower(trim($validated['status_penerima'] ?? ''));

    // Jika front-end mengirim "dosen dan tendik" / "umum" / gabungan lain → jadikan null (biar aman)
    $segmen = in_array($rawSegmen, $allowedSegmen, true) ? $rawSegmen : null;

    // Mode draft / submit
    $status       = $mode === 'submit' ? 'pending' : 'draft';
    $nextApprover = $mode === 'submit' ? $validated['penandatangan'] : null;

    DB::beginTransaction();
    try {
        // Simpan header
        $tugas = TugasHeader::create([
            'nomor'                => $nomorSurat,
            'nomor_status'         => $nomorStatus,
            'bulan'                => $validated['bulan'],
            'tahun'                => $validated['tahun'],
            'nama_umum'            => $validated['nama_umum'],
            'klasifikasi_surat_id' => $validated['klasifikasi_surat_id'],
            'status_surat'         => $status,
            'dibuat_oleh'          => Auth::id(),
            'nama_pembuat'         => $validated['nama_pembuat'],
            'asal_surat'           => $validated['asal_surat'],
            'jenis_tugas'          => $validated['jenis_tugas'],
            'tugas'                => $validated['tugas'],
            'detail_tugas'         => $validated['detail_tugas'] ?? null,
            'detail_tugas_id'      => $detailId,                 // ← sudah dipastikan non-null
            'status_penerima'      => $segmen,                   // ← sudah dinormalisasi
            'redaksi_pembuka'      => $validated['redaksi_pembuka'] ?? null,
            'penutup'              => $validated['penutup'] ?? null,
            'waktu_mulai'          => $validated['waktu_mulai'],
            'waktu_selesai'        => $validated['waktu_selesai'],
            'tempat'               => $validated['tempat'],
            'penandatangan'        => $validated['penandatangan'],
            'next_approver'        => $nextApprover,
        ]);

        // Simpan penerima internal
        $internal = $validated['penerima_internal'] ?? [];
        foreach ($internal as $uid) {
            \App\Models\TugasPenerima::create([
                'tugas_id' => $tugas->id,
                'pengguna_id' => $uid,
                'tipe' => 'internal',
                'nama_penerima' => null,
                'instansi_penerima' => null,
                'jabatan_penerima' => null,
            ]);
        }

        // Simpan penerima eksternal
        $eksternal = $validated['penerima_eksternal'] ?? [];
        foreach ($eksternal as $p) {
            if (!empty($p['nama'])) {
                \App\Models\TugasPenerima::create([
                    'tugas_id' => $tugas->id,
                    'pengguna_id' => null,
                    'tipe' => 'eksternal',
                    'nama_penerima' => $p['nama'],
                    'instansi_penerima' => $p['instansi'] ?? null,
                    'jabatan_penerima' => $p['jabatan'] ?? null,
                ]);
            }
        }

        // Notifikasi jika diajukan
        if ($status === 'pending') {
            app(\App\Services\NotifikasiService::class)->notifyApprovalRequest($tugas);
        }

        DB::commit();

        $message = $status === 'pending'
            ? 'Surat tugas berhasil diajukan!'
            : 'Surat tugas berhasil disimpan sebagai draft!';

        return redirect()->route('surat_tugas.mine')->with('success', $message);

    } catch (\Throwable $e) {
        DB::rollBack();
        // Biar root cause kelihatan saat dev
        \Log::error('Gagal menyimpan Surat Tugas', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
        return back()->withInput()->with('error', 'Terjadi kesalahan saat menyimpan: ' . $e->getMessage());
    }
}

/**
 * Pemetaan cerdas dari nama sub_tugas -> detail_tugas_id.
 * Strategy:
 * 1) Cari SubTugas by nama (dan jenis kalau ada).
 * 2) Ambil TugasDetail di bawahnya (prioritas: yang "paling masuk akal", lalu first()).
 * 3) Fallback terakhir: cari TugasDetail bernama "Lainnya", atau MIN(id).
 */
private function resolveDetailTugasId(?string $tugasNama, ?string $jenisTugas): ?int
{
    $name = trim((string) $tugasNama);
    if ($name === '') {
        \Log::warning('resolveDetailTugasId: tugasNama kosong');
        return null;
    }

    // 1) Dapatkan ID jenis_tugas (kalau ada)
    $jenisId = null;
    if (!empty($jenisTugas)) {
        try {
            $jenisId = optional(JenisTugas::whereRaw('LOWER(nama) = ?', [mb_strtolower($jenisTugas)])->first())->id;
        } catch (\Throwable $e) {
            try {
                // fallback via DB kalau model tidak ada/beda
                $jenisId = DB::table('jenis_tugas')->whereRaw('LOWER(nama) = ?', [mb_strtolower($jenisTugas)])->value('id');
            } catch (\Throwable $e2) {
                // biarkan null
            }
        }
    }

    // 2) Temukan sub_tugas sesuai nama (+ jenis jika tersedia)
    $sub = null;
    try {
        $q = SubTugas::query()->whereRaw('LOWER(nama) = ?', [mb_strtolower($name)]);
        if ($jenisId) $q->where('jenis_tugas_id', $jenisId);
        $sub = $q->first();
    } catch (\Throwable $e) {
        // fallback raw
        try {
            $q = DB::table('sub_tugas')->whereRaw('LOWER(nama) = ?', [mb_strtolower($name)]);
            if ($jenisId) $q->where('jenis_tugas_id', $jenisId);
            $row = $q->first();
            if ($row) {
                $sub = (object) ['id' => $row->id, 'jenis_tugas_id' => $row->jenis_tugas_id, 'nama' => $row->nama];
            }
        } catch (\Throwable $e2) {
            // abaikan
        }
    }

    // Jika tidak ketemu exact, coba LIKE
    if (!$sub) {
        try {
            $q = SubTugas::query()->where('nama', 'LIKE', '%' . $name . '%');
            if ($jenisId) $q->where('jenis_tugas_id', $jenisId);
            $sub = $q->first();
        } catch (\Throwable $e) {
            try {
                $q2 = DB::table('sub_tugas')->where('nama', 'LIKE', '%' . $name . '%');
                if ($jenisId) $q2->where('jenis_tugas_id', $jenisId);
                $row = $q2->first();
                if ($row) $sub = (object) ['id' => $row->id, 'jenis_tugas_id' => $row->jenis_tugas_id, 'nama' => $row->nama];
            } catch (\Throwable $e2) { /* noop */ }
        }
    }

    // 3) Jika sub_tugas ketemu, ambil detail di bawahnya
    if ($sub && isset($sub->id)) {
        // Prioritas cari detail yang namanya "masuk akal" untuk reviewer/publikasi, lalu fallback first()
        $detail = null;

        $cariKataKunci = [
            'jurnal nasional', 'artikel jurnal nasional', 'artikel nasional', 'reviewer jurnal nasional',
            'review jurnal nasional', 'review artikel nasional', 'publikasi nasional'
        ];

        // a) Cari by keywords pada TugasDetail::where('sub_tugas_id', ...)
        try {
            $dq = TugasDetail::query()->where('sub_tugas_id', $sub->id);
            foreach ($cariKataKunci as $kw) {
                $try = (clone $dq)->whereRaw('LOWER(nama) LIKE ?', ['%' . mb_strtolower($kw) . '%'])->first();
                if ($try) { $detail = $try; break; }
            }
            if (!$detail) {
                $detail = TugasDetail::where('sub_tugas_id', $sub->id)->orderBy('id')->first();
            }
        } catch (\Throwable $e) {
            // fallback DB raw
            try {
                foreach ($cariKataKunci as $kw) {
                    $row = DB::table('tugas_detail')
                        ->where('sub_tugas_id', $sub->id)
                        ->whereRaw('LOWER(nama) LIKE ?', ['%' . mb_strtolower($kw) . '%'])
                        ->orderBy('id')
                        ->first();
                    if ($row) { $detail = (object) ['id' => $row->id]; break; }
                }
                if (!$detail) {
                    $row = DB::table('tugas_detail')->where('sub_tugas_id', $sub->id)->orderBy('id')->first();
                    if ($row) $detail = (object) ['id' => $row->id];
                }
            } catch (\Throwable $e2) { /* noop */ }
        }

        if ($detail && isset($detail->id)) {
            return (int) $detail->id;
        }
    }

    // 4) Fallback universal: cari detail bernama "Lainnya"
    try {
        $lainnya = TugasDetail::whereRaw('LOWER(nama) = ?', ['lainnya'])->value('id');
        if ($lainnya) return (int) $lainnya;
    } catch (\Throwable $e) {
        try {
            $lainnya = DB::table('tugas_detail')->whereRaw('LOWER(nama) = ?', ['lainnya'])->value('id');
            if ($lainnya) return (int) $lainnya;
        } catch (\Throwable $e2) { /* noop */ }
    }

    // 5) Fallback terakhir: MIN(id)
    try {
        $minId = TugasDetail::min('id');
        if ($minId) return (int) $minId;
    } catch (\Throwable $e) {
        try {
            $minId = DB::table('tugas_detail')->min('id');
            if ($minId) return (int) $minId;
        } catch (\Throwable $e2) { /* noop */ }
    }

    \Log::warning('resolveDetailTugasId: gagal memetakan, semua fallback habis', [
        'tugas' => $name,
        'jenis' => $jenisTugas,
    ]);
    return null;
}

/**
 * Cari ID untuk detail_tugas berdasarkan nama tugas (dan opsional jenis_tugas).
 * Mengembalikan int ID jika ketemu, atau null jika tidak ketemu.
 */
private function getDetailTugasId(?string $tugasNama, ?string $jenisTugas = null): ?int
{
    $name = trim((string) $tugasNama);
    if ($name === '') {
        \Log::warning('getDetailTugasId: tugasNama kosong');
        return null;
    }

    // Kandidat tabel & kolom yang umum dipakai di berbagai implementasi
    $candidateTables = [
        'detail_tugas',
        'master_detail_tugas',
        'tugas_detail',
    ];
    $candidateNameCols = ['nama', 'nama_detail', 'detail', 'name'];
    $candidateJenisCols = ['jenis', 'kategori', 'jenis_tugas']; // kolom text
    $candidateJenisIdCols = ['jenis_tugas_id', 'kategori_id'];   // kolom foreign key id

    // Jika ada label jenis_tugas, kita siapkan kemungkinan ID-nya (kalau ada master jenis_tugas)
    $jenisId = null;
    if (!empty($jenisTugas)) {
        try {
            // Coba cari di tabel master umum
            $jenisId = DB::table('jenis_tugas')->where('nama', $jenisTugas)->value('id');
            if (!$jenisId) {
                // fallback kemungkinan nama tabel lain
                $jenisId = DB::table('master_jenis_tugas')->where('nama', $jenisTugas)->value('id');
            }
        } catch (\Throwable $e) {
            // aman: jika tabel tidak ada, abaikan
            \Log::info('getDetailTugasId: tabel jenis_tugas/master_jenis_tugas tidak tersedia atau kolom beda', [
                'err' => $e->getMessage()
            ]);
        }
    }

    foreach ($candidateTables as $table) {
        // Cek apakah tabel ada
        try {
            DB::table($table)->limit(1)->get();
        } catch (\Throwable $e) {
            // Tabel tidak ada—lanjut kandidat berikutnya
            continue;
        }

        foreach ($candidateNameCols as $nameCol) {
            try {
                // Apakah kolom nama ada? (uji cepat; jika tidak ada akan lempar exception)
                DB::table($table)->select($nameCol)->limit(1)->get();

                // Build query dasar: cocokkan nama tugas (case-insensitive)
                $q = DB::table($table)->whereRaw("LOWER($nameCol) = ?", [mb_strtolower($name)]);

                // Tambahkan filter jenis (text) bila tersedia
                if (!empty($jenisTugas)) {
                    foreach ($candidateJenisCols as $jc) {
                        try {
                            DB::table($table)->select($jc)->limit(1)->get();
                            // kolom ada → coba filter exact (case-insensitive)
                            $try = (clone $q)->whereRaw("LOWER($jc) = ?", [mb_strtolower($jenisTugas)])->value('id');
                            if ($try) return (int) $try;
                        } catch (\Throwable $e) {
                            // kolom jenis ini tidak ada—abaikan
                        }
                    }
                    // Tambahkan filter jenis id bila kita punya jenisId
                    if ($jenisId) {
                        foreach ($candidateJenisIdCols as $jidc) {
                            try {
                                DB::table($table)->select($jidc)->limit(1)->get();
                                $try = (clone $q)->where($jidc, $jenisId)->value('id');
                                if ($try) return (int) $try;
                            } catch (\Throwable $e) {
                                // kolom id jenis tidak ada—abaikan
                            }
                        }
                    }
                }

                // Jika tidak ada filter jenis yang cocok, coba ambil berdasarkan nama saja
                $id = $q->value('id');
                if ($id) return (int) $id;

                // Terakhir, coba pencarian mirip (LIKE) bila exact tidak ketemu
                $likeId = DB::table($table)
                    ->where($nameCol, 'LIKE', $name)
                    ->orWhere($nameCol, 'LIKE', '%' . $name . '%')
                    ->value('id');
                if ($likeId) return (int) $likeId;
            } catch (\Throwable $e) {
                // Kolom name tidak ada atau query gagal → lanjut kandidat berikutnya
                continue;
            }
        }
    }

    \Log::warning('getDetailTugasId: tidak ditemukan', [
        'tugas' => $name,
        'jenis' => $jenisTugas,
    ]);
    return null;
}

    /**
     * Tambah 1 penerima (internal/eksternal) ke surat yang sudah ada.
     * Endpoint ini memakai StoreTugasPenerimaRequest agar validasi internal/eksternal tegas.
     */
    public function addRecipient(StoreTugasPenerimaRequest $request)
    {
        $data  = $request->validated();
        $tugas = TugasHeader::findOrFail($data['tugas_id']);

        if ($tugas->nomor_status === 'locked') {
            return back()->withErrors(['penerima' => 'Surat sudah terkunci, tidak bisa mengubah penerima.']);
        }

        // Normalisasi: internal -> kosongkan nama, eksternal -> pengguna_id null
        $payload = [
            'tugas_id'         => $tugas->id,
            'pengguna_id'      => $data['pengguna_id'] ?? null,
            'nama_penerima'    => $data['pengguna_id'] ? '' : trim($data['nama_penerima'] ?? ''),
            'jabatan_penerima' => $data['jabatan_penerima'] ?? null,
        ];

        try {
            TugasPenerima::create($payload);
            return back()->with('success', 'Penerima ditambahkan.');
        } catch (QueryException $e) {
            if ((int)($e->errorInfo[1] ?? 0) === 1062) {
                return back()->withErrors([
                    'penerima' => 'Penerima yang sama sudah ada pada surat ini.',
                ])->withInput();
            }
            throw $e;
        }
    }

    public function approveList()
    {
        $this->authorize('approveList', TugasHeader::class);

        $list = TugasHeader::with(['pembuat', 'penerima.pengguna'])
            ->where('status_surat', 'pending')
            ->where('penandatangan', Auth::id())
            ->orderByDesc('created_at')->get();

        $stats = [
            'draft'     => $list->where('status_surat', 'draft')->count(),
            'pending'   => $list->where('status_surat', 'pending')->count(),
            'disetujui' => $list->where('status_surat', 'disetujui')->count(),
        ];

        return view('surat_tugas.index', compact('list', 'stats'));
    }

    // START PATCH: Approve + Digital Sign
    public function approve(\Illuminate\Http\Request $r, $id)
{
    $user = auth()->user();
    if (!in_array((int)$user->peran_id, [2, 3], true)) {
        abort(403, 'Hanya Dekan/Wakil Dekan yang dapat approve.');
    }

    $tugas = \App\Models\TugasHeader::findOrFail($id);
    if (!in_array($tugas->status_surat, ['pending', 'draft'], true)) {
        return back()->with('err', 'Surat sudah diproses.');
    }

    $data = $r->validate([
        'show_ttd'  => 'nullable|boolean',
        'ttd_scale' => 'nullable|integer|min:60|max:160',
        'ttd_x'     => 'nullable|integer|min:-150|max:150', // OFFSET (Δ) X dalam mm
        'ttd_y'     => 'nullable|integer|min:-150|max:150', // OFFSET (Δ) Y dalam mm
        'show_cap'  => 'nullable|boolean',
        'cap_scale' => 'nullable|integer|min:60|max:160',
        'cap_x'     => 'nullable|integer|min:-150|max:150', // OFFSET (Δ) X dalam mm
        'cap_y'     => 'nullable|integer|min:-150|max:150', // OFFSET (Δ) Y dalam mm
    ]);

    /* Titik dasar (BASE) dalam mm — HARUS sama dengan yang dipakai overlay & _core */
    $TTD_BASE_LEFT_MM = 108;
    $TTD_BASE_TOP_MM  = 205;
    $CAP_BASE_LEFT_MM = 125;
    $CAP_BASE_TOP_MM  = 185;

    // ---------- Build config TTD (simpan OFFSET, bukan absolut) ----------
    $sig = $user->signature;
    $ttdCfg = [];
    if (!empty($data['show_ttd']) && $sig && \Illuminate\Support\Facades\Storage::disk('local')->exists($sig->ttd_path)) {
        $scale    = (int)($data['ttd_scale'] ?? 100);
        $defW     = (int)($sig->default_width_mm  ?? 42);
        $defH     = (int)($sig->default_height_mm ?? 22);

        $ttdCfg = [
            'show'         => true,
            'width_mm'     => (int) round($defW * $scale / 100),
            'height_mm'    => (int) round($defH * $scale / 100),
            // simpan OFFSET (Δ), render nanti = BASE + OFFSET
            'offset_x'     => (int)($data['ttd_x'] ?? 0),
            'offset_y'     => (int)($data['ttd_y'] ?? 0),
            // simpan BASE supaya konsisten di render/preview/pdf
            'base_left_mm' => $TTD_BASE_LEFT_MM,
            'base_top_mm'  => $TTD_BASE_TOP_MM,
            'path'         => $sig->ttd_path,
        ];
    }

    // ---------- Build config CAP (simpan OFFSET, bukan absolut) ----------
    $kop = \App\Models\MasterKopSurat::query()->first();
    $capCfg = [];
    if ($kop && !empty($data['show_cap']) && $kop->cap_path) {
        $capScale = (int)($data['cap_scale'] ?? 100);
        $capDefW  = (int)($kop->cap_default_width_mm ?? 35);

        $capCfg   = [
            'show'         => true,
            'width_mm'     => (int) round($capDefW * $capScale / 100),
            'offset_x'     => (int)($data['cap_x'] ?? 0),
            'offset_y'     => (int)($data['cap_y'] ?? 0),
            'base_left_mm' => $CAP_BASE_LEFT_MM,
            'base_top_mm'  => $CAP_BASE_TOP_MM,
            'opacity'      => max(0, min(100, (int)($kop->cap_opacity ?? 85))), // simpan % (0..100)
            'path'         => $kop->cap_path,
        ];
    }

    // ---------- Simpan config & finalize surat ----------
    $tugas->ttd_config   = $ttdCfg;
    $tugas->cap_config   = $capCfg;
    if (empty($tugas->tanggal_surat)) {
        $tugas->tanggal_surat = now()->toDateString();
    }
    $tugas->status_surat = 'disetujui';
    $tugas->signed_at    = now();
    $tugas->save();

    // ---------- Render & simpan PDF signed (pakai _core) ----------
    $pdfBytes = $this->renderTugasPdfWithSign($tugas, $ttdCfg, $capCfg);
    $pdfPath  = "private/surat_tugas/signed/{$tugas->id}.pdf";
    \Illuminate\Support\Facades\Storage::disk('local')->put($pdfPath, $pdfBytes);
    $tugas->signed_pdf_path = $pdfPath;
    $tugas->save();

    return back()->with('ok', 'Surat disetujui & ditandatangani.');
}


    // ====== helper kecil di controller ======
    private function readCfg($cfg)
    {
        if (is_array($cfg)) return $cfg;
        if (is_string($cfg)) {
            $dec = json_decode($cfg, true);
            return is_array($dec) ? $dec : [];
        }
        return [];
    }

    private function b64FromStorage($pathPublicOrLocal)
    {
        if (!$pathPublicOrLocal) return null;

        // coba local (storage/app/..)
        if (Storage::disk('local')->exists($pathPublicOrLocal)) {
            $raw = Storage::disk('local')->get($pathPublicOrLocal);
            return 'data:image/png;base64,' . base64_encode($raw);
        }
        // coba public (storage/app/public/..)
        $pub = ltrim(preg_replace('#^public/#', '', $pathPublicOrLocal), '/');
        if (Storage::exists('public/' . $pub)) {
            $raw = Storage::get('public/' . $pub);
            return 'data:image/png;base64,' . base64_encode($raw);
        }
        return null;
    }

    /**
     * Render PDF dan embed TTD/Cap sebagai base64 <img>.
     */
    private function renderTugasPdfWithSign($tugas, array $ttdCfg = [], array $capCfg = []): string
{
    $kop  = \App\Models\MasterKopSurat::first();

    // Bangun aset + posisi default dari config tersimpan (BASE + OFFSET)
    $sign = $this->buildSignAssets($tugas, $kop);

    // base64 prioritas dari config jika 'show' true & file ada; jika tidak, pakai hasil buildSignAssets()
    $ttdImageB64 = !empty($ttdCfg['show'] ?? null) ? $this->b64FromStorage($ttdCfg['path'] ?? null) : ($sign['ttdImageB64'] ?? null);
    $capImageB64 = !empty($capCfg['show'] ?? null) ? $this->b64FromStorage($capCfg['path'] ?? null) : ($sign['capImageB64'] ?? null);

    // Posisi/ukuran FINAL = BASE + OFFSET (dalam mm)
    $ttdLeft = (int)($ttdCfg['base_left_mm'] ?? 108) + (int)($ttdCfg['offset_x'] ?? 0);
    $ttdTop  = (int)($ttdCfg['base_top_mm']  ?? 205) + (int)($ttdCfg['offset_y'] ?? 0);
    $ttdW    = (int)($ttdCfg['width_mm']     ?? ($sign['ttdW'] ?? 42));
    $ttdH    = (int)($ttdCfg['height_mm']    ?? ($sign['ttdH'] ?? 22));

    $capLeft = (int)($capCfg['base_left_mm'] ?? 125) + (int)($capCfg['offset_x'] ?? 0);
    $capTop  = (int)($capCfg['base_top_mm']  ?? 185) + (int)($capCfg['offset_y'] ?? 0);
    $capW    = (int)($capCfg['width_mm']     ?? ($sign['capW'] ?? 35));
    $capOpacity = isset($capCfg['opacity'])
        ? max(0, min(1, ((int)$capCfg['opacity']) / 100))
        : ($sign['capOpacity'] ?? 0.95);

    $penerimaList = $tugas->penerima->pluck('pengguna.nama_lengkap')->filter()->values()->all();

    $html = view('surat_tugas.surat_pdf', [
        'tugas'        => $tugas,
        'kop'          => $kop,
        'penerimaList' => $penerimaList,
        // assets base64
        'ttdImageB64'  => $ttdImageB64,
        'capImageB64'  => $capImageB64,
        // posisi/ukuran untuk _core
        'ttdLeft' => $ttdLeft, 'ttdTop' => $ttdTop, 'ttdW' => $ttdW, 'ttdH' => $ttdH,
        'capLeft' => $capLeft, 'capTop' => $capTop, 'capW' => $capW, 'capOpacity' => $capOpacity,
        'context'      => 'pdf',
        'disable_sign_layer' => false,
    ])->render();

    return \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html)
        ->setPaper('A4', 'portrait')
        ->setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled'      => true,
            'dpi'                  => 96,
            'chroot'               => public_path(),
        ])->output();
}



    private function getApprovalPreviewImagesForTugas($tugas): array
    {
        $user = auth()->user();

        // --- TTD (privat) ---
        $ttdB64 = null;
        if ($user && $user->signature && \Illuminate\Support\Facades\Storage::disk('local')->exists($user->signature->ttd_path)) {
            $raw = \Illuminate\Support\Facades\Storage::disk('local')->get($user->signature->ttd_path);
            $ttdB64 = 'data:image/png;base64,' . base64_encode($raw);
        }

        // --- CAP (institusional) ---
        // Ambil CAP langsung dari master_kop_surat (sementara tanpa service)
        $kop = \App\Models\MasterKopSurat::query()->first();

        $capB64 = null;
        if ($kop && $kop->cap_path) {
            $raw = null;
            if (\Illuminate\Support\Facades\Storage::exists('public/' . $kop->cap_path)) {
                $raw = \Illuminate\Support\Facades\Storage::get('public/' . $kop->cap_path);
            } elseif (\Illuminate\Support\Facades\Storage::disk('local')->exists($kop->cap_path)) {
                $raw = \Illuminate\Support\Facades\Storage::disk('local')->get($kop->cap_path);
            }
            if ($raw) $capB64 = 'data:image/png;base64,' . base64_encode($raw);
        }

        return [$ttdB64, $capB64, $kop];
    }

    private function buildSigningVisuals($tugas): array
{
    // 1) Ambil Kop
    // AWAL KODE PERBAIKAN
    $kop = \App\Models\MasterKopSurat::first();
    // AKHIR KODE PERBAIKAN

    // 2) Ambil TTD user (penandatangan)
    $user = $tugas->penandatanganUser ?? auth()->user();
    $ttdUrl = $ttdB64 = null;
    if ($user && $user->signature && Storage::disk('local')->exists($user->signature->ttd_path)) {
        $raw = Storage::disk('local')->get($user->signature->ttd_path);
        $ttdB64 = 'data:image/png;base64,' . base64_encode($raw);
        $ttdUrl = Storage::url($user->signature->ttd_path); // untuk web
    }

    // 3) Ambil cap dari Kop
    $capUrl = $capB64 = null;
    if ($kop && $kop->cap_path) {
        // coba public disk dulu
        if (Storage::exists('public/' . $kop->cap_path)) {
            $raw = Storage::get('public/' . $kop->cap_path);
            $capUrl = asset('storage/' . $kop->cap_path);
        } elseif (Storage::disk('local')->exists($kop->cap_path)) {
            $raw = Storage::disk('local')->get($kop->cap_path);
            $capUrl = Storage::url($kop->cap_path);
        } else {
            $raw = null;
        }
        if ($raw) $capB64 = 'data:image/png;base64,' . base64_encode($raw);
    }

    // 4) Posisi dari config (mm) dengan default wajar relatif ke .ttd
    $ttdc = $tugas->ttd_config ?? [];
    $capc = $tugas->cap_config ?? [];
    $ttd = [
        'show' => (bool)($ttdc['show'] ?? false),
        'x'    => (int)($ttdc['offset_x'] ?? -2),   // mm dari kiri .ttd
        'y'    => (int)($ttdc['offset_y'] ?? 28),   // mm dari atas .ttd
        'w'    => (int)($ttdc['width_mm'] ?? ($user->signature->default_width_mm ?? 35)),
    ];
    $cap = [
        'show' => (bool)($capc['show'] ?? false),
        'x'    => (int)($capc['offset_x'] ?? 62),
        'y'    => (int)($capc['offset_y'] ?? 30),
        'w'    => (int)($capc['width_mm'] ?? ($kop->cap_default_width_mm ?? 30)),
        'op'   => max(0, min(1, (($capc['opacity'] ?? $kop->cap_opacity ?? 85) / 100))),
    ];

    return compact('kop', 'ttdB64', 'capB64', 'ttdUrl', 'capUrl', 'ttd', 'cap');
}
    
    public function show($id)
    {
        $user    = Auth::user();
        $peranId = $user->peran_id;

        $tugas = TugasHeader::with([
            'pembuat',
            'penandatanganUser',
            'asalSurat',
            'penerima.pengguna'
        ])->findOrFail($id);

        if ($peranId === 1 && $tugas->dibuat_oleh !== $user->id) {
            return redirect()->route('surat_tugas.index')
                ->with('error', 'Anda tidak berhak melihat detail ini.');
        }

        if (in_array($peranId, [2, 3])) {
            if (!($tugas->status_surat === 'pending' && $tugas->penandatangan == $user->id)) {
                return redirect()->route('surat_tugas.index')
                    ->with('error', 'Anda hanya dapat melihat surat yang menunggu persetujuan Anda.');
            }
        }

        if ($peranId === 4) {
            $isPenerima = $tugas->penerima->contains('pengguna_id', $user->id);
            if (!($tugas->status_surat === 'disetujui' && $isPenerima)) {
                return redirect()->route('surat_tugas.index')
                    ->with('error', 'Anda hanya dapat melihat surat yang sudah disetujui untuk Anda.');
            }
            TugasPenerima::where('tugas_id', $id)
                ->where('pengguna_id', $user->id)
                ->update(['dibaca' => 1]);
        }

        $penerimaList = $tugas->penerima->pluck('pengguna.nama_lengkap')->all();

        [$ttdPreviewB64, $capPreviewB64, $kop] = $this->getApprovalPreviewImagesForTugas($tugas);
        return view('surat_tugas.show', [
            'tugas' => $tugas,
            'ttdPreviewB64' => $ttdPreviewB64,
            'capPreviewB64' => $capPreviewB64,
            'kop' => $kop,
        ]);
    }

    public function edit($id)
    {
        $user    = Auth::user();
        $peranId = $user->peran_id;
        $tugas = TugasHeader::with(['penerima.pengguna'])->findOrFail($id);

        $nomorParts = explode('/', $tugas->nomor);
        $baseNomor = '/' . implode('/', array_slice($nomorParts, 1));

        if ($peranId === 1) {
            if (!($tugas->dibuat_oleh === $user->id && $tugas->status_surat === 'draft')) {
                return redirect()->route('surat_tugas.index')
                    ->with('error', 'Anda tidak berhak mengedit surat ini.');
            }
        } elseif (in_array($peranId, [2, 3])) {
            if (!($tugas->status_surat === 'pending' && $tugas->penandatangan == $user->id)) {
                return redirect()->route('surat_tugas.index')
                    ->with('error', 'Anda hanya dapat merevisi surat yang menunggu persetujuan Anda.');
            }
        } else {
            return redirect()->route('surat_tugas.index')
                ->with('error', 'Anda tidak berhak mengakses form edit ini.');
        }

        $deps = $this->getFormDependencies();
        extract($deps);

        $data = [
            'nomor'            => $tugas->nomor,
            'tanggal_asli'     => $tugas->tanggal_asli?->format('Y-m-d\TH:i'),
            'nama_pembuat'     => $tugas->nama_pembuat,
            'asal_surat'       => $tugas->asal_surat,
            'jenis_tugas'      => $tugas->jenis_tugas,
            'tugas'            => $tugas->tugas,
            'status_penerima'  => $tugas->status_penerima,
            'detail_tugas'     => $tugas->detail_tugas,
            'waktu_mulai'      => $tugas->waktu_mulai?->format('Y-m-d\TH:i'),
            'waktu_selesai'    => $tugas->waktu_selesai?->format('Y-m-d\TH:i'),
            'tempat'           => $tugas->tempat,
            'penandatangan'    => $tugas->penandatangan,
            'penerima_ids'     => $tugas->penerima->pluck('pengguna_id')->all(),
            'tahun'            => $tugas->tahun,
            'semester'         => $tugas->semester,
            'no_bin'           => $tugas->no_bin,
            'no_surat_manual'  => $tugas->no_surat_manual,
            'nama_umum'        => $tugas->nama_umum,
            'tembusan'         => $tugas->tembusan,
            'redaksi_pembuka'  => $tugas->redaksi_pembuka,
            'penutup'          => $tugas->penutup,
            'klasifikasi_surat_id' => $tugas->klasifikasi_surat_id ?? null,
        ];

        return view('surat_tugas.edit', compact(
            'admins',
            'pejabat',
            'users',
            'taskMaster',
            'klasifikasi',
            'data',
            'tugas',
            'baseNomor'
        ));
    }

    // GANTI SELURUH METHOD update() ANDA DENGAN INI
public function update(Request $request, $id)
{
    \Log::info('Proses update Surat Tugas #' . $id . ' dimulai.', $request->all());
    $tugas = TugasHeader::with(['penerima'])->findOrFail($id);
    $mode = $this->resolveMode($request);
    $oldStatus = $tugas->status_surat;
    $newStatus = $oldStatus;
    $nextApprover = $tugas->next_approver;

    $rules = [
        'klasifikasi_surat_id' => 'required|exists:klasifikasi_surat,id',
        'nama_umum' => 'required|string|max:255',
        'jenis_tugas' => 'required|string',
        'tugas' => 'required|string',
        'detail_tugas' => 'nullable|string|max:65000',
        'redaksi_pembuka' => 'nullable|string|max:2000',
        'penutup' => 'nullable|string|max:1000',
        'penerima_internal' => 'nullable|array',
        'penerima_internal.*' => 'exists:pengguna,id',
        'penerima_eksternal' => 'nullable|array',
        'penerima_eksternal.*.nama' => 'required_with:penerima_eksternal|string|max:255',
        'penerima_eksternal.*.jabatan' => 'required_with:penerima_eksternal|string|max:255',
        'penandatangan' => 'required|exists:pengguna,id',
        'status_penerima' => 'nullable',
        'waktu_mulai' => 'nullable|date',
        'waktu_selesai' => 'nullable|date|after_or_equal:waktu_mulai',
        'tempat' => 'nullable|string|max:255',
        'tahun' => 'required|integer|digits:4',
        'bulan' => 'required|string|max:10',
        'semester' => 'required|string|in:Ganjil,Genap',
        'nama_pembuat' => 'required|exists:pengguna,id',
        'asal_surat' => 'required|exists:pengguna,id',
        'nomor' => ['required', 'string', Rule::unique('tugas_header')->ignore($id)],
    ];
    $validated = $request->validate($rules);
    $segmen = $validated['status_penerima'] ?? null;
    if (is_array($segmen)) {
        $segmen = implode(',', array_unique(array_map('strval', $segmen)));
    }
    if ($mode === 'submit' && $oldStatus === 'draft') {
        $newStatus = 'pending';
        $nextApprover = $validated['penandatangan'] ?? null;
    }

    DB::beginTransaction();
    try {
        $tugas->update([
            'nomor' => $validated['nomor'],
            'bulan' => $validated['bulan'],
            'tahun' => $validated['tahun'],
            'nama_umum' => $validated['nama_umum'],
            'klasifikasi_surat_id' => $validated['klasifikasi_surat_id'],
            'status_surat' => $newStatus,
            'nama_pembuat' => $validated['nama_pembuat'],
            'asal_surat' => $validated['asal_surat'],
            'jenis_tugas' => $validated['jenis_tugas'],
            'tugas' => $validated['tugas'],
            'detail_tugas' => $validated['detail_tugas'] ?? null,
            'status_penerima' => $segmen,
            'redaksi_pembuka' => $validated['redaksi_pembuka'] ?? null,
            'penutup' => $validated['penutup'] ?? null,
            'penandatangan' => $validated['penandatangan'] ?? null,
            'next_approver' => $nextApprover,
            'waktu_mulai' => $validated['waktu_mulai'] ?? null,
            'waktu_selesai' => $validated['waktu_selesai'] ?? null,
            'tempat' => $validated['tempat'] ?? null,
            'submitted_at' => ($oldStatus === 'draft' && $newStatus === 'pending') ? now() : $tugas->submitted_at,
            'semester' => $validated['semester'],
        ]);
        $tugas->penerima()->delete();
        if (!empty($validated['penerima_internal'])) {
            $internalIds = array_values(array_unique($validated['penerima_internal']));
            $users = \App\Models\User::with('peran')->find($internalIds);
            foreach ($users as $user) {
                $tugas->penerima()->create(['pengguna_id' => $user->id, 'nama_penerima' => '', 'jabatan_penerima' => $user->jabatan ?: ($user->peran->deskripsi ?? null)]);
            }
        }
        if (!empty($validated['penerima_eksternal'])) {
            $seen = [];
            foreach ($validated['penerima_eksternal'] as $p) {
                $key = strtolower(trim(($p['nama'] ?? '') . '|' . ($p['jabatan'] ?? '')));
                if (isset($seen[$key])) continue;
                $seen[$key] = true;
                $tugas->penerima()->create(['pengguna_id' => null, 'nama_penerima' => $p['nama'], 'jabatan_penerima' => $p['jabatan']]);
            }
        }
        if ($oldStatus === 'draft' && $newStatus === 'pending') {
            app(NotifikasiService::class)->notifyApprovalRequest($tugas);
        }
        DB::commit();
        $message = $newStatus === 'pending' ? 'Surat tugas berhasil diperbarui dan diajukan.' : 'Perubahan draft berhasil disimpan.';
        return redirect()->route('surat_tugas.mine')->with('success', $message);
    } catch (\Exception $e) {
        DB::rollBack();
        return back()->withInput()->with('error', 'Terjadi kesalahan saat update: ' . $e->getMessage());
    }
}

    public function destroy($id)
    {
        $user    = Auth::user();
        $peranId = $user->peran_id;

        $tugas = TugasHeader::findOrFail($id);
        if (!($peranId === 1 && $tugas->dibuat_oleh === $user->id && $tugas->status_surat === 'draft')) {
            return redirect()->route('surat_tugas.mine')
                ->with('error', 'Anda tidak berhak menghapus surat ini.');
        }

        $tugas->delete();
        return redirect()->route('surat_tugas.mine')->with('success', 'Surat tugas berhasil dihapus.');
    }

    public function highlight($id)
    {
        $tugas = TugasHeader::with([
            'pembuat',
            'penandatanganUser',
            'asalSurat',
            'penerima.pengguna'
        ])->findOrFail($id);
        $penerimaList = $tugas->penerima->pluck('pengguna.nama_lengkap')->all();

        return response()
            ->view('surat_tugas.highlight', compact('tugas', 'penerimaList'))
            ->header('X-Frame-Options', 'ALLOWALL');
    }


    public function downloadPdf($id)
{
    $tugas = \App\Models\TugasHeader::with([
        'pembuat','penandatanganUser','asalSurat','penerima.pengguna.peran','subTugas'
    ])->findOrFail($id);

    // ambil config tersimpan (handle string/json)
    $ttdCfg = $this->readCfg($tugas->ttd_config ?? []);
    $capCfg = $this->readCfg($tugas->cap_config ?? []);

    $bytes = $this->renderTugasPdfWithSign($tugas, $ttdCfg, $capCfg);

    $safeNomor = preg_replace('/[\/\\\\]+/', '-', (string)($tugas->nomor ?? 'TanpaNomor'));
    return response($bytes, 200, [
        'Content-Type'        => 'application/pdf',
        'Content-Disposition' => 'inline; filename="SuratTugas_'.$safeNomor.'.pdf"',
        'X-Frame-Options'     => 'ALLOWALL',
    ]);
}

    public function preview($tugasId)
{
    $tugas = \App\Models\TugasHeader::with([
        'pembuat', 'penandatanganUser', 'asalSurat',
        'penerima.pengguna.peran', 'subTugas',
    ])->findOrFail($tugasId);

    $penerimaList = $tugas->penerima->pluck('pengguna.nama_lengkap')->filter()->values()->all();
    $kop = \App\Models\MasterKopSurat::first();

    // >>>> tambahkan ini
    $sign = $this->buildSignAssets($tugas, $kop);

    return response()->view('surat_tugas.preview', array_merge([
        'tugas'        => $tugas,
        'penerimaList' => $penerimaList,
        'kop'          => $kop,
        'context'      => 'web',
        'disable_sign_layer' => false, // preview biasa harus menampilkan sign-layer
    ], $sign))->header('X-Frame-Options', 'ALLOWALL');
}

}
