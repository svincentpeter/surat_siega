@extends('layouts.app')

@section('title', 'Detail Surat Tugas: ' . $tugas->nomor)

@push('styles')
<style>
  body { background:#f7faff; }
  /* Kertas A4 untuk tampilan web */
  .paper-a4 {
      background:#fff;
      width: 794px;          /* ~210mm @96dpi */
      min-height: 1123px;    /* ~297mm @96dpi */
      margin: 0 auto 1.25rem;
      box-shadow: 0 1px 6px rgba(0,0,0,.08);
      position: relative;    /* supaya overlay TTD/Cap nempel */
      padding: 32px 36px;    /* mirip margin PDF */
      color: #000;
      font-family: 'Times New Roman', Times, serif;
      overflow: visible;     /* jangan potong overlay */
  }
  /* Gambar overlay pada lembar (kiri) */
  .overlay-sign {
      position: absolute;
      pointer-events: none;
      z-index: 50;
  }
  .overlay-cap {
      position: absolute;
      pointer-events: none;
      z-index: 49;
      mix-blend-mode: multiply;
  }
  /* Panel kanan */
  .info-card .card-header { background:#fff; }
</style>
@endpush

@section('content_header')
<div class="row mb-2">
  <div class="col-sm-6"><h1>Detail Surat Tugas</h1></div>
  <div class="col-sm-6">
    <ol class="breadcrumb float-sm-right">
      <li class="breadcrumb-item"><a href="{{ route('surat_tugas.index') }}">Surat Tugas</a></li>
      <li class="breadcrumb-item active">Detail</li>
    </ol>
  </div>
</div>
@endsection

@section('content')
<div class="row">
  {{-- ===== Kolom Kiri: Lembar Surat (pakai partial _core agar kop muncul) ===== --}}
  <div class="col-lg-8">
    <div id="surat-wrap" class="paper-a4">
      {{-- _core bertugas menggambar isi + kop. Kita kirim context=web --}}
      @include('surat_tugas.partials._core', [
  'tugas'   => $tugas,
  'kop'     => $kop ?? null,
  'context' => 'web',
  'disable_sign_layer' => true,  // <- penting: biar tidak dobel dgn overlay
  // biarkan posisi default dari _core; overlay (#live-ttd/#live-cap) yang tampil
])

      {{-- Overlay live TTD & Cap yang mengikuti kontrol kanan --}}
      <img id="live-ttd" class="overlay-sign" src="{{ $ttdPreviewB64 ?? '' }}" alt="" style="display:none;">
      <img id="live-cap" class="overlay-cap"  src="{{ $capPreviewB64 ?? '' }}" alt="" style="display:none;">
    </div>
  </div>

  {{-- ===== Kolom Kanan: Informasi & Aksi + kontrol approve ===== --}}
  <div class="col-lg-4">
    <div class="card info-card">
      <div class="card-header">
        <h3 class="card-title font-weight-bold">
          <i class="fas fa-info-circle mr-2"></i>Informasi & Aksi
        </h3>
      </div>
      <div class="card-body">
        <div class="d-grid gap-2">
          @can('edit-surat', $tugas)
            <a href="{{ route('surat_tugas.edit', ['tugas' => $tugas->id, 'mode' => 'koreksi']) }}"
               class="btn btn-warning btn-block">
              <i class="fas fa-pen mr-2"></i> Koreksi (Approver)
            </a>
          @endcan

          @if($tugas->status_surat !== 'disetujui')
            <form method="POST" action="{{ route('surat_tugas.approve', $tugas->id) }}" class="d-grid mb-2">
              @csrf

              @if(in_array(Auth::user()->peran_id, [2,3]))
                <div id="approve-panel">
                  @include('surat_tugas.partials.approve-controls')

                  {{-- Pratinjau mini (kanvas) tetap ada --}}
                  @php
                      $capDefaultWmm = isset($kop) ? (int)($kop->cap_default_width_mm ?? 30) : 30;
                      $capOpacity    = isset($kop) ? (int)($kop->cap_opacity ?? 85) : 85;
                      $ttdWmmDefault = (int) (optional(optional(Auth::user())->signature)->default_width_mm ?? 35);
                      $ttdHmmDefault = (int) (optional(optional(Auth::user())->signature)->default_height_mm ?? 15);
                  @endphp

                  <div class="card border-0 shadow-sm mb-3">
                    <div class="card-body">
                      <h6 class="mb-3">Pratinjau TTD & Cap</h6>
                      <canvas id="signPreview" width="700" height="260" style="border:1px dashed #cfd8dc; width:100%; max-width:700px;"></canvas>
                      <small class="text-muted d-block mt-2">
                        Area pratinjau mewakili bagian kanan-bawah halaman surat. Posisi & ukuran mengikuti slider/offset di atas.
                      </small>
                    </div>
                  </div>
                </div>
              @endif

              {{-- Penanda agar approve lewat UI --}}
              <input type="hidden" name="approve_via" value="ui">

              <button type="submit" class="btn btn-success btn-block"
                      onclick="return confirm('Setujui surat ini?')">
                <i class="fas fa-check mr-2"></i> Approve
              </button>
            </form>
          @endif

          @can('edit-surat', $tugas)
            <a href="{{ route('surat_tugas.edit', ['tugas' => $tugas->id, 'mode' => 'koreksi']) }}"
               class="btn btn-warning btn-block mb-2">
              <i class="fas fa-pen mr-2"></i> Koreksi (Approver)
            </a>
          @endcan

          <a href="{{ route('surat_tugas.downloadPdf', $tugas->id) }}" class="btn btn-danger btn-block" target="_blank">
            <i class="fas fa-file-pdf mr-2"></i>Download PDF
          </a>
          <a href="{{ route('surat_tugas.index') }}" class="btn btn-secondary btn-block">
            <i class="fas fa-arrow-left mr-2"></i>Kembali ke Daftar
          </a>

          @if($tugas->status_surat === 'draft' && auth()->user()->peran_id === 1)
            <a href="{{ route('surat_tugas.edit', ['tugas' => $tugas->id]) }}" class="btn btn-warning btn-block">
              <i class="fas fa-edit mr-2"></i>Edit Surat
            </a>
          @endif
        </div>

        <hr>

        <dl class="info-list">
          <div class="row">
            <dt class="col-sm-5">Status</dt>
            <dd class="col-sm-7">
              <span class="badge badge-pill badge-{{ $tugas->status_surat == 'disetujui' ? 'success' : ($tugas->status_surat == 'pending' ? 'warning' : 'secondary') }}">
                {{ ucfirst($tugas->status_surat) }}
              </span>
            </dd>
          </div>
          <div class="row">
            <dt class="col-sm-5">Dibuat oleh</dt>
            <dd class="col-sm-7">{{ $tugas->pembuat?->nama_lengkap ?? '-' }}</dd>
          </div>
          <div class="row">
            <dt class="col-sm-5">Asal Surat</dt>
            <dd class="col-sm-7">{{ $tugas->asalSurat?->nama_lengkap ?? '-' }}</dd>
          </div>
          <div class="row">
            <dt class="col-sm-5">Tgl Dibuat</dt>
            <dd class="col-sm-7">{{ $tugas->created_at->translatedFormat('d M Y, H:i') }}</dd>
          </div>
          @if($tugas->submitted_at)
          <div class="row">
            <dt class="col-sm-5">Tgl Diajukan</dt>
            <dd class="col-sm-7">{{ $tugas->submitted_at->translatedFormat('d M Y, H:i') }}</dd>
          </div>
          @endif
          <div class="row">
            <dt class="col-sm-5">Diperbarui</dt>
            <dd class="col-sm-7">{{ $tugas->updated_at->diffForHumans() }}</dd>
          </div>
        </dl>
      </div>
    </div>
  </div>
</div>

{{-- Auto-scroll ke panel approve jika datang dari daftar (?approve=1) --}}
@if(request('approve') == 1)
  <script>
    window.addEventListener('DOMContentLoaded', () => {
      const panel = document.getElementById('approve-panel');
      if (panel) panel.scrollIntoView({ behavior: 'smooth' });
    });
  </script>
@endif

{{-- Sinkronkan slider/checkbox → overlay di lembar kiri + canvas mini --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
  // ===== elemen overlay di lembar kiri =====
  const ttdEl = document.getElementById('live-ttd');
  const capEl = document.getElementById('live-cap');

  // ===== sumber gambar =====
  const ttdSrc = @json($ttdPreviewB64 ?? null);
  const capSrc = @json($capPreviewB64 ?? null);
  if (ttdSrc) ttdEl.src = ttdSrc;
  if (capSrc) capEl.src = capSrc;

  // ===== kontrol form (kanan) =====
  const elShowTtd  = document.querySelector('input[name="show_ttd"]');
  const elTtdScale = document.querySelector('input[name="ttd_scale"]');
  const elTtdX     = document.querySelector('input[name="ttd_x"]');
  const elTtdY     = document.querySelector('input[name="ttd_y"]');

  const elShowCap  = document.querySelector('input[name="show_cap"]');
  const elCapScale = document.querySelector('input[name="cap_scale"]');
  const elCapX     = document.querySelector('input[name="cap_x"]');
  const elCapY     = document.querySelector('input[name="cap_y"]');

  // ===== konstanta (mm default & opacity kop) =====
  const mmToPx = mm => Math.round(mm * 3.78); // ~96dpi

  // ukuran default
  const ttdDefaultWmm = {{ (int)($ttdWmmDefault ?? 35) }};
  const ttdDefaultHmm = {{ (int)($ttdHmmDefault ?? 15) }};
  const capDefaultWmm = {{ (int)($capDefaultWmm ?? 30) }};
  const capOpacity    = {{ (int)($capOpacity ?? 85) }};

  // ---- Anchor default (disetel agar tepat di area yang kamu minta)
  // right/bottom dalam millimeter dihitung dari tepi kertas.
  const baseTtdRightMm = 58;  // TTD sedikit ke kiri dari cap
  const baseTtdBottomMm = 56; // antara "Semarang..." dan nama pejabat
  const baseCapRightMm = 30;  // cap dekat margin kanan
  const baseCapBottomMm = 60; // sedikit di atas TTD

  function drawLiveOverlay() {
    // --- TTD ---
    if (ttdSrc && elShowTtd && elShowTtd.checked) {
      const scale = (parseInt(elTtdScale?.value || 100) || 100) / 100;
      const wmm = Math.round(ttdDefaultWmm * scale);
      const hmm = Math.round(ttdDefaultHmm * scale);
      const offx = parseInt(elTtdX?.value || 0);
      const offy = parseInt(elTtdY?.value || 0);

      ttdEl.style.display = 'block';
      ttdEl.style.width   = mmToPx(wmm) + 'px';
      ttdEl.style.right   = (mmToPx(baseTtdRightMm + offx)) + 'px';
      ttdEl.style.bottom  = (mmToPx(baseTtdBottomMm + offy)) + 'px';
      ttdEl.style.opacity = 0.95;
    } else {
      ttdEl.style.display = 'none';
    }

    // --- CAP ---
    if (capSrc && elShowCap && elShowCap.checked) {
      const scale = (parseInt(elCapScale?.value || 100) || 100) / 100;
      const wmm = Math.round(capDefaultWmm * scale);
      const offx = parseInt(elCapX?.value || 0);
      const offy = parseInt(elCapY?.value || 0);

      capEl.style.display = 'block';
      capEl.style.width   = mmToPx(wmm) + 'px';
      capEl.style.right   = (mmToPx(baseCapRightMm + offx)) + 'px';
      capEl.style.bottom  = (mmToPx(baseCapBottomMm + offy)) + 'px';
      capEl.style.opacity = Math.max(0, Math.min(1, capOpacity/100));
    } else {
      capEl.style.display = 'none';
    }
  }

  ['input','change'].forEach(evt => {
    [elShowTtd, elTtdScale, elTtdX, elTtdY, elShowCap, elCapScale, elCapX, elCapY].forEach(el => {
      if (el) el.addEventListener(evt, function(){ drawLiveOverlay(); drawMini(); });
    });
  });
  drawLiveOverlay();

  // ====== KANVAS MINI (tetap seperti sebelumnya) ======
  const canvas = document.getElementById('signPreview');
  if (!canvas) return;
  const ctx = canvas.getContext('2d');

  const ttdImg = new Image();
  const capImg = new Image();
  if (ttdSrc) ttdImg.src = ttdSrc;
  if (capSrc) capImg.src = capSrc;

  function drawMini() {
    ctx.clearRect(0,0,canvas.width,canvas.height);
    ctx.fillStyle = '#fff';
    ctx.fillRect(0,0,canvas.width,canvas.height);

    // grid
    ctx.strokeStyle = '#eceff1';
    for (let x=0; x<canvas.width; x+=50) { ctx.beginPath(); ctx.moveTo(x,0); ctx.lineTo(x,canvas.height); ctx.stroke(); }
    for (let y=0; y<canvas.height; y+=50) { ctx.beginPath(); ctx.moveTo(0,y); ctx.lineTo(canvas.width,y); ctx.stroke(); }

    const padRightPx = 60, padBottomPx = 40;

    if (ttdSrc && elShowTtd && elShowTtd.checked && ttdImg.complete) {
      const scale = (parseInt(elTtdScale?.value || 100) || 100) / 100;
      const wmm = Math.round(ttdDefaultWmm * scale);
      const hmm = Math.round(ttdDefaultHmm * scale);
      const wpx = mmToPx(wmm);
      const hpx = mmToPx(hmm);
      const offx = mmToPx(parseInt(elTtdX?.value || 0));
      const offy = mmToPx(parseInt(elTtdY?.value || 0));
      const x = canvas.width - padRightPx - wpx + offx;
      const y = canvas.height - padBottomPx - hpx + offy;
      ctx.globalAlpha = 0.95;
      ctx.drawImage(ttdImg, x, y, wpx, hpx);
      ctx.globalAlpha = 1;
    }

    if (capSrc && elShowCap && elShowCap.checked && capImg.complete) {
      const scale = (parseInt(elCapScale?.value || 100) || 100) / 100;
      const wmm = Math.round(capDefaultWmm * scale);
      const wpx = mmToPx(wmm);
      const hpx = wpx;
      const offx = mmToPx(parseInt(elCapX?.value || 0));
      const offy = mmToPx(parseInt(elCapY?.value || 0));
      const x = canvas.width - padRightPx - wpx + offx - mmToPx(6);
      const y = canvas.height - padBottomPx - hpx + offy - mmToPx(4);
      ctx.globalAlpha = Math.max(0, Math.min(1, capOpacity/100));
      ctx.drawImage(capImg, x, y, wpx, hpx);
      ctx.globalAlpha = 1;
    }
  }

  const waitImgs = () => {
    if ((ttdSrc && !ttdImg.complete) || (capSrc && !capImg.complete)) setTimeout(waitImgs, 60);
    else { drawMini(); }
  };
  waitImgs();
});
</script>
@endsection