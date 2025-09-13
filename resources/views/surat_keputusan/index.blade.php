@extends('layouts.app')
@section('title', 'Daftar Surat Keputusan')

@push('styles')
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">

    <style>
    /* ========== Header Ungu ========== */
    .header-title {
        color: #800080;
        font-size: 2rem;
        font-weight: 700;
        border-bottom: 4px solid #800080;
        display: inline-block;
        padding-bottom: .25rem;
        margin-bottom: 1.5rem;
    }

    /* ========== Statistik Minimalis Centered ========== */
    .stat-wrapper {
        display: flex;
        justify-content: center;
        align-items: center;
        flex-wrap: wrap;
        gap: 1rem;
        margin-bottom: 2rem;
    }
    .stat-card { width:160px; border-radius:.75rem }
    .stat-card .card-body { text-align:center; padding:1rem }
    .stat-card .icon { font-size:1.75rem; margin-bottom:.5rem }
    .stat-card .label { color:#6c757d; font-size:.85rem; margin-bottom:.25rem; font-weight:600 }
    .stat-card .value { font-size:1.75rem; font-weight:700; line-height:1 }

    /* ========== Override form-select ========== */
    .form-select {
      appearance:none; width:100% !important;
      padding:.375rem .75rem !important;
      border:1px solid #ced4da !important; border-radius:.375rem !important;
      background:#fff url("data:image/svg+xml;charset=UTF-8,%3Csvg viewBox='0 0 4 5' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M2 0L0 2h4zm0 5L0 3h4z' fill='%236c757d'/%3E%3C/svg%3E")
        no-repeat right .75rem center !important;
      background-size:.65em auto !important;
    }
    .form-select::-ms-expand { display:none }
    .form-select:focus {
      border-color:#800080 !important;
      box-shadow:0 0 0 .2rem rgba(128,0,128,.25) !important;
      outline:none !important;
    }

    /* ========== Badge Status ========== */
    .badge-status-draft     { background:#f8f9fa; color:#6c757d }
    .badge-status-pending   { background:#fff3cd; color:#856404 }
    .badge-status-disetujui { background:#d1e7dd; color:#0f5132 }

    /* ========== DataTables tweak ========== */
    #table-keputusan_wrapper .dataTables_length,
    #table-keputusan_wrapper .dataTables_filter { display:none }
    .dataTables_paginate .paginate_button.current,
    .dataTables_paginate .paginate_button.current:hover {
        background:#0d6efd !important; color:#fff !important;
    }
    .dataTables_paginate .paginate_button:hover {
        background:rgba(13,110,253,.1) !important; color:#0d6efd !important;
    }
    </style>
@endpush

@section('content')
<div class="container-fluid px-4">

    {{-- Header Ungu --}}
    <h2 class="header-title">
        <i class="fas fa-list me-2"></i> Daftar Surat Keputusan
    </h2>

    {{-- Flash via SweetAlert --}}
    @if(session('success') || session('error'))
        <div class="d-none" id="swal-flash"
             data-message="{{ session('success') ?? session('error') }}"
             data-type="{{ session('success') ? 'success' : 'error' }}">
        </div>
    @endif

    {{-- Statistik --}}
    <div class="stat-wrapper">
        @foreach([
            'draft'     => ['label'=>'Draft','count'=>$stats['draft'] ?? 0,'color'=>'secondary'],
            'pending'   => ['label'=>'Pending','count'=>$stats['pending'] ?? 0,'color'=>'warning'],
            'disetujui' => ['label'=>'Disetujui','count'=>$stats['disetujui'] ?? 0,'color'=>'success'],
        ] as $key => $info)
        <div class="stat-card card shadow-sm">
            <div class="card-body">
                <div class="icon text-{{ $info['color'] }}">
                    @if($key==='draft')      <i class="fas fa-file-alt"></i>
                    @elseif($key==='pending') <i class="fas fa-hourglass-half"></i>
                    @else                     <i class="fas fa-check-circle"></i>
                    @endif
                </div>
                <div class="label">{{ $info['label'] }}</div>
                <div class="value">{{ $info['count'] }}</div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Tombol Tambah SK --}}
    <div class="d-flex justify-content-end mb-4">
        <a href="{{ route('surat_keputusan.create') }}"
           class="btn btn-primary rounded-pill px-4">
            <i class="fas fa-plus me-2"></i>Tambah Surat Keputusan
        </a>
    </div>

    {{-- Filter/Search --}}
    <div class="card mb-4 shadow-sm border-0">
        <div class="card-header bg-white border-0 py-3">
            <h5 class="mb-0 fw-bold">
                <i class="fas fa-filter me-2 text-primary"></i>Filter & Pencarian
            </h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label small text-uppercase">Cari</label>
                    <input id="globalSearch" class="form-control" placeholder="Ketikan nomor, perihal...">
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-uppercase">Status</label>
                    <select id="statusFilter" class="form-select w-100">
                        <option value="">Semua Status</option>
                        @foreach(['draft'=>'Draft','pending'=>'Pending','disetujui'=>'Disetujui'] as $val=>$lbl)
                            <option value="{{ $val }}">{{ $lbl }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-uppercase">Dari Tgl Dibuat</label>
                    <input id="startDate" type="date" class="form-control">
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-uppercase">Sampai Tgl Dibuat</label>
                    <input id="endDate" type="date" class="form-control">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button id="resetFilters" class="btn btn-outline-secondary w-100">
                        <i class="fas fa-redo me-1"></i>Reset
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabel Surat Keputusan --}}
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table id="table-keputusan" class="table table-hover align-middle w-100 mb-0">
                    <thead class="table-light text-center">
                        <tr>
                            <th>#</th>
                            <th>Nomor</th>
                            <th>Perihal</th>
                            <th>Pembuat</th>
                            <th>Status</th>
                            <th>Tgl Dibuat</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($list as $sk)
                        <tr>
                            <td class="text-center">{{ $loop->iteration }}</td>
                            <td>{{ $sk->nomor }}</td>
                            <td>{{ $sk->tentang ?? '-' }}</td>
                            <td>{{ $sk->pembuat->nama_lengkap ?? '-' }}</td>
                            <td class="text-center">
                                <span class="badge badge-status-{{ $sk->status_surat }}">
                                    {{ ucfirst($sk->status_surat) }}
                                </span>
                            </td>
                            <td>{{ $sk->created_at->format('d-m-Y H:i') }}</td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-1">
                                    {{-- VIEW --}}
                                    <a href="{{ route('surat_keputusan.show', $sk->id) }}"
                                       class="btn btn-sm btn-outline-info" title="Lihat">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    {{-- EDIT & DELETE kalau draft --}}
                                    @if($sk->status_surat==='draft')
                                        <a href="{{ route('surat_keputusan.edit', $sk->id) }}"
                                           class="btn btn-sm btn-outline-warning" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('surat_keputusan.destroy', $sk->id) }}"
                                              method="POST" class="d-inline form-delete">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger"
                                                    title="Hapus">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                Belum ada surat keputusan.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
    <!-- Dependencies -->
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // SweetAlert2 flash
    const flash = document.getElementById('swal-flash');
    if (flash) {
        Swal.fire({
            icon: flash.dataset.type,
            title: flash.dataset.type==='success' ? 'Berhasil!' : 'Error',
            text: flash.dataset.message,
            timer: 2500,
            showConfirmButton: false
        });
    }

    // Init DataTable & filters
    const table = $('#table-keputusan').DataTable({
        dom: 'rt<"d-flex justify-content-between align-items-center mt-3 px-3 pb-2"ip>',
        language: { url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json" },
        columnDefs:[
            { orderable:false, targets:[0,6] },
            { searchable:false, targets:[0,4,6] }
        ],
        pagingType:"simple_numbers"
    });

    $('#globalSearch').on('keyup', ()=>table.search($('#globalSearch').val()).draw());
    $('#statusFilter').on('change', ()=>table.column(4).search($('#statusFilter').val()? `^${$('#statusFilter').val()}$` : '', true, false).draw());
    $('#startDate,#endDate').on('change', ()=>table.draw());
    $('#resetFilters').on('click', ()=>{
        $('#globalSearch,#statusFilter,#startDate,#endDate').val('');
        table.search('').columns().search('').draw();
    });

    // confirm delete
    $(document).on('submit','.form-delete', function(e){
        e.preventDefault();
        const form = this;
        Swal.fire({
            title:'Yakin hapus surat ini?',
            text:'Data yang sudah dihapus tidak dapat dikembalikan.',
            icon:'warning',
            showCancelButton:true,
            confirmButtonText:'Ya, hapus!',
            cancelButtonText:'Batal',
            reverseButtons:true
        }).then(r=> r.isConfirmed && form.submit());
    });
});
</script>
@endpush
