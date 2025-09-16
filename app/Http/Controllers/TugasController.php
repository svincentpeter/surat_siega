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
                } catch (\Throwable $e2) { /* noop */
                }
            }
        }

        // 3) Jika sub_tugas ketemu, ambil detail di bawahnya
        if ($sub && isset($sub->id)) {
            // Prioritas cari detail yang namanya "masuk akal" untuk reviewer/publikasi, lalu fallback first()
            $detail = null;

            $cariKataKunci = [
                'jurnal nasional',
                'artikel jurnal nasional',
                'artikel nasional',
                'reviewer jurnal nasional',
                'review jurnal nasional',
                'review artikel nasional',
                'publikasi nasional'
            ];

            // a) Cari by keywords pada TugasDetail::where('sub_tugas_id', ...)
            try {
                $dq = TugasDetail::query()->where('sub_tugas_id', $sub->id);
                foreach ($cariKataKunci as $kw) {
                    $try = (clone $dq)->whereRaw('LOWER(nama) LIKE ?', ['%' . mb_strtolower($kw) . '%'])->first();
                    if ($try) {
                        $detail = $try;
                        break;
                    }
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
                        if ($row) {
                            $detail = (object) ['id' => $row->id];
                            break;
                        }
                    }
                    if (!$detail) {
                        $row = DB::table('tugas_detail')->where('sub_tugas_id', $sub->id)->orderBy('id')->first();
                        if ($row) $detail = (object) ['id' => $row->id];
                    }
                } catch (\Throwable $e2) { /* noop */
                }
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
            } catch (\Throwable $e2) { /* noop */
            }
        }

        // 5) Fallback terakhir: MIN(id)
        try {
            $minId = TugasDetail::min('id');
            if ($minId) return (int) $minId;
        } catch (\Throwable $e) {
            try {
                $minId = DB::table('tugas_detail')->min('id');
                if ($minId) return (int) $minId;
            } catch (\Throwable $e2) { /* noop */
            }
        }

        \Log::warning('resolveDetailTugasId: gagal memetakan, semua fallback habis', [
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
    public function approve(Request $request, TugasHeader $tugas)
{
    $user = auth()->user();
    if (!in_array((int)$user->peran_id, [2, 3], true)) {
        abort(403, 'Hanya Dekan/Wakil Dekan yang dapat menyetujui surat.');
    }

    // TIDAK PERLU FindOrFail($id) lagi. $tugas sudah ada dari Route Model Binding.
    if (!in_array($tugas->status_surat, ['pending', 'draft'], true)) {
            return back()->with('error', 'Surat ini sudah diproses dan tidak bisa disetujui ulang.');
        }

        $validated = $request->validate([
            'ttd_w_mm'    => 'required|integer|min:30|max:60',
            'cap_w_mm'    => 'required|integer|min:25|max:45',
            'cap_opacity' => 'required|numeric|min:0.7|max:1.0',
        ]);

        DB::beginTransaction();
        try {
            $tugas->ttd_config = null;
            $tugas->cap_config = null;
            $tugas->ttd_w_mm    = $validated['ttd_w_mm'];
            $tugas->cap_w_mm    = $validated['cap_w_mm'];
            $tugas->cap_opacity = $validated['cap_opacity'];

            if (empty($tugas->tanggal_surat)) {
                $tugas->tanggal_surat = now()->toDateString();
            }
            $tugas->status_surat = 'disetujui';
            $tugas->signed_at    = now();
            $tugas->save();

            $pdfBytes = $this->renderTugasPdfWithSign($tugas);
            $pdfPath  = "private/surat_tugas/signed/{$tugas->id}_" . md5($tugas->nomor) . ".pdf";
            Storage::disk('local')->put($pdfPath, $pdfBytes);
            $tugas->signed_pdf_path = $pdfPath;
            $tugas->save();

            app(NotifikasiService::class)->notifyApproved($tugas);

            DB::commit();
            return redirect()->route('surat_tugas.approve_list')->with('success', 'Surat berhasil disetujui & ditandatangani.');
        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error('Gagal approve surat tugas #' . $tugas->id, ['error' => $e->getMessage()]);
            return back()->with('error', 'Terjadi kesalahan sistem saat menyetujui surat.');
        }
    }



    // ====== helper kecil di controller ======

    /**
     * Menyiapkan aset visual (gambar base64 & ukuran) untuk TTD dan Cap.
     * Didesain untuk sistem baru tanpa offset.
     */
    private function getSigningAssets(TugasHeader $tugas): array
    {
        // 1. Ambil gambar TTD dari penandatangan
        $ttdImageB64 = null;
        $penandatangan = $tugas->penandatanganUser;
        if ($penandatangan && $penandatangan->signature && !empty($penandatangan->signature->ttd_path)) {
            $ttdImageB64 = $this->b64FromStorage($penandatangan->signature->ttd_path);
        }

        // 2. Ambil gambar Cap dari Master Kop Surat
        $capImageB64 = null;
        $kop = MasterKopSurat::query()->first();
        if ($kop && !empty($kop->cap_path)) {
            $capImageB64 = $this->b64FromStorage($kop->cap_path);
        }

        // 3. Ambil ukuran & opasitas dari database, dengan fallback default yang aman
        $ttdW = $tugas->ttd_w_mm ?? 42;       // Default 42mm
        $capW = $tugas->cap_w_mm ?? 35;       // Default 35mm
        $capOpacity = $tugas->cap_opacity ?? 0.95; // Default 0.95

        return compact(
            'ttdImageB64',
            'capImageB64',
            'ttdW',
            'capW',
            'capOpacity',
            'kop' // kita kembalikan juga objek $kop untuk keperluan lain
        );
    }

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
    private function renderTugasPdfWithSign(TugasHeader $tugas): string
    {
        // Panggil helper baru kita yang sudah bersih
        $signAssets = $this->getSigningAssets($tugas);

        $penerimaList = $tugas->penerima->pluck('pengguna.nama_lengkap')->filter()->values()->all();

        $html = view('surat_tugas.surat_pdf', array_merge(
            [
                'tugas'        => $tugas,
                'penerimaList' => $penerimaList,
            ],
            $signAssets // Langsung gabungkan semua aset dari helper
        ))->render();

        return Pdf::loadHTML($html)
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

    public function show(Request $request, TugasHeader $tugas)
    {
        // Kita tidak perlu lagi mencari $tugas, karena sudah otomatis di-inject oleh Laravel
        $tugas->load(['pembuat', 'penandatanganUser.peran', 'penerima.pengguna']);


        // Di sini Anda bisa menambahkan logika otorisasi (Gate)
        // Contoh: Gate::authorize('view', $tugas);

        // Panggil helper utama untuk mendapatkan semua aset visual
        $assets = $this->getSigningAssets($tugas);

        // Siapkan data terstruktur untuk pratinjau di halaman approval
        // Ambil nilai dari request (untuk live preview) atau dari database/default
        $previewData = [
            'ttd_image_b64' => $assets['ttdImageB64'],
            'cap_image_b64' => $assets['capImageB64'],
            'ttd_w_mm'      => $request->input('ttd_w_mm', $assets['ttdW']),
            'cap_w_mm'      => $request->input('cap_w_mm', $assets['capW']),
            'cap_opacity'   => $request->input('cap_opacity', $assets['capOpacity']),
        ];

        // Jika ini adalah request AJAX untuk live preview, kembalikan hanya partialnya
        if ($request->input('partial') === 'true') {
            return view('surat_tugas.partials.approve-preview', [ // <-- Tambahkan .partials di sini
                'tugas'   => $tugas,
                'kop'     => $assets['kop'],
                'preview' => $previewData,
            ]);
        }

        // Jika request biasa, render seluruh halaman show.blade.php
        return view('surat_tugas.show', [
            'tugas'   => $tugas,
            'kop'     => $assets['kop'],
            'preview' => $previewData, // Kirim data pratinjau terstruktur
        ]);
    }

    public function edit(TugasHeader $tugas)
    {
        $user    = Auth::user();
        $peranId = $user->peran_id;
        // Variabel $tugas sudah otomatis tersedia karena Route Model Binding
        // Kita hanya perlu memastikan relasinya sudah dimuat jika perlu
        $tugas->load(['penerima.pengguna']);

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
    public function update(Request $request, TugasHeader $tugas)
    {
        // $tugas sudah ada dari Route Model Binding. Kita bisa langsung pakai ID-nya.
        \Log::info('Proses update Surat Tugas #' . $tugas->id . ' dimulai.', $request->all());
        // Tidak perlu FindOrFail lagi. Muat relasi jika perlu (meskipun update() di bawah tidak membutuhkannya).
        $tugas->load(['penerima']);
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
            'nomor' => ['required', 'string', Rule::unique('tugas_header')->ignore($tugas->id)], 
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

    public function destroy(TugasHeader $tugas)
    {
        $user    = Auth::user();
        $peranId = $user->peran_id;

        // Hapus baris FindOrFail($id) di atas. $tugas sudah ada dari Laravel.
        if (!($peranId === 1 && $tugas->dibuat_oleh === $user->id && $tugas->status_surat === 'draft')) {
            return redirect()->route('surat_tugas.mine')
                ->with('error', 'Anda tidak berhak menghapus surat ini.');
        }

        $tugas->delete();
        return redirect()->route('surat_tugas.mine')->with('success', 'Surat tugas berhasil dihapus.');
    }

    public function highlight(TugasHeader $tugas)
    {
        $tugas->load([
            'pembuat',
            'penandatanganUser',
            'asalSurat',
            'penerima.pengguna'
        ]);
        $penerimaList = $tugas->penerima->pluck('pengguna.nama_lengkap')->all();

        return response()
            ->view('surat_tugas.highlight', compact('tugas', 'penerimaList'))
            ->header('X-Frame-Options', 'ALLOWALL');
    }


    public function downloadPdf(TugasHeader $tugas)
    {
        // $tugas sudah ada dari Route Model Binding. Kita hanya perlu load relasi.
        $tugas->load([
            'pembuat',
            'penandatanganUser',
            'penerima.pengguna.peran',
            'tugasDetail.subTugas',
        ]);

        // Cukup panggil renderTugasPdfWithSign yang sudah diperbaiki
        $bytes = $this->renderTugasPdfWithSign($tugas);

        $safeNomor = preg_replace('/[\/\\\\]+/', '-', (string)($tugas->nomor ?? 'TanpaNomor'));
        return response($bytes, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="SuratTugas_' . $safeNomor . '.pdf"',
        ]);
    }

    public function preview($tugasId)
    {
        $tugas = TugasHeader::with([
            'pembuat',
            'penandatanganUser',
            'penerima.pengguna.peran',
            'tugasDetail.subTugas',
        ])->findOrFail($tugasId);

        // Panggil helper baru kita
        $signAssets = $this->getSigningAssets($tugas);
        $penerimaList = $tugas->penerima->pluck('pengguna.nama_lengkap')->filter()->values()->all();

        return response()->view('surat_tugas.preview', array_merge(
            [
                'tugas'        => $tugas,
                'penerimaList' => $penerimaList,
            ],
            $signAssets // Langsung gabungkan semua aset dari helper
        ))->header('X-Frame-Options', 'ALLOWALL');
    }
}
