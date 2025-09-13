{{-- START: approve-preview (Surat Tugas) --}}
<div class="card border-0 shadow-sm mb-3">
  <div class="card-body">
    <h6 class="mb-3">Pratinjau TTD & Cap</h6>
    <canvas id="signPreview" width="700" height="260" style="border:1px dashed #cfd8dc; width:100%; max-width:700px;"></canvas>
    <small class="text-muted d-block mt-2">
      Area pratinjau mewakili bagian kanan-bawah halaman surat. Posisi & ukuran mengikuti slider/offset di atas.
    </small>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
  const canvas = document.getElementById('signPreview');
  if (!canvas) return;
  const ctx = canvas.getContext('2d');

  const ttdImg = new Image();
  const capImg = new Image();
  const ttdSrc = @json($ttdPreviewB64 ?? null);
  const capSrc = @json($capPreviewB64 ?? null);
  if (ttdSrc) ttdImg.src = ttdSrc;
  if (capSrc) capImg.src = capSrc;

  // Ambil input kontrol dari form approve
  const elShowTtd = document.querySelector('input[name="show_ttd"]');
  const elTtdScale = document.querySelector('input[name="ttd_scale"]');
  const elTtdX = document.querySelector('input[name="ttd_x"]');
  const elTtdY = document.querySelector('input[name="ttd_y"]');

  const elShowCap = document.querySelector('input[name="show_cap"]');
  const elCapScale = document.querySelector('input[name="cap_scale"]');
  const elCapX = document.querySelector('input[name="cap_x"]');
  const elCapY = document.querySelector('input[name="cap_y"]');

  // default ukuran mm → mapping ke px kasar untuk pratinjau
  const mmToPx = mm => Math.round(mm * 3.78); // 96dpi ~ 3.78 px/mm

  // default dari server (fallback jika tidak ada)
  const ttdDefaultWmm = {{ (int)optional(optional(Auth::user())->signature)->default_width_mm ?? 35 }};
  const ttdDefaultHmm = {{ (int)optional(optional(Auth::user())->signature)->default_height_mm ?? 15 }};
  const capDefaultWmm = {{ (int)($kop->cap_default_width_mm ?? 30) }};
  const capOpacity = {{ (int)($kop->cap_opacity ?? 85) }};

  function draw() {
    // background polos (area putih)
    ctx.clearRect(0,0,canvas.width,canvas.height);
    ctx.fillStyle = '#fff';
    ctx.fillRect(0,0,canvas.width,canvas.height);

    // grid tipis biar mudah lihat posisi
    ctx.strokeStyle = '#eceff1';
    for (let x=0; x<canvas.width; x+=50) { ctx.beginPath(); ctx.moveTo(x,0); ctx.lineTo(x,canvas.height); ctx.stroke(); }
    for (let y=0; y<canvas.height; y+=50) { ctx.beginPath(); ctx.moveTo(0,y); ctx.lineTo(canvas.width,y); ctx.stroke(); }

    const padRightPx = 60; // jarak dari kanan (anchor)
    const padBottomPx = 40; // jarak dari bawah (anchor)

    // Gambar TTD
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

    // Gambar Cap
    if (capSrc && elShowCap && elShowCap.checked && capImg.complete) {
      const scale = (parseInt(elCapScale?.value || 100) || 100) / 100;
      const wmm = Math.round(capDefaultWmm * scale);
      const wpx = mmToPx(wmm);
      const hpx = wpx; // asumsi cap mendekati bujur sangkar
      const offx = mmToPx(parseInt(elCapX?.value || 0));
      const offy = mmToPx(parseInt(elCapY?.value || 0));

      const x = canvas.width - padRightPx - wpx + offx - mmToPx(12); // sedikit bergeser kiri
      const y = canvas.height - padBottomPx - hpx + offy - mmToPx(5); // sedikit naik

      ctx.globalAlpha = Math.max(0, Math.min(1, capOpacity/100));
      ctx.drawImage(capImg, x, y, wpx, hpx);
      ctx.globalAlpha = 1;
    }
  }

  ['input','change'].forEach(evt => {
    [elShowTtd, elTtdScale, elTtdX, elTtdY, elShowCap, elCapScale, elCapX, elCapY].forEach(el => {
      if (el) el.addEventListener(evt, draw);
    });
  });

  const waitImgs = () => {
    if ((ttdSrc && !ttdImg.complete) || (capSrc && !capImg.complete)) {
      setTimeout(waitImgs, 50);
    } else {
      draw();
    }
  };
  waitImgs();

  // Titik dasar (BASE) harus sama dengan yang kita simpan di controller
  const BASE_TTD_LEFT_MM = 108, BASE_TTD_TOP_MM = 205;
  const BASE_CAP_LEFT_MM = 125, BASE_CAP_TOP_MM = 185;

  function applyOverlay() {
    const ttdX = parseInt(document.getElementById('ttd_x')?.value || 0, 10);
    const ttdY = parseInt(document.getElementById('ttd_y')?.value || 0, 10);
    const capX = parseInt(document.getElementById('cap_x')?.value || 0, 10);
    const capY = parseInt(document.getElementById('cap_y')?.value || 0, 10);

    const ttd = document.getElementById('live-ttd');
    const cap = document.getElementById('live-cap');

    if (ttd) {
      ttd.style.left = (BASE_TTD_LEFT_MM + ttdX) + 'mm';
      ttd.style.top  = (BASE_TTD_TOP_MM  + ttdY) + 'mm';
    }
    if (cap) {
      cap.style.left = (BASE_CAP_LEFT_MM + capX) + 'mm';
      cap.style.top  = (BASE_CAP_TOP_MM  + capY) + 'mm';
    }
  }

  ['ttd_x','ttd_y','cap_x','cap_y'].forEach(id => {
    const el = document.getElementById(id);
    if (el) el.addEventListener('input', applyOverlay);
  });
  window.addEventListener('DOMContentLoaded', applyOverlay);
});
</script>
{{-- END: approve-preview --}}
