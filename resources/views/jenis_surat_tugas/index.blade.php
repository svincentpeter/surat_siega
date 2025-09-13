{{-- resources/views/jenis_surat_tugas/index.blade.php --}}
@extends('layouts.app')
@section('title', 'Daftar Jenis Surat Tugas')

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
    /* ========== Override form-select (jika pakai) ========== */
    .form-select {
      appearance: none !important;
      width: 100% !important;
      padding: .375rem .75rem !important;
      border: 1px solid #ced4da !important;
      border-radius: .375rem !important;
      background: #fff url("data:image/svg+xml;charset=UTF-8,%3Csvg viewBox='0 0 4 5' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M2 0L0 2h4zm0 5L0 3h4z' fill='%236c757d'/%3E%3C/svg%3E") no-repeat right .75rem center !important;
      background-size: .65em auto !important;
    }
    .form-select:focus {
      border-color: #800080 !important;
      box-shadow: 0 0 0 .2rem rgba(128,0,128,.25) !important;
      outline: none !important;
    }
    </style>
@endpush

@section('content')
<div class="container-fluid px-4">

    {{-- Header Ungu --}}
    <h2 class="header-title">
        <i class="fas fa-list me-2"></i>Daftar Jenis Surat Tugas
    </h2>

    {{-- Flash --}}
    @if(session('success'))
        <div class="d-none" id="swal-flash" data-message="{{ session('success') }}"></div>
    @endif

    {{-- Tombol Tambah --}}
    <div class="mb-4">
        <a href="{{ route('jenis_surat_tugas.create') }}"
           class="btn btn-primary rounded-pill px-4">
            <i class="fas fa-plus me-2"></i>Tambah Jenis Surat Tugas
        </a>
    </div>

    {{-- Tabel --}}
    <div class="card shadow-sm border-0">
        <div class="card-body">
            <div class="table-responsive">
                <table id="table-jenis" class="table table-hover align-middle w-100 mb-0">
                    <thead class="table-light">
                        <tr class="text-center">
                            <th width="60">#</th>
                            <th>Nama Jenis Surat Tugas</th>
                            <th width="140">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($list as $i => $item)
                            <tr>
                                <td class="text-center">{{ $i+1 }}</td>
                                <td>{{ $item->nama }}</td>
                                <td class="text-center">
                                    <a href="{{ route('jenis_surat_tugas.edit', $item->id) }}"
                                       class="btn btn-sm btn-outline-warning me-1" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button data-url="{{ route('jenis_surat_tugas.destroy', $item->id) }}"
                                            class="btn btn-sm btn-outline-danger btn-delete"
                                            title="Hapus">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
    <tr>
        <td></td>
        <td class="text-center text-muted py-4">Belum ada data jenis surat tugas.</td>
        <td></td>
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
document.addEventListener('DOMContentLoaded', function () {
    // Flash message
    const flash = document.getElementById('swal-flash');
    if (flash) {
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: flash.dataset.message,
            timer: 2500,
            showConfirmButton: false
        });
    }

    // Init DataTable
    $('#table-jenis').DataTable({
        paging: false,
        info: false,
        searching: false,
        columnDefs: [{ orderable:false, targets:2 }]
    });

    // Konfirmasi hapus
    $('body').on('click', '.btn-delete', function (e) {
        e.preventDefault();
        const url = $(this).data('url');
        Swal.fire({
            title: 'Hapus Jenis Surat Tugas?',
            text: "Data akan dihapus permanen.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal'
        }).then((res) => {
            if (res.isConfirmed) {
                $('<form>', { method: 'POST', action: url })
                    .append('@csrf')
                    .append('@method("DELETE")')
                    .appendTo('body')
                    .submit();
            }
        });
    });
});
</script>
@endpush
