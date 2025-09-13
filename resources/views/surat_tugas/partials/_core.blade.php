@php
  // ==== context: 'pdf' | 'web' (default 'web') ====
  $context = $context ?? 'web';

  // Helper path gambar (header/logo/foot) untuk WEB/PDF
  $img = function ($path) use ($context) {
      if (!$path) return null;
      return $context === 'pdf' ? public_path('storage/'.$path) : asset('storage/'.$path);
  };

  // Base64 untuk DomPDF (stabil)
  $img64 = function ($path) {
      if (!$path) return null;
      $file = public_path('storage/'.$path);
      if (!is_file($file)) return null;
      $ext = pathinfo($file, PATHINFO_EXTENSION);
      return 'data:image/'.$ext.';base64,'.base64_encode(file_get_contents($file));
  };

  // Penerima list fallback
  if (!isset($penerimaList) || !is_array($penerimaList)) {
      try { $penerimaList = $tugas->penerima?->pluck('pengguna.nama_lengkap')->filter()->values()->all() ?? []; }
      catch (\Throwable $e) { $penerimaList = []; }
  }

  // Status tampilan berdasarkan peran penerima
  $roleNames = collect($tugas->penerima ?? [])->map(function ($p) {
      $desc = optional(optional($p->pengguna)->peran)->deskripsi;
      $name = optional(optional($p->pengguna)->peran)->nama;
      return $desc ?: ($name ? \Illuminate\Support\Str::headline($name) : null);
  })->filter()->unique()->values()->all();
  $statusDisplay = $roleNames ? implode(', ', $roleNames)
                              : (\Illuminate\Support\Str::headline($tugas->status_penerima ?? '') ?: '-');

  // Tugas spesifik
  $tugasSpesifik = optional($tugas->subTugas)->nama ?? ($tugas->tugas ?? $tugas->nama_umum ?? '-');

  // ============ CONFIG & BASE64 TTD / CAP ============
  // Variabel dari controller:
  // $ttdImageB64, $ttdCfg (width_mm,height_mm,offset_x,offset_y)
  // $capImageB64, $capCfg (width_mm,offset_x,offset_y,opacity)
  $ttdCfg = $ttdCfg ?? [];
  $capCfg = $capCfg ?? [];

  // Nilai default posisi (mm) – kira-kira di area bawah tanda tangan
  $baseTtdLeft = 135;   // mm dari kiri
  $baseTtdTop  = 222;   // mm dari atas
  $baseCapLeft = 152;   // mm
  $baseCapTop  = 214;   // mm

  $ttdLeft = $baseTtdLeft + (int)($ttdCfg['offset_x'] ?? 0);
  $ttdTop  = $baseTtdTop  + (int)($ttdCfg['offset_y'] ?? 0);
  $ttdW    = (int)($ttdCfg['width_mm']  ?? 35);
  $ttdH    = (int)($ttdCfg['height_mm'] ?? 15);

  $capLeft = $baseCapLeft + (int)($capCfg['offset_x'] ?? 0);
  $capTop  = $baseCapTop  + (int)($capCfg['offset_y'] ?? 0);
  $capW    = (int)($capCfg['width_mm'] ?? 30);
  $capOpacity = max(0, min(100, (int)($capCfg['opacity'] ?? 85))) / 100;
@endphp

