@extends('layouts.app')
@section('title', 'Daftar Surat Tugas')

@push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap4.min.css">
    <style>
        body { background: #f7faff; }
        /* HEADER */
        .surat-header {
            background: #f3f6fa;
            padding: 1.3rem 2.2rem 1.3rem 1.8rem;
            border-radius: 1.1rem;
            margin-bottom: 2.2rem;
            border: 1px solid #e0e6ed;
            display: flex; align-items: center; gap: 1.3rem;
        }
        .surat-header .icon {
            background: linear-gradient(135deg,#1498ff 0,#1fc8ff 100%);
            width: 54px; height: 54px;
            display: flex; align-items: center; justify-content: center;
            border-radius: 50%;
            box-shadow: 0 1px 10px #1498ff30;
            font-size: 2rem;
        }
        .surat-header-title {
            font-weight: bold;
            color: #0056b3;
            font-size: 1.85rem;
            margin-bottom: 0.13rem;
            letter-spacing: -1px;
        }
        .surat-header-desc {
            color: #636e7b; font-size: 1.03rem;
        }
        /* STAT */
        .stat-wrapper {
            display: flex; justify-content: flex-start; gap: 1.2rem; margin-bottom: 2.1rem;
            flex-wrap: wrap;
        }
        .stat-card { width: 170px; border-radius: .85rem; border: none; background: #fff; }
        .stat-card .card-body { text-align: center; padding: 1.15rem 1rem; }
        .stat-card .icon { font-size: 2.3rem; margin-bottom: .5rem; }
        .stat-card .label { color: #6c757d; font-size: .83rem; margin-bottom: .25rem; font-weight: 600; text-transform: uppercase; letter-spacing: 1px;}
        .stat-card .value { font-size: 2.1rem; font-weight: 700; line-height: 1.1; }
        /* FILTER */
        .card.filter-card { margin-bottom: 2.2rem; border-radius: 1rem; }
        .card.filter-card .card-header { background: #f8fafc; border-radius: 1rem 1rem 0 0; border: none; }
        .card.filter-card .card-body { padding-bottom: 0.7rem; }
        /* TABEL */
        .card.data-card { border-radius: 1rem; }
        .card.data-card .card-body { padding-top: 1.2rem; }
        .table th, .table td { vertical-align: middle !important; }
        .table { background: #fff; }
        /* RESPONSIVE + MOBILE */
        @media (max-width: 767.98px) {
            .surat-header { flex-direction: column; align-items: flex-start; padding: 1.2rem 1rem; gap: .7rem; }
            .stat-wrapper { flex-direction: column; gap: .8rem; }
            .stat-card { width: 100%; }
            .surat-header-title { font-size: 1.18rem; }
            .surat-header-desc { font-size: .99rem; }
            .card.filter-card, .card.data-card { border-radius: .6rem; }
        }
        /* Custom badge, filter btn, dan spacing */
        .badge-info { background: #0bb1e3 !important; color: #fff; }
        .card .btn { font-size: 0.96rem; paddi }
        .dropdown-menu a.dropdown-item { cursor: pointer; }
        .dropdown-menu .fa-fw { margin-right: 8px; }
        #quickViewModal .modal-body { height: 75vh; }
        .quickview-spinner { position: absolute; top:48%; left:48%; z-index:10; display:none }
    </style>
@endpush

@section('content_header')
<div class="surat-header mt-2 mb-3">
    <span class="icon">
        <i class="fas fa-envelope-open-text text-white"></i>
    </span>
    <span>
        <div class="surat-header-title">Daftar Surat Tugas</div>
        <div class="surat-header-desc">
            Semua surat tugas <b>sekolah</b> — kelola, filter, cetak PDF, dan lacak statusnya di sini.
        </div>
    </span>
</div>
@endsection

@section('content')
<div class="container-fluid px-2">
    {{-- Statistik --}}
    <div class="d-flex justify-content-center w-100 mb-3">
        <div class="stat-wrapper py-1" style="width: 100%; max-width: 650px;">
            @foreach([
                'draft'     => ['icon'=>'fa-file-alt', 'label'=>'Draft', 'count'=>$stats['draft'] ?? 0, 'color'=>'secondary'],
                'pending'   => ['icon'=>'fa-hourglass-half', 'label'=>'Pending', 'count'=>$stats['pending'] ?? 0, 'color'=>'warning'],
                'disetujui' => ['icon'=>'fa-check-circle', 'label'=>'Disetujui', 'count'=>$stats['disetujui'] ?? 0, 'color'=>'success'],
            ] as $status => $info)
            <div class="stat-card card shadow-sm mx-2">
                <div class="card-body">
                    <div class="icon text-{{ $info['color'] }}" data-toggle="tooltip" title="{{ $info['label'] }}">
                        <i class="fas {{ $info['icon'] }}"></i>
                    </div>
                    <div class="label">{{ $info['label'] }}</div>
                    <div class="value text-{{ $info['color'] }}">{{ $info['count'] }}</div>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Filter dan Tombol --}}
    <div class="card filter-card mb-4 shadow-sm">
        <div class="card-header bg-white border-0 py-3">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h5 class="mb-0 font-weight-bold">
                    <i class="fas fa-filter mr-2 text-primary"></i>Filter & Pencarian
                </h5>
                <div class="d-flex gap-2">
                    <a href="{{ route('surat_tugas.create') }}" class="btn btn-primary"><i class="fas fa-plus mr-2"></i>Tambah Surat Tugas</a>
                    <a href="{{ route('jenis_surat_tugas.index') }}" class="btn btn-outline-secondary"><i class="fas fa-cog mr-2"></i>Manage Jenis Surat</a>
                </div>
            </div>
        </div>
        <div class="card-body">
            <form class="row">
                <div class="col-md-6 form-group mb-2">
                    <input id="globalSearch" type="text" class="form-control" placeholder="Cari berdasarkan nomor, perihal, pembuat, atau penerima...">
                </div>
                <div class="col-md-3 form-group mb-2">
                    <select id="statusFilter" class="form-control">
                        <option value="">Semua Status</option>
                        <option value="draft">Draft</option>
                        <option value="pending">Pending</option>
                        <option value="disetujui">Disetujui</option>
                    </select>
                </div>
                <div class="col-md-3 form-group mb-2">
                    <button id="resetFilters" class="btn btn-outline-secondary w-100" type="button">
                        <i class="fas fa-redo mr-1"></i>Reset Filter
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Tabel Utama --}}
    <div class="card data-card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table id="table-tugas" class="table table-hover" style="width:100%">
                    <thead>
                        <tr class="text-center">
                            <th>No</th>
                            <th>Nomor Surat</th>
                            <th>Perihal</th>
                            <th>Tgl Surat</th>
                            <th>Pembuat</th>
                            <th>Penerima</th>
                            <th>Status</th>
                            <th>Berkas</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($list as $h)
                        <tr>
                            <td class="text-center">{{ $loop->iteration }}</td>
                            <td>{{ $h->nomor }}</td>
                            <td>{{ $h->nama_umum }}</td>
                            @php $tgl = $h->tanggal_utama; @endphp
                            <td class="text-center" data-sort="{{ $tgl ? $tgl->timestamp : 0 }}">
                                {{ $tgl ? $tgl->format('d M Y') : '-' }}
                                @if($tgl)
                                    <br>
                                    <small class="text-muted">
                                        <i class="far fa-clock"></i> {{ $tgl->diffForHumans() }}
                                    </small>
                                @endif
                            </td>

                            <td>{{ $h->pembuat?->nama_lengkap ?? 'N/A' }}</td>
                            <td>
                                @php
                                    $penerima = $h->penerima->pluck('pengguna.nama_lengkap')->filter();
                                    $penerimaCount = $penerima->count();
                                @endphp
                                @if($penerimaCount > 0)
                                    {{ $penerima->first() }}
                                    @if($penerimaCount > 1)
                                        <span class="badge badge-info ml-1" data-toggle="tooltip" title="Total penerima">{{ '+' . ($penerimaCount - 1) }} lainnya</span>
                                    @endif
                                @else
                                    -
                                @endif
                            </td>
                            <td class="text-center">
                                <span class="badge badge-pill badge-{{ $h->status_surat == 'disetujui' ? 'success' : ($h->status_surat == 'pending' ? 'warning' : 'secondary') }}">
                                    {{ ucfirst($h->status_surat) }}
                                </span>
                            </td>
                            <td class="text-center">
                                @if($h->status_surat == 'disetujui')
                                    <a href="{{ route('surat_tugas.downloadPdf', $h->id) }}" class="btn btn-sm btn-danger" title="Download PDF" target="_blank"><i class="fas fa-file-pdf"></i></a>
                                @else
                                    -
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-secondary dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" title="Menu aksi">
                                        <i class="fas fa-cog"></i>
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-right">
                                        <a class="dropdown-item"
   href="{{ route('surat_tugas.preview', $h->id) }}?v={{ optional($h->updated_at)->timestamp }}"
   data-toggle="modal"
   data-target="#modal-preview"
   data-preview-url="{{ route('surat_tugas.preview', $h->id) }}?v={{ optional($h->updated_at)->timestamp }}">
   <i class="fas fa-search mr-2"></i> Lihat Cepat
</a>


                                        <a class="dropdown-item" href="{{ route('surat_tugas.show', $h->id) }}"><i class="fas fa-fw fa-eye"></i> Halaman Detail</a>
                                        @can('edit-surat', $h)
                                          <a class="dropdown-item"
                                             href="{{ route('surat_tugas.edit', ['tugas' => $h->id, 'mode' => 'koreksi']) }}">
                                             <i class="fas fa-pen mr-2"></i> Koreksi (Approver)
                                          </a>
                                        @endcan

                                        <div class="dropdown-divider"></div>
                                        @if($h->status_surat == 'disetujui')
                                            <a class="dropdown-item" href="{{ route('surat_tugas.downloadPdf', $h->id) }}" target="_blank"><i class="fas fa-fw fa-download"></i> Download PDF</a>
                                        @endif

                                        {{-- PENGGANTIAN UTAMA: Approve langsung -> Tinjau & Setujui --}}
                                        @if($h->status_surat === 'pending' && in_array(auth()->user()->peran_id, [2, 3]) && $h->penandatangan == auth()->id())
                                            {{-- SEBELUM (hapus):
                                            <a class="dropdown-item btn-approve" data-url="{{ route('surat_tugas.approve', $h->id) }}">
                                                <i class="fas fa-fw fa-check text-success"></i> Approve
                                            </a>
                                            --}}
                                            {{-- SESUDAH: arahkan ke halaman detail + auto-scroll ke panel approve --}}
                                            <a class="dropdown-item goto-approve"
                                               href="{{ route('surat_tugas.show', $h->id) }}?approve=1#approve-panel">
                                                <i class="fas fa-fw fa-check text-success"></i> Tinjau & Setujui
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted">Tidak ada data surat tugas ditemukan.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Modal Quick View --}}
<div class="modal fade" id="quickViewModal" tabindex="-1" role="dialog" aria-labelledby="quickViewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="quickViewModalLabel">Pratinjau Surat Tugas</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-0" style="position:relative;">
                <div class="spinner-border text-primary quickview-spinner"></div>
                <iframe src="about:blank" style="width: 100%; border: none; min-height:70vh"></iframe>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
    $(function () {
        $('[data-toggle="tooltip"]').tooltip();
        const table = $('#table-tugas').DataTable({
            responsive: true,
            autoWidth: false,
            language: { url: "/assets/datatables/i18n/id.json" },
            columnDefs: [
                { targets: [7, 8], orderable: false, searchable: false }
            ]
        });

        $('#globalSearch').on('keyup', function() { table.search(this.value).draw(); });
        $('#statusFilter').on('change', function() {
            const status = this.value;
            if (status) {
                table.column(6).search('^' + status + '$', true, false).draw();
            } else {
                table.column(6).search('').draw();
            }
        });
        $('#resetFilters').on('click', function(e) {
            e.preventDefault();
            $('#globalSearch, #statusFilter').val('');
            table.search('').columns().search('').draw();
        });

        @if(session('success'))
            Swal.fire({ icon: 'success', title: 'Berhasil!', text: "{{ session('success') }}", timer: 2500, showConfirmButton: false });
        @endif

        // Handler dropdown item
        $('#table-tugas').on('click', '.dropdown-item', function(e) {
            e.preventDefault();
            const action = $(this);
            if (action.hasClass('quick-view')) {
                const url = action.data('url');
                $('.quickview-spinner').show();
                const iframe = $('#quickViewModal iframe');
                iframe.off('load').on('load', function() { $('.quickview-spinner').hide(); });
                iframe.attr('src', url);
                $('#quickViewModal').modal('show');
            } 
            else if (action.hasClass('btn-delete')) {
                const url = action.data('url');
                Swal.fire({
                    title: 'Anda yakin?', text: "Surat ini akan dihapus permanen.", icon: 'warning',
                    showCancelButton: true, confirmButtonColor: '#d33', cancelButtonText: 'Batal', confirmButtonText: 'Ya, hapus!'
                }).then(result => {
                    if (result.isConfirmed) {
                        const form = $('<form>', { 'method': 'POST', 'action': url, 'style':'display:none' })
                            .append('@csrf')
                            .append('@method("DELETE")');
                        $('body').append(form);
                        form.submit();
                    }
                });
            } 
            // PENGHAPUSAN: blok approve instan dihapus agar wajib lewat UI
            else {
                // default: navigasi ke href (Detail, Preview, Tinjau & Setujui, dll.)
                window.location.href = action.attr('href');
            }
        });

        $('#quickViewModal').on('hidden.bs.modal', function () {
            const iframe = $(this).find('iframe');
            iframe.off('load');
            iframe.attr('src', 'about:blank');
            $('.quickview-spinner').hide();
        });
    });

    // Handler modal preview (tetap dipertahankan sesuai kode awal)
    document.addEventListener('DOMContentLoaded', function(){
      $('#modal-preview').on('show.bs.modal', function (e) {
        const btn = $(e.relatedTarget);
        const url = btn.data('preview-url') || btn.attr('href');
        if (!url) {
          $('#preview-frame').attr('src', 'about:blank');
          return;
        }
        $('#preview-frame').attr('src', url);
      });

      $('#modal-preview').on('hidden.bs.modal', function () {
        $('#preview-frame').attr('src', 'about:blank');
      });
    });
    </script>
@endpush
