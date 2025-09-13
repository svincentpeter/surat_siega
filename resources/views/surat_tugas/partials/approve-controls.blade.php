@php
  $ttd_scale = old('ttd_scale', 100);
  $cap_scale = old('cap_scale', 100);
@endphp

<div class="card border-0 shadow-sm mb-3">
  <div class="card-body">
    <h6 class="mb-3">Opsi Tanda Tangan & Cap</h6>

    <div class="form-check form-switch mb-2">
      <input class="form-check-input" type="checkbox" name="show_ttd" id="show_ttd" value="1" checked>
      <label class="form-check-label" for="show_ttd">Tampilkan TTD Saya</label>
    </div>

    <div class="mb-3">
      <label class="form-label">Ukuran TTD (% dari default)</label>
      <input type="range" min="60" max="160" step="5" name="ttd_scale" value="{{ $ttd_scale }}" oninput="document.getElementById('ttdScaleVal').innerText=this.value+'%';">
      <small id="ttdScaleVal" class="text-muted">{{ $ttd_scale }}%</small>
    </div>

    <div class="form-check form-switch mb-2">
      <input class="form-check-input" type="checkbox" name="show_cap" id="show_cap" value="1" checked>
      <label class="form-check-label" for="show_cap">Tampilkan Cap/Stamp</label>
    </div>

    <div class="mb-3">
      <label class="form-label">Ukuran Cap (% dari default Kop)</label>
      <input type="range" min="60" max="160" step="5" name="cap_scale" value="{{ $cap_scale }}" oninput="document.getElementById('capScaleVal').innerText=this.value+'%';">
      <small id="capScaleVal" class="text-muted">{{ $cap_scale }}%</small>
    </div>

    <div class="row">
      <div class="col-md-6 mb-2">
        <label class="form-label">Offset TTD X (mm)</label>
        <input type="number" name="ttd_x" class="form-control" value="0" min="-150" max="150">
      </div>
      <div class="col-md-6 mb-2">
        <label class="form-label">Offset TTD Y (mm)</label>
        <input type="number" name="ttd_y" class="form-control" value="0" min="-150" max="150">
      </div>
      <div class="col-md-6 mb-2">
        <label class="form-label">Offset Cap X (mm)</label>
        <input type="number" name="cap_x" class="form-control" value="0" min="-150" max="150">
      </div>
      <div class="col-md-6 mb-2">
        <label class="form-label">Offset Cap Y (mm)</label>
        <input type="number" name="cap_y" class="form-control" value="0" min="-150" max="150">
      </div>
    </div>

    <small class="text-muted d-block">
      Tip: nilai positif = bergeser ke kanan/bawah, nilai negatif = kiri/atas.
    </small>
  </div>
</div>