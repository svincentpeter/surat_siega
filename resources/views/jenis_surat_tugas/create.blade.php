{{-- resources/views/jenis_surat_tugas/create.blade.php --}}
@extends('layouts.app')
@section('title', 'Tambah Jenis Surat Tugas')

@push('styles')
    <!-- (Optional) sama seperti index jika butuh DataTables CSS -->
@endpush

@section('content')
<div class="container-fluid px-4">
    {{-- Header Ungu --}}
    <h2 class="header-title">
        <i class="fas fa-plus me-2"></i>Tambah Jenis Surat Tugas
    </h2>

    {{-- Error Validasi --}}
    @if($errors->any())
        <div class="alert alert-danger">
            <strong>Periksa kembali input:</strong>
            <ul class="mb-0">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('jenis_surat_tugas.store') }}" method="POST">
        @csrf
        <div class="card mb-4">
            <div class="card-header bg-purple text-white">
                <i class="fas fa-tasks me-2"></i>Form Jenis Surat Tugas
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Nama Jenis Surat Tugas</label>
                    <input type="text" name="nama"
                           class="form-control @error('nama') is-invalid @enderror"
                           value="{{ old('nama') }}"
                           placeholder="Contoh: Monitoring">
                    @error('nama')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>

        <button class="btn btn-success me-2">
            <i class="fas fa-save me-1"></i> Simpan
        </button>
        <a href="{{ route('jenis_surat_tugas.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i> Batal
        </a>
    </form>
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

