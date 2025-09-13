@extends('layouts.app')
@section('title','TTD Saya')

@section('content')
<div class="card">
  <div class="card-header">Upload/Perbarui TTD</div>
  <div class="card-body">
    @if(session('ok')) <div class="alert alert-success">{{ session('ok') }}</div> @endif
    <form method="post" action="{{ route('kop.ttd.update') }}" enctype="multipart/form-data">
      @csrf
      <div class="mb-3">
        <label class="form-label">File TTD (PNG transparan)</label>
        <input type="file" name="file" class="form-control" accept="image/png" required>
        <small class="text-muted">Gunakan PNG transparan agar hasil rapi.</small>
      </div>
      <div class="row">
        <div class="col-md-6 mb-3">
          <label class="form-label">Lebar default (mm)</label>
          <input type="number" name="default_width_mm" class="form-control" min="20" max="80" value="{{ old('default_width_mm', $sig->default_width_mm ?? 35) }}">
        </div>
        <div class="col-md-6 mb-3">
          <label class="form-label">Tinggi default (mm)</label>
          <input type="number" name="default_height_mm" class="form-control" min="10" max="30" value="{{ old('default_height_mm', $sig->default_height_mm ?? 15) }}">
        </div>
      </div>
      <button class="btn btn-primary">Simpan</button>
    </form>
    @if($sig)
      <hr>
      <p class="text-muted">Pratinjau:</p>
      <img src="data:image/png;base64,{{ base64_encode(Storage::disk('local')->get($sig->ttd_path)) }}" style="max-width:300px">
    @endif
  </div>
</div>
@endsection
