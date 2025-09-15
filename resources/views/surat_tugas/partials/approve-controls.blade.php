{{-- Panel Kontrol Approve (tanpa offset; fokus ukuran & opacity) --}}
<form method="post" action="{{ route('surat_tugas.approve', $tugas) }}" class="needs-validation" novalidate>
  @csrf

  {{-- Matikan offset lama (kompatibilitas jika controller belum diubah) --}}
  <input type="hidden" name="ttd_x" value="0">
  <input type="hidden" name="ttd_y" value="0">
  <input type="hidden" name="cap_x" value="0">
  <input type="hidden" name="cap_y" value="0">

  <div class="row g-3">
    <div class="col-md-4">
      <label class="form-label fw-semibold">Ukuran TTD (mm)</label>
      <input type="number" name="ttd_w_mm" class="form-control"
             min="30" max="60" step="1"
             value="{{ old('ttd_w_mm', $ttdW ?? 42) }}">
      <div class="form-text">Rentang aman: 30–60 mm (default: 42).</div>
    </div>

    <div class="col-md-4">
      <label class="form-label fw-semibold">Ukuran Cap (mm)</label>
      <input type="number" name="cap_w_mm" class="form-control"
             min="25" max="45" step="1"
             value="{{ old('cap_w_mm', $capW ?? 35) }}">
      <div class="form-text">Rentang aman: 25–45 mm (default: 35).</div>
    </div>

    <div class="col-md-4">
      <label class="form-label fw-semibold">Opacity Cap</label>
      <input type="number" name="cap_opacity" class="form-control"
             min="0.70" max="1.00" step="0.01"
             value="{{ old('cap_opacity', $capOpacity ?? 0.95) }}">
      <div class="form-text">0.7 (transparan) – 1.0 (solid). Default: 0.95.</div>
    </div>
  </div>

  <div class="d-flex gap-2 mt-3">
    <button type="submit" class="btn btn-primary">
      <i class="bi bi-check2-circle me-1"></i> Simpan & Approve
    </button>
    <a href="{{ url()->previous() }}" class="btn btn-secondary">Batal</a>
  </div>
</form>