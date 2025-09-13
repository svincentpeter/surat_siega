@push('css')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@1.5.2/dist/select2-bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap4.min.css">
    <style>
        .select2-container--bootstrap4 .select2-selection--single {
            height: calc(2.25rem + 2px) !important;
        }

        .select2-container--bootstrap4 .select2-selection--single .select2-selection__arrow {
            height: calc(2.25rem + 2px) !important;
        }

        #penerima-list .list-group-item {
            padding: 0.65rem 1rem;
            border-color: #e9ecef;
        }

        #penerima-list .eksternal-label {
            color: #198754;
            font-size: .92em;
            margin-left: 3px;
        }

        .list-group-item-action {
            transition: background-color 0.15s ease-in-out;
        }

        #penerima-table thead th {
            vertical-align: middle;
            text-align: center;
        }

        #penerima-table tbody td:first-child {
            text-align: center;
        }

        #task-preview {
            background-color: #f8f9fa;
            border: 1px dashed #ced4da;
            border-radius: .25rem;
            padding: 1.5rem;
            min-height: 158px;
            transition: all 0.3s ease-in-out;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        #task-preview.has-content {
            align-items: flex-start;
            justify-content: flex-start;
        }

        #task-preview .placeholder-text {
            color: #6c757d;
            font-style: italic;
        }

        #task-preview .preview-title {
            font-weight: 600;
            color: #007bff;
        }

        #task-preview .preview-content {
            font-size: 1.1rem;
        }

        .ck-editor__editable_inline {
            min-height: 250px;
        }

        /* --- HAPUS SEMUA CSS LAMA, GANTI DENGAN INI --- */

        /* Keyframes untuk Animasi */
        @keyframes pulseGlow {

            0%,
            100% {
                filter: drop-shadow(0 0 5px #00f6ff) drop-shadow(0 0 15px #00f6ff);
            }

            50% {
                filter: drop-shadow(0 0 10px #00f6ff) drop-shadow(0 0 25px #00f6ff);
            }
        }

        @keyframes auroraGradient {
            0% {
                background-position: 0% 50%;
            }

            50% {
                background-position: 100% 50%;
            }

            100% {
                background-position: 0% 50%;
            }
        }


        .page-header-ui-container {
            /* Latar belakang gelap dengan efek 'spotlight' untuk fokus */
            background: radial-gradient(ellipse at center, hsl(210, 20%, 20%) 0%, hsl(210, 25%, 10%) 100%);
            color: #ffffff;
            padding: 2rem 2.5rem;
            border-radius: 0.75rem;
            border: 1px solid rgba(255, 255, 255, 0.1);
            /* Tepi seperti kaca */
            display: flex;
            align-items: center;
            margin-bottom: 1.5rem;
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.4), inset 0 1px 1px rgba(255, 255, 255, 0.1);
            position: relative;
            overflow: hidden;
        }

        .page-header-icon {
            font-size: 3.5rem;
            margin-right: 2rem;
            color: #00f6ff;
            /* Warna Cyan Terang */

            /* Animasi denyut cahaya (pulse) */
            animation: pulseGlow 4s ease-in-out infinite;
        }

        .page-header-text {
            flex-grow: 1;
        }

        .page-header-title {
            margin: 0 0 0.5rem 0;
            font-size: 2.25rem;
            font-weight: 700;

            /* Shadow tajam untuk kejelasan maksimal */
            text-shadow: 0 2px 5px rgba(0, 0, 0, 0.5);
        }

        /* Garis Pemisah Animasi (Divider) */
        .page-header-divider {
            border: 0;
            height: 2px;
            margin-block: 0.75rem;
            width: 80px;

            /* Gradient Aurora yang bergerak */
            background: linear-gradient(90deg, #00f6ff, #ff00c1, #a900ff, #00f6ff);
            background-size: 300% 100%;
            animation: auroraGradient 6s linear infinite;
            border-radius: 2px;
            box-shadow: 0 0 5px #ff00c1, 0 0 10px #a900ff;
        }

        .page-header-subtitle {
            margin: 0;
            font-size: 1.05rem;
            font-weight: 400;
            color: rgba(255, 255, 255, 0.85);
            /* Warna lebih terang untuk kejelasan */
            line-height: 1.6;
        }

        /* Highlight untuk Nomor Surat */
        .nomor-surat-highlight {
            color: #00f6ff;
            /* Warna sama dengan ikon */
            font-weight: 600;
            background-color: rgba(0, 246, 255, 0.1);
            padding: 2px 6px;
            border-radius: 4px;
        }
    </style>
@endpush

@extends('layouts.app')
@section('title', 'Edit Surat Tugas')

@section('content_header')
    <div class="page-header-ui-container">
        <div class="page-header-icon">
            <i class="fas fa-edit"></i>
        </div>
        <div class="page-header-text">
            <h1 class="page-header-title">
                Edit Surat Tugas
            </h1>
            {{-- Garis pemisah yang akan dianimasikan --}}
            <hr class="page-header-divider">
            <p class="page-header-subtitle">
                Ubah detail, kelola penerima, dan ajukan surat tugas dengan nomor:
                {{-- Highlight khusus untuk nomor surat --}}
                <span class="nomor-surat-highlight">{{ $tugas->nomor }}</span>
            </p>
        </div>
    </div>
@endsection

@section('content')
    {{-- Error validasi --}}
    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <h5><i class="icon fas fa-ban"></i> Gagal Memperbarui!</h5>
            Mohon periksa kembali isian Anda:
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form id="tugasForm" action="{{ route('surat_tugas.update', ['tugas' => $tugas, 'mode' => request('mode')]) }}"
        method="POST">

        @csrf
        @method('PUT')
        <input type="hidden" name="tahun" value="{{ $tugas->tahun }}">
        <input type="hidden" name="semester" value="{{ $tugas->semester }}">
        {{-- AWAL PERBAIKAN: Tambahkan input hidden untuk bulan --}}
        <input type="hidden" name="bulan" value="{{ $tugas->bulan }}">
        {{-- AKHIR PERBAIKAN --}}

        <div class="row">
            {{-- Kiri: Form Utama --}}
            <div class="col-lg-8">
                <div class="card card-primary card-tabs">
                    <div class="card-header p-0 pt-1">
                        <ul class="nav nav-tabs" id="main-tabs" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" id="tab-dasar-tab" data-toggle="pill" href="#tab-dasar"
                                    role="tab"><i class="fas fa-file-alt mr-2"></i>Informasi Dasar</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="tab-isi-tab" data-toggle="pill" href="#tab-isi" role="tab"><i
                                        class="fas fa-tasks mr-2"></i>Detail Tugas</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="tab-pelaksanaan-tab" data-toggle="pill" href="#tab-pelaksanaan"
                                    role="tab"><i class="fas fa-calendar-alt mr-2"></i>Pelaksanaan</a>
                            </li>
                        </ul>
                    </div>
                    <div class="card-body">
                        <div class="tab-content" id="main-tabs-content">
                            {{-- TAB 1: INFORMASI DASAR --}}
                            <div class="tab-pane fade show active" id="tab-dasar" role="tabpanel">
                                <div class="row">
                                    <div class="col-md-6 form-group">
                                        <label for="nama_pembuat">Nama Pembuat</label>
                                        <select id="nama_pembuat" name="nama_pembuat" class="form-control select2bs4">
                                            @foreach ($admins as $id => $nama)
                                                <option value="{{ $id }}" @selected(old('nama_pembuat', $tugas->nama_pembuat) == $id)>
                                                    {{ $nama }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6 form-group">
                                        <label for="asal_surat">Asal Surat (Pejabat)</label>
                                        <select id="asal_surat" name="asal_surat" class="form-control select2bs4">
                                            @foreach ($pejabat as $p)
                                                <option value="{{ $p->id }}" @selected(old('asal_surat', $tugas->asal_surat) == $p->id)>
                                                    {{ $p->nama_lengkap }} ({{ $p->peran->nama }})</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="nama_umum">Perihal Surat</label>
                                    <input type="text" name="nama_umum" id="nama_umum" class="form-control"
                                        placeholder="Contoh: Penugasan sebagai Dosen Pembimbing"
                                        value="{{ old('nama_umum', $tugas->nama_umum) }}" required>
                                </div>

                                {{-- NOMOR SURAT SECTION (SELARAS DENGAN CREATE) --}}
                                <div class="row">
                                    <div class="col-md-6 form-group">
                                        <label for="klasifikasi_surat">Kode Klasifikasi</label>
                                        <select id="klasifikasi_surat" name="klasifikasi_surat_id"
                                            class="form-control select2bs4" required>
                                            <option value="">-- Pilih Kode --</option>
                                            @foreach ($klasifikasi as $k)
                                                <option value="{{ $k->id }}" data-kode="{{ $k->kode }}"
                                                    @selected(old('klasifikasi_surat_id', $tugas->klasifikasi_surat_id) == $k->id)>{{ $k->kode }} - {{ $k->deskripsi }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6 form-group">
                                        <label for="nomor_surat">Nomor Surat</label>
                                        <div class="input-group">
                                            <input type="text" id="nomor_urut" class="form-control text-center"
                                                style="max-width: 90px;"
                                                value="{{ old('nomor_urut', explode('/', $tugas->nomor)[0] ?? '001') }}">
                                            <input type="text" id="nomor_surat_display" class="form-control"
                                                value="{{ $tugas->nomor }}" readonly>
                                            <input type="hidden" name="nomor" id="nomor_surat_hidden"
                                                value="{{ $tugas->nomor }}">
                                        </div>
                                    </div>
                                </div>
                                <input type="hidden" name="bulan" id="bulan_hidden" value="{{ $tugas->bulan }}">
                                <input type="hidden" name="tahun" id="tahun_hidden" value="{{ $tugas->tahun }}">
                            </div>
                            <div class="mb-3">
                                <label for="tanggal_surat" class="form-label">Tanggal Surat (opsional)</label>
                                <input type="date" name="tanggal_surat" id="tanggal_surat" class="form-control"
                                    value="{{ old('tanggal_surat', optional($tugas->tanggal_surat)->format('Y-m-d')) }}">
                                <small class="text-muted">Kosongkan jika ingin otomatis saat final approve.</small>
                            </div>


                        </div>
                        <div class="form-group">
                            <label for="status_penerima_display">Status Penerima (Otomatis)</label>
                            <input type="text" id="status_penerima_display" class="form-control"
                                style="background-color: #e9ecef; cursor: not-allowed;" value="Belum ada penerima"
                                readonly>
                            <input type="hidden" name="status_penerima" id="status_penerima_hidden">
                        </div>
                    </div>

                    {{-- TAB 2: DETAIL TUGAS --}}
                    <div class="tab-pane fade" id="tab-isi" role="tabpanel">
                        <div class="row">
                            <div class="col-md-7">
                                <div class="form-group">
                                    <label for="jenis_tugas">Kategori Tugas</label>
                                    <select name="jenis_tugas" id="jenis_tugas" class="form-control select2bs4">
                                        @foreach ($taskMaster as $jt)
                                            <option value="{{ $jt->nama }}" @selected(old('jenis_tugas', $tugas->jenis_tugas) == $jt->nama)>
                                                {{ $jt->nama }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="tugas">Jenis Tugas Spesifik</label>
                                    <select name="tugas" id="tugas" class="form-control select2bs4">
                                        <option value="">Pilih Tugas...</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-5">
                                <label>Pratinjau Pilihan Tugas</label>
                                <div id="task-preview"><span class="placeholder-text text-center">Pilih kategori &
                                        tugas untuk melihat pratinjau.</span></div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="nama_umum">Perihal Surat</label>
                            <input type="text" name="nama_umum" id="nama_umum" class="form-control"
                                placeholder="Contoh: Penugasan sebagai Dosen Pembimbing Skripsi"
                                value="{{ old('nama_umum', $tugas->nama_umum) }}" required>
                        </div>
                        <hr class="my-4">
                        <div class="form-group">
                            <label for="redaksi_pembuka">Redaksi Pembuka</label>
                            <textarea name="redaksi_pembuka" id="redaksi_pembuka" class="form-control" rows="3"
                                placeholder="Contoh: Sehubungan dengan acara Seminar...">{{ old('redaksi_pembuka', $tugas->redaksi_pembuka) }}</textarea>
                        </div>
                        <div class="form-group">
                            <label for="detail_tugas_editor">Isi / Detail Rincian Tugas (Opsional)</label>
                            <textarea name="detail_tugas" id="detail_tugas_editor">{{ old('detail_tugas', $tugas->detail_tugas) }}</textarea>
                        </div>
                    </div>

                    {{-- TAB 3: PELAKSANAAN --}}
                    <div class="tab-pane fade" id="tab-pelaksanaan" role="tabpanel">
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label for="waktu_mulai">Waktu Mulai</label>
                                <input type="datetime-local" id="waktu_mulai" name="waktu_mulai" class="form-control"
                                    value="{{ old('waktu_mulai', $tugas->waktu_mulai ? $tugas->waktu_mulai->format('Y-m-d\TH:i') : '') }}">
                            </div>
                            <div class="col-md-6 form-group">
                                <label for="waktu_selesai">Waktu Selesai</label>
                                <input type="datetime-local" id="waktu_selesai" name="waktu_selesai"
                                    class="form-control"
                                    value="{{ old('waktu_selesai', $tugas->waktu_selesai ? $tugas->waktu_selesai->format('Y-m-d\TH:i') : '') }}">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="tempat">Tempat Pelaksanaan</label>
                            <input type="text" id="tempat" name="tempat" class="form-control"
                                placeholder="Cth: Ruang Teater, Gedung Thomas Aquinas"
                                value="{{ old('tempat', $tugas->tempat) }}">
                        </div>
                    </div>
                </div>
            </div>
        </div>
        </div>

        {{-- Kanan: Penerima & Aksi --}}
        <div class="col-lg-4">
            <div class="card card-success card-outline mb-4">
                <div class="card-header">
                    <h3 class="card-title font-weight-bold"><i class="fas fa-users mr-2"></i>Penerima Tugas</h3>
                </div>
                <div class="card-body">
                    <div id="penerima-list-container">
                        <p class="text-muted text-center py-3" id="penerima-placeholder">Belum ada penerima dipilih.
                        </p>
                        <ul id="penerima-list" class="list-group list-group-flush"></ul>
                    </div>
                    <hr>
                    <button type="button" class="btn btn-sm btn-info btn-block mb-2" data-toggle="modal"
                        data-target="#penerimaModal">
                        <i class="fas fa-user-check mr-2"></i> Pilih dari Pengguna Sistem
                    </button>
                    <button type="button" class="btn btn-sm btn-success btn-block" data-toggle="modal"
                        data-target="#penerimaEksternalModal">
                        <i class="fas fa-user-plus mr-2"></i> Tambah Penerima Luar (Manual)
                    </button>
                </div>
            </div>
            <div class="card card-info card-outline">
                <div class="card-header">
                    <h3 class="card-title font-weight-bold"><i class="fas fa-paper-plane mr-2"></i>Aksi & Persetujuan
                    </h3>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label for="penandatangan">Pilih Penandatangan</label>
                        <select name="penandatangan" id="penandatangan" class="form-control select2bs4" required>
                            @foreach ($pejabat as $p)
                                <option value="{{ $p->id }}" @selected(old('penandatangan', $tugas->penandatangan) == $p->id)>
                                    {{ $p->nama_lengkap }} ({{ $p->peran->nama }})</option>
                            @endforeach
                        </select>
                    </div>
                    <hr>
                    <button type="button" name="action" value="draft" class="btn btn-block btn-secondary mb-2"><i
                            class="fas fa-save mr-2"></i>Simpan Draft</button>
                    <button type="button" name="action" value="submit" class="btn btn-block btn-primary"><i
                            class="fas fa-check-circle mr-2"></i>Ajukan</button>
                    @if (request('mode') === 'koreksi')
                        @can('edit-surat', $tugas)
                            <div class="mt-2">
                                <button type="submit" class="btn btn-block btn-warning mb-2">
                                    <i class="fas fa-pen mr-2"></i>Simpan Koreksi
                                </button>
                                <button type="submit" name="simpan_approve" value="1"
                                    class="btn btn-block btn-success">
                                    <i class="fas fa-check mr-2"></i>Simpan & Approve
                                </button>
                            </div>
                        @endcan
                    @endif

                </div>
            </div>
        </div>
        </div>
    </form>

    {{-- MODAL TAMBAH PENERIMA EKSTERNAL --}}
    <div class="modal fade" id="penerimaEksternalModal" tabindex="-1" role="dialog"
        aria-labelledby="penerimaEksternalModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="penerimaEksternalModalLabel"><i class="fas fa-user-plus mr-2"></i>Tambah
                        Penerima Manual</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <form id="form-penerima-eksternal" onsubmit="return false;">
                        <div class="form-group">
                            <label for="nama_eksternal">Nama Lengkap</label>
                            <input type="text" id="nama_eksternal" class="form-control"
                                placeholder="Masukkan nama lengkap" required>
                        </div>
                        <div class="form-group">
                            <label for="jabatan_eksternal">Jabatan / Posisi</label>
                            <select id="jabatan_eksternal" class="form-control" required>
                                <option value="" disabled selected>-- Pilih Posisi --</option>
                                <option value="Mahasiswa">Mahasiswa</option>
                                <option value="Dosen Luar">Dosen Luar</option>
                                <option value="Umum">Umum</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" id="simpanPenerimaEksternal">Tambah ke Daftar</button>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL PILIH PENERIMA INTERNAL --}}
    <div class="modal fade" id="penerimaModal" tabindex="-1" role="dialog" aria-labelledby="penerimaModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="penerimaModalLabel"><i class="fas fa-user-check mr-2"></i>Pilih Penerima
                        Tugas</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <table id="penerima-table" class="table table-bordered table-striped table-hover" style="width:100%">
                        <thead>
                            <tr>
                                <th style="width:5%;"><input type="checkbox" id="select-all-penerima"></th>
                                <th style="width:30%;">Nama Lengkap</th>
                                <th style="width:30%;">Email</th>
                                <th style="width:20%;">Jabatan</th>
                                <th style="width:15%;">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($users as $user)
                                <tr>
                                    <td>
                                        <input type="checkbox" class="penerima-checkbox" value="{{ $user->id }}"
                                            data-nama="{{ $user->nama_lengkap }}" data-peran-id="{{ $user->peran_id }}"
                                            data-jabatan="{{ $user->jabatan ?: $user->peran->deskripsi }}"
                                            data-status-deskripsi="{{ $user->peran->deskripsi }}">
                                    </td>
                                    <td>{{ $user->nama_lengkap }}</td>
                                    <td>{{ $user->email }}</td>
                                    <td>{{ $user->jabatan ?: '-' }}</td>
                                    <td>
                                        <span class="badge badge-info">{{ $user->peran->deskripsi }}</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-default" data-dismiss="modal"><i
                            class="fas fa-times mr-2"></i>Batal</button>
                    <button type="button" class="btn btn-primary" id="simpanPenerima"><i
                            class="fas fa-check mr-2"></i>Simpan Pilihan</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap4.min.js"></script>
    <script src="https://cdn.ckeditor.com/ckeditor5/41.4.2/classic/ckeditor.js"></script>
    <script>
        $(function() {
            // --- Inisialisasi plugin
            $('.select2bs4').select2({
                theme: 'bootstrap4',
                width: '100%'
            });
            const table = $('#penerima-table').DataTable({
                responsive: true,
                lengthChange: true,
                autoWidth: false,
                language: {
                    search: "Cari:",
                    lengthMenu: "Tampilkan _MENU_ data",
                    info: "Menampilkan _START_-_END_ dari _TOTAL_",
                    zeroRecords: "Tidak ditemukan",
                    paginate: {
                        next: ">>",
                        previous: "<<"
                    }
                },
                columnDefs: [{
                    orderable: false,
                    targets: 0
                }]
            });
            ClassicEditor.create(document.querySelector('#detail_tugas_editor'), {
                toolbar: {
                    items: ['heading', '|', 'bold', 'italic', 'link', '|', 'bulletedList', 'numberedList',
                        '|', 'undo', 'redo'
                    ],
                    shouldNotGroupWhenFull: true
                }
            }).catch(error => {
                console.error('Error initializing CKEditor:', error);
            });

            // -- Logika Nomor Surat
            const $nomorUrutInput = $('#nomor_urut');
        const $klasifikasiSelect = $('#klasifikasi_surat');
        const $nomorDisplay = $('#nomor_surat_display');
        const $nomorHidden = $('#nomor_surat_hidden');
        const $bulanHidden = $('#bulan_hidden');
        const $tahunHidden = $('#tahun_hidden');

        function updateNomorSurat() {
            const noUrut = $nomorUrutInput.val().padStart(3, '0');
            const kode = $klasifikasiSelect.find(':selected').data('kode') || '...';
            const bulan = $bulanHidden.val();
            const tahun = $tahunHidden.val();

            const nomorLengkap = `${noUrut}/${kode}/TG/UNIKA/${bulan}/${tahun}`;

            $nomorDisplay.val(nomorLengkap);
            $nomorHidden.val(nomorLengkap);
        }
        $nomorUrutInput.on('input', updateNomorSurat);
        $klasifikasiSelect.on('change', updateNomorSurat);

        // Panggil sekali di awal untuk memastikan tampilan benar
        updateNomorSurat(); 

            // -- Dropdown tugas & preview
            const taskData = @json($taskMaster);
            const $tugasPreview = $('#task-preview');
            const placeholderText =
                `<span class="placeholder-text text-center">Pilih kategori & tugas untuk melihat pratinjau.</span>`;

            function updateTaskPreview() {
                const kategori = $('#jenis_tugas').val();
                const tugas = $('#tugas').val();
                if (kategori && tugas) {
                    const previewHtml =
                        `<div><p class="mb-1 text-muted">Kategori:</p><h5 class="preview-title mb-3"><i class="fas fa-layer-group mr-2"></i>${kategori}</h5><p class="mb-1 text-muted">Tugas Spesifik:</p><p class="preview-content font-weight-bold">${tugas}</p></div>`;
                    $tugasPreview.html(previewHtml).addClass('has-content');
                } else {
                    $tugasPreview.html(placeholderText).removeClass('has-content');
                }
            }

            function populateSpecificTask(selectedKategori, preselectedTugas) {
                const $tugasSelect = $('#tugas');
                $tugasSelect.empty().append(new Option('Pilih Tugas...', ''));
                const found = taskData.find(jt => jt.nama === selectedKategori);
                if (found && Array.isArray(found.subtugas) && found.subtugas.length) {
                    found.subtugas.forEach(st => {
                        const isSelected = preselectedTugas === st.nama;
                        $tugasSelect.append(new Option(st.nama, st.nama, isSelected, isSelected));
                    });
                    $tugasSelect.prop('disabled', false);
                } else {
                    $tugasSelect.prop('disabled', true);
                }
                $tugasSelect.trigger('change.select2');
                updateTaskPreview();
            }
            $('#jenis_tugas').on('change', function() {
                populateSpecificTask($(this).val(), null);
            });
            $('#tugas').on('change', updateTaskPreview);
            const oldJenis = "{{ old('jenis_tugas', $tugas->jenis_tugas) }}";
            if (oldJenis) {
                populateSpecificTask(oldJenis, "{{ old('tugas', $tugas->tugas) }}");
            }

            // --------- INISIALISASI PENERIMA (internal & eksternal) ---------
            const allUsersData = @json($users->keyBy('id'));
            const oldInternalIds = @json(old('penerima_internal', []));
            const oldEksternal = @json(old('penerima_eksternal', []));
            // Siapkan data dari DB (penerima lama) jika tidak ada old
            let penerimaState = {
                internal: {},
                eksternal: []
            };
            if (oldInternalIds.length > 0) {
                oldInternalIds.forEach(id => {
                    if (allUsersData[id]) {
                        penerimaState.internal[id] = {
                            nama: allUsersData[id].nama_lengkap,
                            peran_id: allUsersData[id].peran_id
                        };
                    }
                });
            } else {
                @foreach ($tugas->penerima->whereNotNull('pengguna_id') as $p)
                    penerimaState.internal[{{ $p->pengguna_id }}] = {
                        nama: "{{ $p->pengguna->nama_lengkap }}",
                        peran_id: {{ $p->pengguna->peran_id }}
                    };
                @endforeach
            }
            if (oldEksternal.length > 0) {
                penerimaState.eksternal = oldEksternal;
            } else {
                penerimaState.eksternal = @json(
                    $tugas->penerima->whereNull('pengguna_id')->map(function ($p) {
                            return ['nama' => $p->nama_penerima, 'jabatan' => $p->jabatan_penerima];
                        })->values());
            }
            // --------- END INISIALISASI PENERIMA ---------

            function updateStatusPenerima() {
                const $displayInput = $('#status_penerima_display');
                const $hiddenInput = $('#status_penerima_hidden');
                const statusSet = new Set();
                // Internal
                for (const id in penerimaState.internal) {
                    const peranId = penerimaState.internal[id].peran_id;
                    if (peranId == 1 || peranId == 6) {
                        statusSet.add('Tendik');
                    } else {
                        statusSet.add('Dosen');
                    }
                }
                // Eksternal
                penerimaState.eksternal.forEach(penerima => {
                    const jabatan = (penerima.jabatan || '').toLowerCase();
                    if (jabatan === 'mahasiswa') statusSet.add('Mahasiswa');
                    else if (jabatan === 'dosen luar') statusSet.add('Dosen');
                    else if (jabatan === 'umum') statusSet.add('Umum');
                });
                // Gabungkan hasil & tampilkan
                if (statusSet.size === 0) {
                    $displayInput.val('Belum ada penerima');
                    $hiddenInput.val('');
                } else {
                    const statusArray = Array.from(statusSet).sort();
                    let displayText = '';
                    if (statusArray.length === 1) displayText = statusArray[0];
                    else if (statusArray.length === 2) displayText = statusArray.join(' dan ');
                    else {
                        const allButLast = statusArray.slice(0, -1).join(', ');
                        const lastItem = statusArray[statusArray.length - 1];
                        displayText = `${allButLast}, dan ${lastItem}`;
                    }
                    $displayInput.val(displayText);
                    $hiddenInput.val(displayText.toLowerCase());
                }
            }

            function renderPenerimaList() {
                const listContainer = $('#penerima-list');
                const placeholder = $('#penerima-placeholder');
                listContainer.empty();
                $('input[name^="penerima_internal"],input[name^="penerima_eksternal"]').remove();

                const internalCount = Object.keys(penerimaState.internal).length;
                const eksternalCount = penerimaState.eksternal.length;

                if (internalCount === 0 && eksternalCount === 0) {
                    placeholder.show();
                } else {
                    placeholder.hide();
                    for (const id in penerimaState.internal) {
                        const data = penerimaState.internal[id];
                        const itemHtml = `<li class="list-group-item d-flex justify-content-between align-items-center">
                            <div><i class="fas fa-user-tie mr-2 text-info"></i>${data.nama}</div>
                            <button type="button" class="btn btn-xs btn-danger remove-penerima" data-type="internal" data-id="${id}"><i class="fas fa-times"></i></button>
                        </li>`;
                        listContainer.append(itemHtml);
                        $('#tugasForm').append(`<input type="hidden" name="penerima_internal[]" value="${id}">`);
                    }
                    penerimaState.eksternal.forEach((p, index) => {
                        const itemHtml = `<li class="list-group-item d-flex justify-content-between align-items-center">
                            <div><i class="fas fa-user mr-2 text-success"></i>${p.nama} <span class="eksternal-label">(${p.jabatan})</span></div>
                            <button type="button" class="btn btn-xs btn-danger remove-penerima" data-type="eksternal" data-id="${index}"><i class="fas fa-times"></i></button>
                        </li>`;
                        listContainer.append(itemHtml);
                        $('#tugasForm').append(
                            `<input type="hidden" name="penerima_eksternal[${index}][nama]" value="${p.nama}">`
                        );
                        $('#tugasForm').append(
                            `<input type="hidden" name="penerima_eksternal[${index}][jabatan]" value="${p.jabatan}">`
                        );
                    });
                }
                updateStatusPenerima();
            }
            $('#simpanPenerima').on('click', function() {
                penerimaState.internal = {};
                table.$('.penerima-checkbox:checked').each(function() {
                    const id = $(this).val();
                    const nama = $(this).data('nama');
                    const peranId = $(this).data('peran-id');
                    penerimaState.internal[id] = {
                        nama: nama,
                        peran_id: peranId
                    };
                });
                renderPenerimaList();
                $('#penerimaModal').modal('hide');
                Swal.fire({
                    icon: 'success',
                    title: 'Penerima Disimpan!',
                    text: 'Daftar penerima berhasil diperbarui.',
                    showConfirmButton: false,
                    timer: 1500
                });
            });
            $('#simpanPenerimaEksternal').on('click', function() {
                const nama = $('#nama_eksternal').val().trim();
                const jabatan = $('#jabatan_eksternal').val().trim();
                if (nama && jabatan) {
                    penerimaState.eksternal.push({
                        nama,
                        jabatan
                    });
                    renderPenerimaList();
                    $('#form-penerima-eksternal')[0].reset();
                    $('#penerimaEksternalModal').modal('hide');
                } else {
                    Swal.fire('Lengkapi Nama & Jabatan', '', 'warning');
                }
            });
            $('#penerima-list').on('click', '.remove-penerima', function() {
                const type = $(this).data('type'),
                    id = $(this).data('id');
                if (type === 'internal') delete penerimaState.internal[id];
                else penerimaState.eksternal.splice(id, 1);
                renderPenerimaList();
            });

            // -- Tombol simpan draft/submit + konfirmasi
            let clickedAction = null;
            $('button[name="action"]').on('click', function(e) {
                e.preventDefault(); // Selalu cegah aksi default
                const clickedAction = $(this).val(); // 'draft' atau 'submit'
                const $form = $('#tugasForm');

                // Validasi minimal ada satu penerima
                const internalCount = Object.keys(penerimaState.internal).length;
                const eksternalCount = penerimaState.eksternal.length;
                if (internalCount === 0 && eksternalCount === 0) {
                    Swal.fire('Peringatan', 'Anda harus memilih setidaknya satu penerima tugas.',
                        'warning');
                    return; // Hentikan proses
                }

                const isSubmit = clickedAction === 'submit';
                Swal.fire({
                    title: isSubmit ? 'Ajukan Surat Tugas?' : 'Simpan sebagai Draft?',
                    text: isSubmit ?
                        'Surat akan dikirim untuk persetujuan. Lanjutkan?' :
                        'Anda dapat mengedit draft ini nanti. Simpan sekarang?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: isSubmit ? 'Ya, Ajukan' : 'Ya, Simpan',
                    cancelButtonText: 'Batal',
                    reverseButtons: true,
                }).then((result) => {
                    if (result.isConfirmed) {
                        // 1. Hapus input 'action' lama jika ada.
                        $form.find('input[name="action"]').remove();

                        // 2. Buat dan sisipkan input hidden baru.
                        $('<input>').attr({
                            type: 'hidden',
                            name: 'action',
                            value: clickedAction
                        }).appendTo($form);

                        // 3. Tampilkan loading & kirim form.
                        Swal.fire({
                            title: 'Memproses...',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                                $form.find('button').prop('disabled', true);
                                $form.submit();
                            },
                            showConfirmButton: false,
                        });
                    }
                });
            });
            // Fallback submit
            $('#tugasForm').on('submit', function(e) {
                if (!clickedAction) {
                    e.preventDefault();
                    Swal.fire({
                        title: 'Pilih Aksi',
                        text: 'Anda belum memilih apakah ingin menyimpan draft atau mengajukan surat tugas.',
                        icon: 'warning',
                        showDenyButton: true,
                        showCancelButton: true,
                        confirmButtonText: 'Simpan Draft',
                        denyButtonText: 'Ajukan',
                        cancelButtonText: 'Batal',
                        reverseButtons: true,
                    }).then((result) => {
                        if (result.isConfirmed) clickedAction = 'draft';
                        else if (result.isDenied) clickedAction = 'submit';
                        else return;
                        $('input[name="action"]', '#tugasForm').remove();
                        $('<input>')
                            .attr({
                                type: 'hidden',
                                name: 'action',
                                value: clickedAction
                            })
                            .appendTo('#tugasForm');
                        Swal.fire({
                            title: 'Sedang diproses…',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                                $('#tugasForm').find('button[type="button"]').prop(
                                    'disabled', true).addClass('disabled');
                                $('#tugasForm')[0].submit();
                            },
                            showConfirmButton: false,
                        });
                    });
                }
            });

            $('#tanggal_surat').on('change', function() {
                const v = this.value;
                const d = new Date(v);
                if (isNaN(d.getTime())) return;

                const romans = ['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'];
                const r = romans[d.getMonth()];
                const y = d.getFullYear();

                $('input[name="bulan"]').val(r);
                $('input[name="tahun"]').val(y);

                // Opsional: update preview nomor di input readonly #nomor_surat
                const val = $('#nomor_surat').val();
                if (val && val.includes('/')) {
                    const parts = val.split('/');
                    // Biasanya format: .../<BULAN_ROMAWI>/<TAHUN>
                    if (parts.length >= 2) {
                        parts[parts.length - 2] = r;
                        parts[parts.length - 1] = String(y);
                        $('#nomor_surat').val(parts.join('/'));
                    }
                }
            });

            // Render awal
            renderPenerimaList();
        });
    </script>
@endpush
