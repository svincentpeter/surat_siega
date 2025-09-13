{{-- resources/views/surat_keputusan/edit.blade.php --}}

@extends('layouts.app')

@section('title', 'Edit Surat Keputusan')

@section('content_header')
    <h1>Edit Surat Keputusan</h1>
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

        <form id="skForm" action="{{ route('surat_keputusan.update', $sk->id) }}" method="POST">
            @csrf
            @method('PUT')

            {{-- Data Utama --}}
            <div class="card mb-4">
                <div class="card-header bg-purple text-white">
                    <i class="fas fa-info-circle mr-2"></i>Data Utama
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        {{-- Nomor (readonly) --}}
                        <div class="col-md-6">
                            <label class="form-label">Nomor Surat Keputusan</label>
                            <input type="text" name="nomor" class="form-control" value="{{ old('nomor', $sk->nomor) }}"
                                readonly>
                        </div>

                        {{-- Tentang (Judul/Perihal) --}}
                        <div class="col-md-6">
                            <label class="form-label">Tentang (Judul/Perihal)</label>
                            <input type="text" name="tentang" class="form-control @error('tentang') is-invalid @enderror"
                                value="{{ old('tentang', $sk->tentang) }}"
                                placeholder="Contoh: Penetapan Visi, Misi, Tujuan ...">
                            @error('tentang')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Tanggal Asli --}}
                        <div class="col-md-6">
                            <label class="form-label">Tanggal SK</label>
                            <input type="date" name="tanggal_asli"
                                class="form-control @error('tanggal_asli') is-invalid @enderror"
                                value="{{ old('tanggal_asli', $sk->tanggal_asli ? \Illuminate\Support\Carbon::parse($sk->tanggal_asli)->format('Y-m-d') : date('Y-m-d')) }}">
                            @error('tanggal_asli')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="tanggal_surat" class="form-label">Tanggal Surat (opsional)</label>
                            <input type="date" name="tanggal_surat" id="tanggal_surat" class="form-control"
                                value="{{ old('tanggal_surat', optional($sk->tanggal_surat)->format('Y-m-d')) }}">
                            <small class="text-muted">Kosongkan jika ingin otomatis saat final approve.</small>
                        </div>

                        {{-- (Dropdown Nama Pembuat dihapus, karena controller otomatis menyimpan dibuat_oleh = Auth::id()) --}}
                    </div>
                </div>
            </div>

            {{-- Menimbang --}}
            <div class="card mb-4">
                <div class="card-header bg-purple text-white">
                    <i class="fas fa-balance-scale mr-2"></i>Menimbang
                </div>
                <div class="card-body">
                    <div id="menimbang-list">
                        @php
                            // Ambil old() atau data dari model (controller sudah decode JSON menjadi array)
                            $menimbang = old('menimbang', $sk->menimbang ?? ['']);
                        @endphp

                        @foreach ($menimbang as $i => $mt)
                            <div class="input-group mb-2 menimbang-item">
                                <span class="input-group-text">{{ chr(97 + $i) }}.</span>
                                <input type="text" name="menimbang[]" class="form-control" value="{{ $mt }}"
                                    placeholder="Isi alasan pertimbangan ...">
                                <button type="button" class="btn btn-danger btn-remove-menimbang"
                                    @if ($i === 0) style="display:none;" @endif>
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

            {{-- Mengingat --}}
            <div class="card mb-4">
                <div class="card-header bg-purple text-white">
                    <i class="fas fa-book mr-2"></i>Mengingat
                </div>
                <div class="card-body">
                    <div id="mengingat-list">
                        @php
                            $mengingat = old('mengingat', $sk->mengingat ?? ['']);
                        @endphp

                        @foreach ($mengingat as $i => $mg)
                            <div class="input-group mb-2 mengingat-item">
                                <span class="input-group-text">{{ $i + 1 }}.</span>
                                <input type="text" name="mengingat[]" class="form-control" value="{{ $mg }}"
                                    placeholder="Dasar hukum, peraturan, dsb ...">
                                <button type="button" class="btn btn-danger btn-remove-mengingat"
                                    @if ($i === 0) style="display:none;" @endif>
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

            {{-- Menetapkan --}}
            <div class="card mb-4">
                <div class="card-header bg-purple text-white">
                    <i class="fas fa-gavel mr-2"></i>Menetapkan
                </div>
                <div class="card-body">
                    <div id="menetapkan-list">
                        @php
                            // Ambil old() atau data dari model (controller sudah decode JSON)
                            $menetapkan = old('menetapkan', $sk->menetapkan ?? [['judul' => 'KESATU', 'isi' => '']]);
                            if (is_string($menetapkan)) {
                                $menetapkan = json_decode($menetapkan, true);
                            }
                            if (empty($menetapkan)) {
                                $menetapkan = [['judul' => 'KESATU', 'isi' => '']];
                            }
                            $labels = [
                                'KESATU',
                                'KEDUA',
                                'KETIGA',
                                'KEEMPAT',
                                'KELIMA',
                                'KEENAM',
                                'KETUJUH',
                                'KEDELAPAN',
                                'KESEMBILAN',
                                'KESEPULUH',
                            ];
                        @endphp

                        @foreach ($menetapkan as $i => $mt)
                            <div class="menetapkan-item mb-3 border p-3 rounded bg-light">
                                <div class="row g-2 align-items-center">
                                    <div class="col-md-2">
                                        <input type="text" name="menetapkan[{{ $i }}][judul]"
                                            class="form-control" value="{{ $mt['judul'] ?? ($labels[$i] ?? '') }}"
                                            readonly>
                                    </div>
                                    <div class="col-md-9">
                                        <textarea name="menetapkan[{{ $i }}][isi]" class="form-control" rows="2"
                                            placeholder="Isi keputusan ...">{{ $mt['isi'] ?? '' }}</textarea>
                                    </div>
                                    <div class="col-md-1 text-end">
                                        <button type="button" class="btn btn-danger btn-remove-menetapkan"
                                            @if ($i === 0) style="display:none;" @endif>
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

            {{-- Penandatangan & Tembusan --}}
            <div class="card mb-4">
                <div class="card-header bg-purple text-white">
                    <i class="fas fa-user-check mr-2"></i>Penandatangan & Tembusan
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        {{-- Penandatangan --}}
                        <div class="col-md-6">
                            <label class="form-label">Penandatangan</label>
                            <select name="penandatangan"
                                class="form-control @error('penandatangan') is-invalid @enderror">
                                <option value="">-- Pilih Pejabat --</option>
                                @foreach ($pejabat as $p)
                                    <option value="{{ $p->id }}"
                                        {{ old('penandatangan', $sk->penandatangan) == $p->id ? 'selected' : '' }}>
                                        {{ $p->nama_lengkap }} ({{ $p->peran->deskripsi ?? '' }})
                                    </option>
                                @endforeach
                            </select>
                            @error('penandatangan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Tembusan --}}
                        <div class="col-md-6">
                            <label class="form-label">Tembusan (opsional, pisahkan koma)</label>
                            <input type="text" name="tembusan" class="form-control"
                                value="{{ old('tembusan', $sk->tembusan) }}"
                                placeholder="Yth. Rektor, Yth. Ketua Prodi ...">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Penerima (Hidden: semua pengguna menjadi penerima secara default) --}}
            @foreach (\App\Models\User::where('peran_id', '!=', 1)->pluck('id') as $pid)
                <input type="hidden" name="penerima[]" value="{{ $pid }}">
            @endforeach

            {{-- Tombol Aksi --}}
            <div class="mb-4 text-end">
                <button type="submit" name="mode" value="draft" class="btn btn-outline-secondary me-2">
                    <i class="fas fa-save me-1"></i>Update Draft
                </button>
                <button type="submit" name="mode" value="terkirim" class="btn btn-success me-2">
                    <i class="fas fa-paper-plane me-1"></i>Update & Submit
                </button>
                <a href="{{ route('surat_keputusan.index') }}" class="btn btn-danger">
                    <i class="fas fa-times me-1"></i>Batal
                </a>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // MENIMBANG
            document.getElementById('btn-add-menimbang').onclick = function() {
                let list = document.getElementById('menimbang-list');
                let itemCount = list.querySelectorAll('.menimbang-item').length;
                let html = `
            <div class="input-group mb-2 menimbang-item">
                <span class="input-group-text">${String.fromCharCode(97 + itemCount)}.</span>
                <input type="text" name="menimbang[]" class="form-control" placeholder="Isi alasan pertimbangan ...">
                <button type="button" class="btn btn-danger btn-remove-menimbang">
                    <i class="fas fa-minus"></i>
                </button>
            </div>`;
                list.insertAdjacentHTML('beforeend', html);
                refreshRemoveButtons('menimbang');
            };
            document.getElementById('menimbang-list').addEventListener('click', function(e) {
                if (e.target.closest('.btn-remove-menimbang')) {
                    e.target.closest('.menimbang-item').remove();
                    refreshRemoveButtons('menimbang');
                }
            });

            // MENGINGAT
            document.getElementById('btn-add-mengingat').onclick = function() {
                let list = document.getElementById('mengingat-list');
                let itemCount = list.querySelectorAll('.mengingat-item').length;
                let html = `
            <div class="input-group mb-2 mengingat-item">
                <span class="input-group-text">${itemCount + 1}.</span>
                <input type="text" name="mengingat[]" class="form-control" placeholder="Dasar hukum, peraturan, dsb ...">
                <button type="button" class="btn btn-danger btn-remove-mengingat">
                    <i class="fas fa-minus"></i>
                </button>
            </div>`;
                list.insertAdjacentHTML('beforeend', html);
                refreshRemoveButtons('mengingat');
            };
            document.getElementById('mengingat-list').addEventListener('click', function(e) {
                if (e.target.closest('.btn-remove-mengingat')) {
                    e.target.closest('.mengingat-item').remove();
                    refreshRemoveButtons('mengingat');
                }
            });

            // MENETAPKAN
            let menetapkanLabels = ['KESATU', 'KEDUA', 'KETIGA', 'KEEMPAT', 'KELIMA', 'KEENAM', 'KETUJUH',
                'KEDELAPAN', 'KESEMBILAN', 'KESEPULUH'
            ];
            document.getElementById('btn-add-menetapkan').onclick = function() {
                let list = document.getElementById('menetapkan-list');
                let itemCount = list.querySelectorAll('.menetapkan-item').length;
                let judul = menetapkanLabels[itemCount] ?? 'LAINNYA';
                let html = `
            <div class="menetapkan-item mb-3 border p-3 rounded bg-light">
                <div class="row g-2 align-items-center">
                    <div class="col-md-2">
                        <input type="text"
                               name="menetapkan[${itemCount}][judul]"
                               class="form-control"
                               value="${judul}"
                               readonly>
                    </div>
                    <div class="col-md-9">
                        <textarea name="menetapkan[${itemCount}][isi]"
                                  class="form-control"
                                  rows="2"
                                  placeholder="Isi keputusan ..."></textarea>
                    </div>
                    <div class="col-md-1 text-end">
                        <button type="button" class="btn btn-danger btn-remove-menetapkan">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
            </div>`;
                list.insertAdjacentHTML('beforeend', html);
                refreshRemoveButtons('menetapkan');
            };
            document.getElementById('menetapkan-list').addEventListener('click', function(e) {
                if (e.target.closest('.btn-remove-menetapkan')) {
                    e.target.closest('.menetapkan-item').remove();
                    refreshRemoveButtons('menetapkan');
                }
            });

            // Fungsi untuk menyembunyikan tombol minus pada item pertama dan memperbarui label
            function refreshRemoveButtons(section) {
                let list = document.getElementById(section + '-list');
                let items = list.querySelectorAll('.' + section + '-item');
                items.forEach((el, i) => {
                    let btn = el.querySelector('.btn-remove-' + section);
                    if (i === 0) {
                        btn.style.display = 'none';
                    } else {
                        btn.style.display = '';
                    }
                    // Ubah label untuk menimbang / mengingat
                    if (section === 'menimbang') {
                        el.querySelector('.input-group-text').textContent = String.fromCharCode(97 + i) +
                            '.';
                    }
                    if (section === 'mengingat') {
                        el.querySelector('.input-group-text').textContent = (i + 1) + '.';
                    }
                });
            }

            // SweetAlert2 flash
            @if (session('success'))
                Swal.fire({
                    title: 'Berhasil!',
                    text: @json(session('success')),
                    icon: 'success',
                    confirmButtonText: 'OK'
                });
            @endif

            @if (session('error'))
                Swal.fire({
                    title: 'Gagal!',
                    text: @json(session('error')),
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            @endif
        });
    </script>
@endpush

@push('css')
    <style>
        .bg-purple {
            background: #6f42c1 !important;
            color: #fff !important;
        }
    </style>
@endpush