@if($context === 'pdf')
  <style>
    @page { margin: 140px 40px 100px 40px; } /* top right bottom left */
    body { font-family: "Times New Roman", Times, serif; }
    .judul { text-align:center; font-weight:700; font-size:22px; text-decoration:underline; margin-top:6px; }
    .nomor { text-align:center; margin:6px 0 20px; }
    .isi { margin:20px 0 0 60px; font-size:16px; }
    .ttd { text-align:right; margin-right:60px; margin-top:40px; position:relative; }
    table { border-collapse:collapse; }
    td { padding:4px 8px; vertical-align:top; }

    /* Header composed */
    .kop-wrap{ position: fixed; top:-118px; left:40px; right:40px; padding-bottom:8px; border-bottom:2px solid #000; }
    .kop-tbl{ width:100%; border-collapse:collapse; }
    .kop-td-text{ width: calc(100% - 130px); }
    .kop-td-logo{ width:130px; text-align:right; border-left:2px solid #000; padding-left:12px; }

    :root { --brand-ungu:#6A2C8E; }
    .kop-text{ line-height:1.25; text-align:right; }
    .kop-text .l1{ font-weight:800; font-size:21px; letter-spacing:.4px; color:var(--brand-ungu); }
    .kop-text .l2{ font-weight:800; font-size:15px; margin-top:-2px; color:var(--brand-ungu); }
    .kop-text .addr{ font-size:11px; margin-top:6px; color:#111; text-align:right; }
    .logo-kanan{ width:92px; height:auto; }

    /* Layer tanda tangan & cap (absolute ke halaman) */
    .sign-layer{ position: fixed; left:0; top:0; width:210mm; height:297mm; pointer-events:none; }
    .sign{ position:absolute; }
  </style>
@else
  <style>
    body { margin:0; font-family:"Times New Roman", Times, serif; background:#f6f7fb; }
    .sheet{ width:210mm; min-height:297mm; margin:8mm auto; background:#fff; position:relative;
            box-shadow:0 10px 30px rgba(0,0,0,.08); padding:40mm 15mm 25mm 15mm; }
    .judul { text-align:center; font-weight:700; font-size:22px; text-decoration:underline; margin-top:6px; }
    .nomor { text-align:center; margin:6px 0 20px; }
    .isi { margin:20px 0 0 60px; font-size:16px; }
    .ttd { text-align:right; margin-right:60px; margin-top:40px; position:relative; }
    table{ border-collapse:collapse; }
    td{ padding:4px 8px; vertical-align:top; }

    .kop-wrap{ position:absolute; top:10mm; left:15mm; right:15mm; padding-bottom:8px; border-bottom:2px solid #000; }
    .kop-tbl{ width:100%; border-collapse:collapse; }
    .kop-td-text{ width: calc(100% - 130px); }
    .kop-td-logo{ width:130px; text-align:right; border-left:2px solid #000; padding-left:12px; }
    :root { --brand-ungu:#6A2C8E; }
    .kop-text{ line-height:1.25; text-align:right; }
    .kop-text .l1{ font-weight:800; font-size:21px; letter-spacing:.4px; color:var(--brand-ungu); }
    .kop-text .l2{ font-weight:800; font-size:15px; margin-top:-2px; color:var(--brand-ungu); }
    .kop-text .addr{ font-size:11px; margin-top:6px; color:#111; text-align:right; }
    .logo-kanan{ width:92px; height:auto; }

    .sign-layer{ position:absolute; left:0; top:0; width:210mm; height:297mm; pointer-events:none; }
    .sign{ position:absolute; }
  </style>
  <div class="sheet">
@endif

{{-- ====================== HEADER ====================== --}}
@if($kop && ($kop->mode ?? 'image') === 'composed')
  <div class="kop-wrap">
    <table class="kop-tbl">
      <tr>
        <td class="kop-td-text">
          <div class="kop-text">
            <div class="l1">{{ strtoupper($kop->judul_atas ?? '') }}</div>
            <div class="l2">{{ strtoupper($kop->subjudul ?? '') }}</div>
            <div class="addr">
              {{ $kop->alamat ?? '' }}<br>
              Telp. {{ $kop->telepon ?? '' }}@if(!empty($kop?->fax)) , Fax. {{ $kop->fax }} @endif<br>
              email: {{ $kop->email ?? '' }} @if(!empty($kop?->website)) | {{ $kop->website }} @endif
            </div>
          </div>
        </td>
        <td class="kop-td-logo">
          @if(($kop->tampilkan_logo_kanan ?? false) && !empty($kop->logo_kanan_path))
            @if($context === 'pdf')
              @php $src = $img64($kop->logo_kanan_path); @endphp
              @if($src)<img class="logo-kanan" src="{{ $src }}">@endif
            @else
              <img class="logo-kanan" src="{{ $img($kop->logo_kanan_path) }}">
            @endif
          @endif
        </td>
      </tr>
    </table>
  </div>
@elseif(!empty($kop?->header_path))
  <div class="kop-header" style="{{ $context==='pdf' ? 'position:fixed; top:-100px; left:0; right:0;' : 'position:absolute; top:0; left:0; right:0;' }}">
    <img src="{{ $img($kop->header_path) }}" style="width:100%;">
  </div>
@endif

{{-- ====================== JUDUL & NOMOR ====================== --}}
<div class="judul">SURAT TUGAS</div>
<div class="nomor">Nomor : {{ $tugas->nomor ?? '-' }}</div>

{{-- ====================== ISI SURAT ====================== --}}
<div style="margin-bottom:2.2em; margin-left:10px;">
  Dekan Fakultas Ilmu Komputer Universitas Katolik Soegijapranata dengan ini memberikan tugas kepada:
  <table style="margin-top:1.2em;">
    <tr>
      <td style="width:75px;">Nama</td><td style="width:10px;">:</td>
      <td>{{ !empty($penerimaList) ? implode(', ', $penerimaList) : '—' }}</td>
    </tr>
    <tr><td>Status</td><td>:</td><td>{{ $statusDisplay }}</td></tr>
    <tr><td>Tugas</td><td>:</td><td>{{ $tugasSpesifik }}</td></tr>
    <tr><td>Waktu</td><td>:</td><td>{{ $tugas->semester ?? '-' }} {{ $tugas->tahun ?? '-' }}</td></tr>
  </table>

  <div style="margin-top:0.8em;">
    Harap melaksanakan tugas dengan sebaik-baiknya dan penuh tanggung jawab serta memberikan laporan setelah selesai melaksanakan tugas.
  </div>
</div>

{{-- ====================== SIGN LAYER (TTD & CAP) ====================== --}}
@php
  // Matikan sign-layer hanya bila diminta dan konteks web (approve panel pakai overlay)
  $isWeb = ($context ?? 'web') === 'web';
  $disableSignLayer = ($disable_sign_layer ?? false) && $isWeb;
@endphp

@if(!$disableSignLayer)
  <div class="sign-layer">
    {{-- CAP --}}
    @if(!empty($capImageB64))
      <img class="sign cap"
           src="{{ $capImageB64 }}"
           style="position:absolute;
                  left: {{ $capLeft ?? 125 }}mm;
                  top:  {{ $capTop  ?? 185 }}mm;
                  width:{{ $capW    ?? 35  }}mm;
                  opacity:{{ $capOpacity ?? 0.95 }};">
    @endif

    {{-- TTD --}}
    @if(!empty($ttdImageB64))
      <img class="sign ttd"
           src="{{ $ttdImageB64 }}"
           style="position:absolute;
                  left: {{ $ttdLeft ?? 108 }}mm;
                  top:  {{ $ttdTop  ?? 205 }}mm;
                  width:{{ $ttdW    ?? 42  }}mm;
                  height:{{ $ttdH   ?? 22  }}mm;">
    @endif
  </div>
@endif
{{-- ==================== /SIGN LAYER (TTD & CAP) ======================= --}}


{{-- ====================== BLOK TTD (TEKS) ====================== --}}
<div class="ttd">
  Semarang,
  {{ isset($tugas->tanggal_surat)
        ? \Carbon\Carbon::parse($tugas->tanggal_surat)->translatedFormat('d F Y')
        : \Carbon\Carbon::now()->translatedFormat('d F Y') }}
  <br>a.n. Dekan Fakultas Ilmu Komputer<br>Wakil Dekan Fakultas Ilmu Komputer
  <br><br><br><br>
  <strong>{{ $tugas->penandatanganUser->nama_lengkap ?? '-' }}</strong><br>
  NPP. {{ $tugas->penandatanganUser->npp ?? '-' }}
</div>

@if($context === 'web')
  </div>
@endif