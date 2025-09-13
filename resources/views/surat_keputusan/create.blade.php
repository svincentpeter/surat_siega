{{-- resources/views/surat_keputusan/create.blade.php --}}

@extends('layouts.app')

@section('title', 'Buat Surat Keputusan')

@section('content_header')
    <h1>Buat Surat Keputusan</h1>
@endsection

@section('content')
<div class="container-fluid">

    {{-- Tampilkan error validasi --}}
    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>Periksa kembali input Anda:</strong>
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form id="skForm" action="{{ route('surat_keputusan.store') }}" method="POST">
        @csrf

        {{-- ========================= --}}
        {{-- 1) Data Utama         --}}
        {{-- ========================= --}}
        <div class="card mb-4">
            <div class="card-header bg-purple text-white">
                <i class="fas fa-info-circle mr-2"></i>Data Utama
            </div>
            <div class="card-body">
                <div class="row g-3">
                    {{-- Nomor Otomatis --}}
                    <div class="col-md-6">
                        <label class="form-label">Nomor Surat Keputusan</label>
                        <input type="text" name="nomor" class="form-control"
                               value="{{ old('nomor', $autoNomor ?? '') }}" readonly>
                    </div>

                    {{-- Judul / Perihal (di‐save ke field “tentang”) --}}
                    <div class="col-md-6">
                        <label class="form-label">Judul / Perihal</label>
                        <input type="text" name="tentang"
                               class="form-control @error('tentang') is-invalid @enderror"
                               value="{{ old('tentang') }}"
                               placeholder="Contoh: Penetapan Visi, Misi, Tujuan ...">
                        @error('tentang')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Nama Pembuat (Admin TU) --}}
                    <div class="col-md-6">
                        <label class="form-label">Nama Pembuat</label>
                        <select name="nama_pembuat" class="form-control @error('nama_pembuat') is-invalid @enderror">
                            <option value="">-- Pilih Admin TU --</option>
                            @foreach($admins as $id => $nama)
                                <option value="{{ $id }}" {{ old('nama_pembuat') == $id ? 'selected' : '' }}>
                                    {{ $nama }}
                                </option>
                            @endforeach
                        </select>
                        @error('nama_pembuat')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Tanggal SK (disimpan di field “tanggal_asli”) --}}
                    <div class="col-md-6">
                        <label class="form-label">Tanggal SK</label>
                        <input type="date" name="tanggal_asli"
                               class="form-control @error('tanggal_asli') is-invalid @enderror"
                               value="{{ old('tanggal_asli', date('Y-m-d')) }}">
                        @error('tanggal_asli')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- ========================= --}}
        {{-- 2) Menimbang           --}}
        {{-- ========================= --}}
        <div class="card mb-4">
            <div class="card-header bg-purple text-white">
                <i class="fas fa-balance-scale mr-2"></i>Menimbang
            </div>
            <div class="card-body">
                <div id="menimbang-list">
                    @php $menimbang = old('menimbang', ['']); @endphp
                    @foreach($menimbang as $i => $mt)
                        <div class="input-group mb-2 menimbang-item">
                            <span class="input-group-text">{{ chr(97 + $i) }}.</span>
                            <input type="text" name="menimbang[]" class="form-control"
                                   value="{{ $mt }}" placeholder="Isi alasan pertimbangan ...">
                            <button type="button" class="btn btn-danger btn-remove-menimbang" @if($i == 0) style="display:none;" @endif>
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    @endforeach
                </div>
                <button type="button" class="btn btn-outline-primary" id="btn-add-menimbang">
                    <i class="fas fa-plus"></i> Tambah Menimbang
                </button>
            </div>
        </div>

        {{-- ========================= --}}
        {{-- 3) Mengingat           --}}
        {{-- ========================= --}}
        <div class="card mb-4">
            <div class="card-header bg-purple text-white">
                <i class="fas fa-book mr-2"></i>Mengingat
            </div>
            <div class="card-body">
                <div id="mengingat-list">
                    @php $mengingat = old('mengingat', ['']); @endphp
                    @foreach($mengingat as $i => $mg)
                        <div class="input-group mb-2 mengingat-item">
                            <span class="input-group-text">{{ $i + 1 }}.</span>
                            <input type="text" name="mengingat[]" class="form-control"
                                   value="{{ $mg }}" placeholder="Dasar hukum, peraturan, dsb ...">
                            <button type="button" class="btn btn-danger btn-remove-mengingat" @if($i == 0) style="display:none;" @endif>
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    @endforeach
                </div>
                <button type="button" class="btn btn-outline-primary" id="btn-add-mengingat">
                    <i class="fas fa-plus"></i> Tambah Mengingat
                </button>
            </div>
        </div>

        {{-- ========================= --}}
        {{-- 4) Menetapkan          --}}
        {{-- ========================= --}}
        <div class="card mb-4">
            <div class="card-header bg-purple text-white">
                <i class="fas fa-gavel mr-2"></i>Menetapkan
            </div>
            <div class="card-body">
                <div id="menetapkan-list">
                    @php
                        $menetapkan = old('menetapkan', [['judul'=>'KESATU','isi'=>'']]);
                        $labels = ['KESATU', 'KEDUA', 'KETIGA', 'KEEMPAT', 'KELIMA', 'KEENAM', 'KETUJUH', 'KEDELAPAN', 'KESEMBILAN', 'KESEPULUH'];
                    @endphp
                    @foreach($menetapkan as $i => $mt)
                        <div class="menetapkan-item mb-3 border p-3 rounded bg-light">
                            <div class="row g-2 align-items-center">
                                <div class="col-md-2">
                                    <input type="text" name="menetapkan[{{ $i }}][judul]" class="form-control"
                                           value="{{ $mt['judul'] ?? ($labels[$i] ?? '') }}" readonly>
                                </div>
                                <div class="col-md-9">
                                    <textarea name="menetapkan[{{ $i }}][isi]" class="form-control"
                                              rows="2" placeholder="Isi keputusan ...">{{ $mt['isi'] ?? '' }}</textarea>
                                </div>
                                <div class="col-md-1 text-end">
                                    <button type="button" class="btn btn-danger btn-remove-menetapkan" @if($i == 0) style="display:none;" @endif>
                                        <i class="fas fa-minus"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <button type="button" class="btn btn-outline-primary" id="btn-add-menetapkan">
                    <i class="fas fa-plus"></i> Tambah Menetapkan
                </button>
            </div>
        </div>

        {{-- ========================= --}}
        {{-- 5) Penandatangan & Tembusan --}}
        {{-- ========================= --}}
        <div class="card mb-4">
            <div class="card-header bg-purple text-white">
                <i class="fas fa-user-check mr-2"></i>Penandatangan & Tembusan
            </div>
            <div class="card-body">
                <div class="row g-3">
                    {{-- Pilih Pejabat (Dekan / Wakil Dekan) --}}
                    <div class="col-md-6">
                        <label class="form-label">Penandatangan</label>
                        <select name="penandatangan" class="form-control @error('penandatangan') is-invalid @enderror">
                            <option value="">-- Pilih Pejabat --</option>
                            @foreach($pejabat as $p)
                                <option value="{{ $p->id }}" {{ old('penandatangan') == $p->id ? 'selected' : '' }}>
                                    {{ $p->nama_lengkap }} ({{ $p->peran->deskripsi ?? '' }})
                                </option>
                            @endforeach
                        </select>
                        @error('penandatangan')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Tembusan (opsional) --}}
                    <div class="col-md-6">
                        <label class="form-label">Tembusan (opsional, pisahkan dengan koma)</label>
                        <input type="text" name="tembusan" class="form-control"
                               value="{{ old('tembusan') }}"
                               placeholder="Yth. Rektor, Yth. Ketua Prodi ...">
                    </div>
                </div>
            </div>
        </div>

        {{-- ========================= --}}
        {{-- 6) Penerima (Hidden)    --}}
        {{-- ========================= --}}
        {{-- Secara otomatis semua user (role != Admin TU) jadi penerima --}}
        @foreach($users as $u)
            <input type="hidden" name="penerima[]" value="{{ $u->id }}">
        @endforeach

        {{-- ========================= --}}
        {{-- 7) Tombol Submit        --}}
        {{-- ========================= --}}
        <div class="mb-4 text-end">
            <button type="submit" name="mode" value="draft" class="btn btn-outline-secondary me-2">
                <i class="fas fa-save me-1"></i>Simpan Draft
            </button>
            <button type="submit" name="mode" value="terkirim" class="btn btn-success me-2">
                <i class="fas fa-paper-plane me-1"></i>Submit ke Penandatangan
            </button>
            <a href="{{ route('surat_keputusan.index') }}" class="btn btn-danger">
                <i class="fas fa-times me-1"></i>Batal
            </a>
        </div>

    </form>
</div>
@endsection

@push('scripts')
<script>
$(function(){
  // 1) Utility: refresh labels & tombol remove
  function refresh(section){
    const $list = $('#' + section + '-list');
    $list.find('.' + section + '-item').each(function(i){
      // tampilkan hanya tombol remove pada item ke-1 ke atas
      const $btn = $(this).find('.btn-remove-' + section);
      $btn.toggle(i > 0);

      // update label menimbang (a., b., c.) atau mengingat (1., 2., …)
      if(section === 'menimbang'){
        $(this).find('.input-group-text')
          .text(String.fromCharCode(97 + i) + '.');
      }
      if(section === 'mengingat'){
        $(this).find('.input-group-text')
          .text((i + 1) + '.');
      }
    });

    // khusus menetapkan: re-index name attributes supaya [0], [1], … selalu berurutan
    if(section === 'menetapkan'){
      $list.find('.menetapkan-item').each(function(i){
        $(this).find('input, textarea').each(function(){
          let name = $(this).attr('name');
          // ubah netto bagian [n] menjadi index baru
          name = name.replace(/menetapkan\[\d+\]/, `menetapkan[${i}]`);
          $(this).attr('name', name);
        });
      });
    }
  }

  // 2) Factory HTML untuk setiap section
  function makeItem(section, index){
    if(section === 'menimbang' || section === 'mengingat'){
      const label = section === 'menimbang'
        ? String.fromCharCode(97 + index) + '.'
        : (index + 1) + '.';
      const placeholder = section === 'menimbang'
        ? 'Isi alasan pertimbangan …'
        : 'Dasar hukum, peraturan, dsb …';
      return `
      <div class="input-group mb-2 ${section}-item">
        <span class="input-group-text">${label}</span>
        <input type="text" name="${section}[]" class="form-control"
               placeholder="${placeholder}">
        <button type="button" class="btn btn-danger btn-remove-${section}">
          <i class="fas fa-minus"></i>
        </button>
      </div>`;
    }
    if(section === 'menetapkan'){
      const labels = ['KESATU','KEDUA','KETIGA','KEEMPAT','KELIMA','KEENAM'];
      const judul = labels[index] || 'LAINNYA';
      return `
      <div class="menetapkan-item mb-3 border p-3 rounded bg-light">
        <div class="row g-2 align-items-center">
          <div class="col-md-2">
            <input type="text" name="menetapkan[${index}][judul]" 
                   class="form-control" value="${judul}" readonly>
          </div>
          <div class="col-md-9">
            <textarea name="menetapkan[${index}][isi]" 
                      class="form-control" rows="2"
                      placeholder="Isi keputusan …"></textarea>
          </div>
          <div class="col-md-1 text-end">
            <button type="button" 
                    class="btn btn-danger btn-remove-menetapkan">
              <i class="fas fa-minus"></i>
            </button>
          </div>
        </div>
      </div>`;
    }
  }

  // 3) Event “Tambah”
  $('#btn-add-menimbang').click(function(){
    const idx = $('#menimbang-list .menimbang-item').length;
    $('#menimbang-list').append(makeItem('menimbang', idx));
    refresh('menimbang');
  });
  $('#btn-add-mengingat').click(function(){
    const idx = $('#mengingat-list .mengingat-item').length;
    $('#mengingat-list').append(makeItem('mengingat', idx));
    refresh('mengingat');
  });
  $('#btn-add-menetapkan').click(function(){
    const idx = $('#menetapkan-list .menetapkan-item').length;
    $('#menetapkan-list').append(makeItem('menetapkan', idx));
    refresh('menetapkan');
  });

  // 4) Event “Hapus” (delegated)
  $('#menimbang-list').on('click', '.btn-remove-menimbang', function(){
    $(this).closest('.menimbang-item').remove();
    refresh('menimbang');
  });
  $('#mengingat-list').on('click', '.btn-remove-mengingat', function(){
    $(this).closest('.mengingat-item').remove();
    refresh('mengingat');
  });
  $('#menetapkan-list').on('click', '.btn-remove-menetapkan', function(){
    $(this).closest('.menetapkan-item').remove();
    refresh('menetapkan');
  });

  // 5) Inisialisasi saat load page
  refresh('menimbang');
  refresh('mengingat');
  refresh('menetapkan');

  // 6) SweetAlert flash (jika ada session)
  @if (session('success'))
    Swal.fire({ title: 'Berhasil!', text: @json(session('success')), icon: 'success' });
  @endif
  @if (session('error'))
    Swal.fire({ title: 'Gagal!', text: @json(session('error')), icon: 'error' });
  @endif
});
</script>
@endpush


@push('css')
<style>
    .bg-purple { background: #6f42c1 !important; color: #fff !important; }
</style>
@endpush
